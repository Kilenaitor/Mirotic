<?hh // strict

final class Session {

  public static function get(string $key, mixed $default = null): mixed {
    /* HH_IGNORE_ERROR[2050] The session variable is definitely defined */
    return idx($_SESSION, $key, $default);
  }

  public static function set(string $key, mixed $val): void {
    /* HH_IGNORE_ERROR[2050] The session variable is definitely defined */
    $_SESSION[$key] = $val;
  }

}
