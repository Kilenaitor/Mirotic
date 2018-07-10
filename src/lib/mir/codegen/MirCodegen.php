<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\{
  CLIWithArguments,
  ExitException,
  Terminal,
  OutputInterface,
};
use namespace Facebook\CLILib\CLIOptions;
use Facebook\HackCodegen\{
  HackCodegenFactory,
  HackCodegenConfig,
  HackBuilderValues,
  HackBuilderKeys,
};

final class MirCodegen extends CLIWithArguments {

  // Files
  private string $directory_path = '';
  private string $file_path = '';

  // Controller
  private string $raw_controller_name = '';
  private string $controller_name = '';
  private HTTPMethodClasses $controller_type = HTTPMethodClasses::GET;
  private string $controller_url = '';

  // Pages
  private string $raw_page_name = '';
  private string $page_name = '';

  <<__Override>>
  public async function mainAsync(): Awaitable<int> {
    $arguments = $this->getArguments();
    $codegen_type = C\firstx($arguments);
    $error_code = 0; // Default

    switch ($codegen_type) {
      case 'controller':
        await $this->genController();
        break;
      case 'page':
        await $this->genPage();
        break;
      case 'urlmap':
        echo 'urlmap';
        break;
      default:
        echo 'Must be one of: [controller, page, urlmap, ...]';
        $error_code = 11;
    }
    return $error_code;
  }

  <<__Override>>
  protected function getSupportedOptions(): vec<CLIOptions\CLIOption> {
    return vec[

    ];
  }

  private async function genController(): Awaitable<void> {
    $correct = false;
    while (!$correct) {
      $this->controller_type = await $this->promptControllerType();
      echo Str\format('Creating a new %s controller. ', $this->controller_type);
      $correct =
        await MirUtil::promptYesNo($this->getStdin(), 'Is this correct');
    }

    $correct = false;
    while (!$correct) {
      $this->raw_controller_name = await $this->promptControllerName();
      $controller_name = Str\format('%sController', $this->raw_controller_name);
      echo Str\format('Controller will be named %s. ', $controller_name);
      $this->controller_name = $controller_name;
      $correct =
        await MirUtil::promptYesNo($this->getStdin(), 'Is this correct');
    }

    $correct = false;
    while (!$correct) {
      $this->directory_path = await $this->promptDirectoryName();
      $this->directory_path .= Str\is_empty($this->directory_path) ? '' : '/';
      $this->file_path = Str\format(
        '%s%s%s.php',
        self::getControllerPath(),
        $this->directory_path,
        $this->controller_name,
      );
      $this->controller_url = $this->getControllerURL();
      echo
        Str\format('Creating new controller at `%s`. ', $this->file_path);
      $correct =
        await MirUtil::promptYesNo($this->getStdin(), 'Is this correct');
    }

    await $this->genCodegenController();

    $page_too = false;
    if ($this->controller_type === HTTPMethodClasses::GET) {
      $page_too = await MirUtil::promptNoYes(
        $this->getStdin(),
        'Would you like to generate a corresponding page',
      );
    }
    if ($page_too) {
      $this->page_name = Str\format('%sPage', $this->raw_controller_name);
      $this->file_path = Str\format(
        '%s%s%s.php',
        self::getPagePath(),
        $this->directory_path,
        $this->page_name,
      );
      echo Str\format('Creating new page at `%s`', $this->file_path);
      await $this->genCodegenPage();
    }
  }

  private async function genPage(): Awaitable<void> {
    $correct = false;

    while (!$correct) {
      $this->raw_page_name = await $this->promptPageName();
      $page_name = Str\format('%sPage', $this->raw_page_name);
      echo Str\format('Page will be named \'%s\'. ', $page_name);
      $this->page_name = $page_name;
      $correct =
        await MirUtil::promptYesNo($this->getStdin(), 'Is this correct');
    }

    $correct = false;
    while (!$correct) {
      $this->directory_path = await $this->promptDirectoryName();
      $this->directory_path .= Str\is_empty($this->directory_path) ? '' : '/';
      $this->file_path = Str\format(
        '%s%s%s.php',
        self::getPagePath(),
        $this->directory_path,
        $this->page_name,
      );
      echo Str\format('Creating new page at `%s`. ', $this->file_path);
      $correct =
        await MirUtil::promptYesNo($this->getStdin(), 'Is this correct');
    }

    await $this->genCodegenPage();
  }

  private async function promptControllerName(): Awaitable<string> {
    $raw_controller_name = '';
    while (Str\is_empty($raw_controller_name)) {
      echo 'Name for the controller: ';
      $raw_controller_name = await $this->getStdin()->readLineAsync();
      $raw_controller_name = Str\trim($raw_controller_name);
    }
    if ($this->controller_type === HTTPMethodClasses::AJAX) {
      $raw_controller_name = Str\format('%sAjax', $raw_controller_name);
    }
    return $raw_controller_name;
  }

  private async function promptControllerType(): Awaitable<HTTPMethodClasses> {
    $controller_type = null;
    while ($controller_type === null) {
      echo "Type of controller [GET, AJAX]: ";
      $controller_type_raw = await $this->getStdin()->readLineAsync();
      $controller_type =
        HTTPMethodClasses::coerce(Str\trim($controller_type_raw));
    }
    return $controller_type;
  }

  private async function promptPageName(): Awaitable<string> {
    $raw_page_name = '';
    while (Str\is_empty($raw_page_name)) {
      echo 'Name for the page: ';
      $raw_page_name = await $this->getStdin()->readLineAsync();
      $raw_page_name = Str\trim($raw_page_name);
    }
    return $raw_page_name;
  }

  private async function promptDirectoryName(): Awaitable<string> {
    echo '(Optional) Name for the parent directory: ';
    $directory_name = await $this->getStdin()->readLineAsync();
    return Str\trim($directory_name);
  }

  private function getControllerURL(): string {
    if ($this->controller_type === HTTPMethodClasses::GET) {
      return Str\format(
        '/%s%s',
        $this->directory_path,
        Str\lowercase($this->raw_controller_name),
      );
    } else {
      return Str\format(
        '/ajax/%s%s',
        $this->directory_path,
        Str\strip_suffix($this->raw_controller_name, 'Ajax')
          |> Str\lowercase($$),
      );
    }
  }

  private async function genCodegenController(): Awaitable<void> {
    $cg = new HackCodegenFactory(new HackCodegenConfig());
    switch ($this->controller_type) {
      case HTTPMethodClasses::GET:
        $file = $this->genCodegenGetController($cg);
        break;
      case HTTPMethodClasses::AJAX:
        $file = $this->genCodegenAjaxController($cg);
        break;
    }
    $file->save();
  }

  private async function genCodegenPage(): Awaitable<void> {
    $cg = new HackCodegenFactory(new HackCodegenConfig());
    $cg->codegenFile($this->file_path)
      ->addClass(
        $cg->codegenClass($this->page_name)
          ->setExtends(BasePage::class)
          ->addMethod(
            $cg->codegenMethod('render')
              ->setIsOverride(true)
              ->addParameter('dict<string, mixed> $args')
              ->setReturnType(':xhp')
              ->setBody(
                $cg->codegenHackBuilder()
                  ->addReturnf('<div></div>')
                  ->getCode(),
              ),
          ),
      )
      ->setIsSignedFile(false)
      ->save();
  }

  private function genCodegenGetController(
    HackCodegenFactory $cg,
  ): \Facebook\HackCodegen\CodegenFile {
    return $cg->codegenFile($this->file_path)
      ->addClass(
        $cg->codegenClass($this->controller_name)
          ->setExtends(BaseGetController::class)
          ->addMethod(
            $cg->codegenMethod('getURL')
              ->setIsOverride(true)
              ->setIsStatic(true)
              ->setReturnType('string')
              ->setBody(
                $cg->codegenHackBuilder()
                  ->addReturn(
                    $this->controller_url,
                    HackBuilderValues::export(),
                  )
                  ->getCode(),
              ),
          )
          ->addMethod(
            $cg->codegenMethod('genRender')
              ->setIsOverride(true)
              ->setIsAsync(true)
              ->setReturnType('Awaitable<?:xhp>')
              ->setBody(
                $cg->codegenHackBuilder()
                  ->addReturnf('(new BlankPage())->getHTML(dict[])')
                  ->getCode(),
              ),
          ),
      )
      ->setIsSignedFile(false);
  }

  private function genCodegenAjaxController(
    HackCodegenFactory $cg,
  ): \Facebook\HackCodegen\CodegenFile {
    return $cg->codegenFile($this->file_path)
      ->addClass(
        $cg->codegenClass($this->controller_name)
          ->setExtends(BaseAjaxController::class)
          ->addMethod(
            $cg->codegenMethod('getURL')
              ->setIsOverride(true)
              ->setIsStatic(true)
              ->setReturnType('string')
              ->setBody(
                $cg->codegenHackBuilder()
                  ->addReturn(
                    $this->controller_url,
                    HackBuilderValues::export(),
                  )
                  ->getCode(),
              ),
          )
          ->addMethod(
            $cg->codegenMethod('genResponse')
              ->setIsOverride(true)
              ->setIsAsync(true)
              ->setReturnType('Awaitable<?string>')
              ->setBody(
                $cg->codegenHackBuilder()
                  ->addReturnf('\'\'')
                  ->getCode(),
              ),
          ),
      )
      ->setIsSignedFile(false);
  }

  private static function getControllerPath(): string {
    return MirUtil::getPathToSrc().'controllers/';
  }

  private static function getPagePath(): string {
    return MirUtil::getPathToSrc().'pages/';
  }

}

enum HTTPMethodClasses: string as string {
  GET = 'GET';
  AJAX = 'AJAX';
}
