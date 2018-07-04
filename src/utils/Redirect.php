<?hh // strict

final class Redirect {

  public static function to(string $url): noreturn {
    \exit(\header('Location: '.$url));
  }

}
