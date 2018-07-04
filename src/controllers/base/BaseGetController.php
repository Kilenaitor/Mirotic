<?hh // strict

abstract class BaseGetController extends BaseController {

  // The type of method to call the controller with
  <<__Override>>
  public static function getHTTPMethod(): HTTPMethod {
    return HTTPMethod::GET;
  }

}
