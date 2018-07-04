<?hh // strict

abstract class BasePostController extends BaseController {

  <<__Override>>
  public static function getHTTPMethod(): HTTPMethod {
    return HTTPMethod::POST;
  }

}
