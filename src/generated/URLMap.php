<?hh // strict

/**
 * THIS FILE IS GENERATED. DO NOT MODIFY IT MANUALLY. YOUR CHANGES WILL BE LOST.
 */
class URLMap {

  const dict<string, classname<BaseController>> URL_GET_PATTERNS = dict[
    '/^\/?(\?.*)?$/' => HomeController::class
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
