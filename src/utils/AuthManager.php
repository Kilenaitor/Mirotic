<?hh // strict

final abstract class AuthManager {

  final public static async function doesUserHavePermissionToSeeRouteAsync(
    BaseController $controller,
  ): Awaitable<bool> {
    $auth_level_required = $controller->getAdminLevel();
    return $auth_level_required === AdminLevel::PUBLIC;
  }

}
