<?hh // strict
/**
 * This file is generated. Do not modify it manually!
 *
 * @generated SignedSource<<39acfc0e190c9fedbb62b76b616f1d29>>
 */

class UrlMap {


  const dict<string, classname<BaseController>> URL_GET_PATTERNS = dict[
    "/^\/?(\?.*)?\$/" => \HomeController::class,
    "/^\/greeting\/(\w+)\/?(\?.*)?\$/" => \GreetingController::class,
  ];

  const dict<string, classname<BaseController>> URL_POST_PATTERNS = dict[
  ];

  public static function getPatternsForMethod(
    HttpMethod $method,
  ): dict<string, classname<BaseController>> {
    switch ($method) {
      case HttpMethod::GET:
        return self::URL_GET_PATTERNS;
      case HttpMethod::POST:
        return self::URL_POST_PATTERNS;
      default:
        return self::URL_GET_PATTERNS;
    }
  }
}
