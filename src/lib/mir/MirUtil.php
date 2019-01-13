<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use type Facebook\CLILib\{
  CLIWithRequiredArguments,
  ExitException,
  Terminal,
  InputInterface,
};

final class MirUtil {

  public static async function confirmAsync<T>(
    InputInterface $in,
    (function(): Awaitable<T>) $body,
  ): Awaitable<?T> {
    $response = null;
    $correct = false;
    while (!$correct) {
      $response = await $body();
      $correct = await MirUtil::promptYesNoAsync($in, 'Is this correct');
    }
    return $response;
  }

  public static async function promptYesNoAsync(
    InputInterface $in,
    string $promptText,
  ): Awaitable<bool> {
    return await self::promptImplAsync($in, $promptText, true);
  }

  public static async function promptNoYesAsync(
    InputInterface $in,
    string $promptText,
  ): Awaitable<bool> {
    return await self::promptImplAsync($in, $promptText, false);
  }

  private static async function promptImplAsync(
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

  public static function getControllerPath(): string {
    return MirUtil::getPathToSrc().'controllers';
  }

  public static function getPagePath(): string {
    return MirUtil::getPathToSrc().'pages';
  }

  public static function getRoutingPath(): string {
    return MirUtil::getPathToSrc().'routing';
  }

}
