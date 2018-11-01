<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * @generated SignedSource<<f378057fcd42bc2a1ed7d118dd718051>>
 */

class URLMap {


  const dict<string, classname<BaseController>> URL_GET_PATTERNS = dict[
    "/^\/?(\?.*)?\$/" => \HomeController::class,
  ];

  const dict<string, classname<BaseController>> URL_POST_PATTERNS = dict[
  ];

  public static function getPatternsForMethod(
    HTTPMethod $method,
  ): dict<string, classname<BaseController>> {
    switch ($method) {
      case HTTPMethod::GET:
        return self::URL_GET_PATTERNS;
      case HTTPMethod::POST:
        return self::URL_POST_PATTERNS;
      default:
        return self::URL_GET_PATTERNS;
    }
  }
}
