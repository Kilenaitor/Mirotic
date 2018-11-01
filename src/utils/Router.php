<?hh // strict

final abstract class Router {

  final public static function getRequestPath(): string {
    /* HH_IGNORE_ERROR[2050] The server variable is definitely defined */
    return $_SERVER['REQUEST_URI'];
  }

  final public static function getRequestMethod(): HttpMethod {
    /* HH_IGNORE_ERROR[2050] The server variable is definitely defined */
    return HttpMethod::assert($_SERVER['REQUEST_METHOD']);
  }

  final public static async function genController(
  ): Awaitable<BaseController> {
    $controller = await UrlResolver::getControllerForURLAndMethodAsync(
      self::getRequestPath(),
      self::getRequestMethod(),
    );
    $has_permission =
      await AuthManager::doesUserHavePermissionToSeeRouteAsync($controller);
    if (!$has_permission) {
      throw new PermissionDeniedException();
    }
    return $controller;
  }

}
