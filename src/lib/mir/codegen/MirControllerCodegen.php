<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\InputInterface;
use type Facebook\HackCodegen\{
  HackCodegenFactory,
  HackCodegenConfig,
  HackBuilderValues,
  HackBuilderKeys,
};

final class MirControllerCodegen {

  // Files
  private string $directory_path = '';
  private string $file_path = '';

  // Controller
  private string $raw_controller_name = '';
  private string $controller_name = '';
  private HttpMethodClasses $controller_type = HttpMethodClasses::GET;
  private string $controller_url = '';

  public function __construct(private InputInterface $in) {}

  public async function controllerFlowAsync(): Awaitable<void> {
    await MirUtil::confirmAsync(
      $this->in,
      async () ==> {
        $this->controller_type = await $this->promptControllerType();
        echo
          Str\format('Creating a new %s controller. ', $this->controller_type);
      },
    );

    await MirUtil::confirmAsync($this->in, async () ==> {
      $this->raw_controller_name = await $this->promptControllerName();
      $controller_name = Str\format('%sController', $this->raw_controller_name);
      echo Str\format('Controller will be named %s. ', $controller_name);
      $this->controller_name = $controller_name;
    });

    await MirUtil::confirmAsync(
      $this->in,
      async () ==> {
        $this->directory_path = await $this->promptDirectoryName();
        $this->directory_path .= Str\is_empty($this->directory_path) ? '' : '/';
        $this->file_path = Str\format(
          '%s%s%s.php',
          MirUtil::getControllerPath().'/',
          $this->directory_path,
          $this->controller_name,
        );
        $this->controller_url = $this->getControllerURL();
        echo Str\format('Creating new controller at `%s`. ', $this->file_path);
      },
    );

    await $this->codegenControllerAsync();

    $page_too = false;
    if ($this->controller_type === HttpMethodClasses::GET) {
      $page_too = await MirUtil::promptNoYesAsync(
        $this->in,
        'Would you like to generate a corresponding page',
      );
    }
    if ($page_too) {
      $page_name = Str\format('%sPage', $this->raw_controller_name);
      $file_path = Str\format(
        '%s%s%s.php',
        MirUtil::getPagePath().'/',
        $this->directory_path,
        $page_name,
      );
      echo Str\format('Creating new page at `%s`', $this->file_path);
      $page_codegen = new MirPageCodegen(
        $this->in,
        $this->directory_path,
        $file_path,
        $page_name,
        '',
      );
      await $page_codegen->codegenPageAsync();
    }
  }

  private async function promptControllerName(): Awaitable<string> {
    $raw_controller_name = '';
    while (Str\is_empty($raw_controller_name)) {
      echo 'Name for the controller: ';
      $raw_controller_name = await $this->in->readLineAsync();
      $raw_controller_name = Str\trim($raw_controller_name);
    }
    if ($this->controller_type === HttpMethodClasses::AJAX) {
      $raw_controller_name = Str\format('%sAjax', $raw_controller_name);
    }
    return $raw_controller_name;
  }

  private async function promptControllerType(): Awaitable<HttpMethodClasses> {
    $controller_type = null;
    while ($controller_type === null) {
      echo "Type of controller [GET, AJAX]: ";
      $controller_type_raw = await $this->in->readLineAsync();
      $controller_type =
        HttpMethodClasses::coerce(Str\trim($controller_type_raw));
    }
    return $controller_type;
  }

  private async function promptDirectoryName(): Awaitable<string> {
    echo '(Optional) Name for the parent directory: ';
    $directory_name = await $this->in->readLineAsync();
    return Str\trim($directory_name);
  }

  private function getControllerURL(): string {
    if ($this->controller_type === HttpMethodClasses::GET) {
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

  private async function codegenControllerAsync(): Awaitable<void> {
    $cg = new HackCodegenFactory(new HackCodegenConfig());
    switch ($this->controller_type) {
      case HttpMethodClasses::GET:
        $file = $this->codegenGetControllerAsync($cg);
        break;
      case HttpMethodClasses::AJAX:
        $file = $this->codegenAjaxControllerAsync($cg);
        break;
    }
    $file->save();
  }

  private function codegenGetControllerAsync(
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
            $cg->codegenMethod('renderAsync')
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

  private function codegenAjaxControllerAsync(
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

}
