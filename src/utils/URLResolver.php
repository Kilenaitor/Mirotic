<?hh // strict

class URLResolver {

  public static async function getControllerForURLAndMethodAsync(
    string $url,
    HTTPMethod $method,
  ): Awaitable<BaseController> {
    list($controller_name, $controller_args) =
      await self::mapURLToControllerWithArgsAsync($url, $method);
    return new $controller_name($controller_args);
  }

  public static function getArgMapForControllerAndURL(
    classname<BaseController> $controller_class,
    string $url,
    string $url_pattern,
    HTTPMethod $method,
  ): dict<string, mixed> {
    switch ($method) {
      case HTTPMethod::GET:
        return self::getArgMapForControllerAndURLForGet(
          $controller_class,
          $url,
          $url_pattern,
        );
      case HTTPMethod::POST:
        return self::getArgMapForControllerAndURLForPost(
          $controller_class,
        );
      default:
        return self::getArgMapForControllerAndURLForGet(
          $controller_class,
          $url,
          $url_pattern,
        );
    }
  }

  private static function getArgMapForControllerAndURLForGet(
    classname<BaseController> $controller_class,
    string $url,
    string $url_pattern,
  ): dict<string, mixed> {
    $final_param_map = dict[];
    $param_definitions = $controller_class::getParamDefinitions();

    $matches = dict[];
    \preg_match($url_pattern, $url, &$matches);
    \array_shift(&$matches);

    $index_counter = 0;
    foreach ($param_definitions as $param_name => $param_type) {
      \settype(&$matches[$index_counter], $param_type);
      $final_param_map[$param_name] = $matches[$index_counter];
      $index_counter++;
    }

    return $final_param_map;
  }

  private static function getArgMapForControllerAndURLForPost(
    classname<BaseController> $controller_class,
  ): dict<string, mixed> {
    $final_param_map = dict[];
    $param_definitions = $controller_class::getParamDefinitions();

    foreach ($param_definitions as $param_name => $param_type) {
      $post_param = Post::get($param_name);
      if ($post_param === null) {
        throw new PageNotFoundException();
      }
      \settype(&$post_param, $param_type);
      $final_param_map[$param_name] = $post_param;
    }

    return $final_param_map;
  }

  public static async function mapURLToControllerWithArgsAsync(
    string $url,
    HTTPMethod $method,
  ): Awaitable<(classname<BaseController>, dict<string, mixed>)> {
    $url_key = '';
    $url_map = URLMap::getPatternsForMethod($method);
    foreach (Keyset\keys($url_map) as $url_pattern) {
      $is_match = preg_match($url_pattern, $url);
      if ($is_match) {
        $url_key = $url_pattern;
        break;
      }
    }

    // In case we get no match
    if ($url_key === '') {
      throw new PageNotFoundException();
    }

    $controller_name = $url_map[$url_key];
    $controller_args = self::getArgMapForControllerAndURL(
      $controller_name,
      $url,
      $url_key,
      $method,
    );

    return tuple($controller_name, $controller_args);
  }

}
