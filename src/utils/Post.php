<?hh // strict

final class Post {

  public static function get(string $key, mixed $default = null): mixed {
    /* HH_IGNORE_ERROR[2050] The post variable is definitely defined */
    return idx($_POST, $key, $default);
  }

}
