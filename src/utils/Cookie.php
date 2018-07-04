<?hh // strict

final class Cookie {

  public static function get(string $key, mixed $default = null): mixed {
    /* HH_IGNORE_ERROR[2050] The cookie variable is definitely defined */
    return idx($_COOKIE, $key, $default);
  }

  public static function set(
    string $key,
    mixed $val,
    int $timeout = 2147483647,
    string $path = '/',
  ): void {
    /* HH_IGNORE_ERROR[2050] The cookie variable is definitely defined */
    \setcookie(
      $key, // Key used to retrieve the cookie value
      $val, // Value to store in the cookie.
      $timeout, // Cookie expiratory date. This is set to 2038
      $path, // Path the cookie should be available on. We want whole domain.
    );
  }

}
