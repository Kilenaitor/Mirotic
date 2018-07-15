<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\{
  CLIWithRequiredArguments,
  ExitException,
  Terminal,
  InputInterface,
};

final class MirUtil {

  public static async function genConfirm<T>(
    InputInterface $in,
    (function(): Awaitable<T>) $body,
  ): Awaitable<?T> {
    $response = null;
    $correct = false;
    while (!$correct) {
      $response = await $body();
      $correct = await MirUtil::promptYesNo($in, 'Is this correct');
    }
    return $response;
  }

  public static async function promptYesNo(
    InputInterface $in,
    string $promptText,
  ): Awaitable<bool> {
    return await self::promptImpl($in, $promptText, true);
  }

  public static async function promptNoYes(
    InputInterface $in,
    string $promptText,
  ): Awaitable<bool> {
    return await self::promptImpl($in, $promptText, false);
  }

  private static async function promptImpl(
    InputInterface $in,
    string $promptText,
    bool $defaultAnswer = true,
  ): Awaitable<bool> {
    if ($defaultAnswer) {
      echo "$promptText? [Y/n] ";
    } else {
      echo "$promptText? [y/N] ";
    }
    $response = await $in->readLineAsync();
    $response = Str\trim($response);

    if (Str\is_empty($response)) {
      return $defaultAnswer;
    } else {
      return $response === 'Y' || $response === 'y';
    }
  }

  public static function getPathToSrc(): string {
    return Str\strip_suffix(__DIR__, 'lib/mir');
  }

}
