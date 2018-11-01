<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\{
  CLIWithRequiredArguments,
  ExitException,
  Terminal,
  OutputInterface,
};
use namespace Facebook\CLILib\CLIOptions;

final class Mir extends CLIWithRequiredArguments {

  <<__Override>>
  public async function mainAsync(): Awaitable<int> {
    $arguments = $this->getArguments();
    $command = C\firstx($arguments);
    $error_code = 0;
    switch ($command) {
      case 'g':
      case 'generate':
        // CLIBase drops first argument on its own.
        // So, if we strip off process name, it'll strip off first command.
        $codegen =
          new MirCodegen(Vec\drop($this->getArgv(), 1), $this->getTerminal());
        $error_code = await $codegen->mainAsync();
        break;
      case 'r':
      case 'route':
      case 'routes':
        echo 'Print out all routes configured for the site';
        break;
      default:
        echo 'Unsupported option.';
        $error_code = 1;
        break;
    }
    return $error_code;
  }

  <<__Override>>
  protected function getSupportedOptions(): vec<CLIOptions\CLIOption> {
    return vec[];
  }

  <<__Override>>
  public static function getHelpTextForRequiredArguments(): vec<string> {
    return vec[
      'COMMAND',
    ];
  }

}
