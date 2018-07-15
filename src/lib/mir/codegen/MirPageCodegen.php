<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\InputInterface;
use type Facebook\HackCodegen\{
  HackCodegenFactory,
  HackCodegenConfig,
  HackBuilderValues,
  HackBuilderKeys,
};

final class MirPageCodegen {

  public function __construct(
    private InputInterface $in,
    private string $directory_path = '',
    private string $file_path = '',
    private string $page_name = '',
    private string $raw_page_name = '',
  ) {}

  public async function pageFlowAsync(): Awaitable<void> {
    await MirUtil::confirmAsync(
      $this->in,
      async () ==> {
        $this->raw_page_name = await $this->promptPageName();
        $page_name = Str\format('%sPage', $this->raw_page_name);
        echo Str\format('Page will be named \'%s\'. ', $page_name);
        $this->page_name = $page_name;
      },
    );

    await MirUtil::confirmAsync(
      $this->in,
      async () ==> {
        $this->directory_path = await $this->promptDirectoryName();
        $this->directory_path .= Str\is_empty($this->directory_path) ? '' : '/';
        $this->file_path = Str\format(
          '%s%s%s.php',
          MirUtil::getPagePath().'/',
          $this->directory_path,
          $this->page_name,
        );
        echo Str\format('Creating new page at `%s`. ', $this->file_path);
      },
    );

    await $this->codegenPageAsync();
  }

  public async function codegenPageAsync(): Awaitable<void> {
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

  private async function promptPageName(): Awaitable<string> {
    $raw_page_name = '';
    while (Str\is_empty($raw_page_name)) {
      echo 'Name for the page: ';
      $raw_page_name = await $this->in->readLineAsync();
      $raw_page_name = Str\trim($raw_page_name);
    }
    return $raw_page_name;
  }

  private async function promptDirectoryName(): Awaitable<string> {
    echo '(Optional) Name for the parent directory: ';
    $directory_name = await $this->in->readLineAsync();
    return Str\trim($directory_name);
  }
}
