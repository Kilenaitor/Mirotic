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

    $error_code = 0; // Default

    switch ($command) {
      case 'generate':
        // CLIBase drops first argument on its own.
        // So, if we strip off process name, it'll strip off first command.
        $codegen =
          new MirCodegen(Vec\drop($this->getArgv(), 1), $this->getTerminal());
        $error_code = await $codegen->mainAsync();
        break;
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

  <<__Override>>
  public function displayHelp(OutputInterface $out): void {
    echo "Welcome to Mir!\n\n";
    echo
      "Mir is the toolkit provided in Mirotic to automate various operations.\n";
    echo "These include things like: \n";
    echo "  Codegeneration\n";
    echo "  Linting\n";
    echo "  Debugging\n";
    echo "  and more...\n\n";
    echo
      "There are many sub-utils in Mir. They are accessed with the appropriate command.\n";
    echo "For example, to generate a new controller, you'd type:\n\n";
    echo "  hhvm bin/mir codgen controller\n\n";
    echo "and then follow the prompts from there.\n\n";
    echo "To see a full list of commands, check the documentation.\n";
    parent::displayHelp($this->getStdout());
  }

}
