<?hh // strict

use type Facebook\CLILib\{CLIWithArguments, ExitException, Terminal};
use namespace Facebook\CLILib\CLIOptions;

final class Mir extends CLIWithArguments {

  private int $verbosity = 0;

  <<__Override>>
  public async function mainAsync(): Awaitable<int> {
    $this->getStdout()->write("Welcome to Mirotic!\n");
    return 0;
  }

  <<__Override>>
  protected function getSupportedOptions(): vec<CLIOptions\CLIOption> {
    return vec[
      CLIOptions\flag(
        () ==> { $this->verbosity++; },
        "Increase output verbosity",
        '--verbose',
        '-v',
      ),
    ];
  }

}
