<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * @generated SignedSource<<f2dce66c46b5677a35faf1db49e44455>>
 */

class URLMap {

  static dict<string, classname<BaseController>> $URL_GET_PATTERNS = dict [
    '/^\/?(\?.*)?$/' => HomeController::class,
    '/^\/admin(\/\w+)(\/\w+)?\/?(\?.*)?$/' => AdminController::class,
  ];

  const URL_POST_PATTERNS = dict [
  ];

  public static function getPatternsForMethod(
    HTTPMethod $method,
  ): dict<string, classname<BaseController>> {
    switch ($method) {
      case HTTPMethod::GET:
        return self::$URL_GET_PATTERNS;
      case HTTPMethod::POST:
        return self::URL_POST_PATTERNS;
      default:
        return self::URL_GET_PATTERNS;
    }
  }
}
