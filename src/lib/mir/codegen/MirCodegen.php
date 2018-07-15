<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\{
  CLIWithArguments,
  ExitException,
  Terminal,
  OutputInterface,
};
use namespace Facebook\CLILib\CLIOptions;
use namespace Facebook\HHAST;

final class MirCodegen extends CLIWithArguments {

  <<__Override>>
  public async function mainAsync(): Awaitable<int> {
    $arguments = $this->getArguments();
    $codegen_type = C\firstx($arguments);
    $error_code = 0;
    switch ($codegen_type) {
      case 'controller':
        $controller_codegen = new MirControllerCodegen($this->getStdin());
        await $controller_codegen->controllerFlowAsync();
        break;
      case 'page':
        $page_codegen = new MirPageCodegen($this->getStdin());
        await $page_codegen->pageFlowAsync();
        break;
      case 'urlmap':
        $urlmap_codegen = new MirURLMapCodegen($this->getStdin());
        await $urlmap_codegen->rebuildRoutesFlowAsync();
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

}

enum HTTPMethodClasses: string as string {
  GET = 'GET';
  AJAX = 'AJAX';
}
