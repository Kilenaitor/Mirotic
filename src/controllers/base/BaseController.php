<?hh // strict

<<__ConsistentConstruct>>
abstract class BaseController {

  private dict<string, mixed> $params = dict[];

  public function __construct(dict<string, mixed> $params): void {
    $this->params = $params;
  }

  // URL of the controller
  abstract public static function getURL(): string;

  // Arguments the controller accepts
  public function getParams(): dict<string, mixed> {
    return $this->params;
  }

  // What arguments to look for in the url
  public static function getParamDefinitions(): dict<string, string> {
    return dict[];
  }

  // The type of method to call the controller with
  abstract public static function getHTTPMethod(): HTTPMethod;

  // The XHP output of the controller to be rendered
  abstract public function genRender(): Awaitable<?:xhp>;

  public function getAdminLevel(): AdminLevel {
    return AdminLevel::PUBLIC;
  }

}
