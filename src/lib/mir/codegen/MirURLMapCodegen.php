<?hh // strict

use namespace HH\Lib\{C, Dict, Str, Vec};
use namespace Facebook\TypeAssert;
use type Facebook\CLILib\InputInterface;
use type Facebook\HackCodegen\{
  HackCodegenFactory,
  HackCodegenConfig,
  HackBuilderValues,
  HackBuilderKeys,
};
use namespace Facebook\HHAST;
use type Facebook\HHAST\{
  EditableNode,
  EditableToken,
  IFunctionishDeclaration,
  MethodishDeclaration,
  FunctionDeclaration,
  ReturnStatement,
  LiteralExpression,
};

final class MirUrlMapCodegen {

  const URL_MAP_CLASS_NAME = 'URLMap';
  const type TUrlWithType = shape(
    'url' => string,
    'type' => HttpMethod,
  );
  const type TRouteWithClass = shape(
    'route' => string,
    'class' => classname<BaseController>,
  );

  // Route consts
  const ROUTE_START = '/^';
  const ROUTE_END = '\/?(\?.*)?$/';
  const SLASH = '\/';
  // Parameter Regex Replacements
  const PARAMETER_PATTERN = '/\{\??\w+\}/';
  const STRING_PARAM = '\w+';
  const INT_PARAM = '[-+]?\d+';
  const FLOAT_PARAM = '[-+]?[0-9]*\.?[0-9]+';

  private dict<string, classname<BaseController>> $get_routes = dict[];
  private dict<string, classname<BaseController>> $post_routes = dict[];

  public function __construct(private InputInterface $in) {}

  public async function rebuildRoutesFlowAsync(): Awaitable<int> {
    $proceed = await MirUtil::promptNoYesAsync(
      $this->in,
      'This will re-generate the URL mapping for your application. '.
      'This will overwrite any manual changes to the file. Proceed',
    );
    if (!$proceed) {
      return 0;
    }

    $controllers = self::getAllControllers();
    foreach ($controllers as $controller) {
      $route = self::convertURLToRoute($controller);
      if ($controller::getHttpMethod() === HttpMethod::GET) {
        $this->get_routes[$route] = $controller;
      } else {
        $this->post_routes[$route] = $controller;
      }
    }
    await $this->generateURLMapAsync();
    return 0;
  }

  private static function getAllControllers(): vec<classname<BaseController>> {
    $project_controller_path = MirUtil::getControllerPath();
    $excluded_controllers = Mirotic::CONTROLLERS_TO_IGNORE_FROM_URLMAP;
    $directory = new \RecursiveDirectoryIterator($project_controller_path);
    $iterator = new \RecursiveIteratorIterator($directory);
    $php_controllers = new \RegexIterator($iterator, '/Controller\.php$/i');
    $hh_controllers = new \RegexIterator($iterator, '/Controller\.hh$/i');

    $controllers = vec[];
    foreach ($php_controllers as $controller) {
      $controller_name = $controller->getFilename();
      if (!C\contains($excluded_controllers, $controller_name)) {
        $controllers[] = $controller_name;
      }
    }
    foreach ($hh_controllers as $controller) {
      $controller_name = $controller->getFilename();
      if (!C\contains($excluded_controllers, $controller_name)) {
        $controllers[] = $controller_name;
      }
    }
    return Vec\map(
      $controllers,
      $controller ==> Str\strip_suffix($controller, '.php'),
    )
      |> Vec\map($$, $controller ==> Str\strip_suffix($controller, '.hh'))
      |> Vec\map(
        $$,
        $controller ==>
          TypeAssert\classname_of(BaseController::class, $controller),
      );
  }

  private static function convertURLToRoute(
    classname<BaseController> $controller,
  ): string {
    $route_string = self::ROUTE_START;
    $url = $controller::getURL();
    $definitions = $controller::getParamDefinitions();

    $url_components = Str\split($url, '/')
      |> Vec\filter($$, $component ==> !Str\is_empty($component));

    if (C\is_empty($url_components)) {
      // Home route
      return $route_string.self::ROUTE_END;
    } else {
      $route_string .= self::SLASH;
    }

    foreach ($url_components as $component) {
      $is_param = \preg_match(self::PARAMETER_PATTERN, $component);
      if ($is_param) {
        $param_name = Str\slice($component, 1, Str\length($component) - 2);
        $is_optional = Str\starts_with($param_name, '?');
        if ($is_optional) {
          $param_name = Str\slice($param_name, 1);
        }

        if (!C\contains_key($definitions, $param_name)) {
          throw new ParamNotFoundException();
        }

        $param_type = $definitions[$param_name];
        switch ($param_type) {
          case ParamType::STRING:
            $route_string .= self::makeParam(self::STRING_PARAM, $is_optional);
            break;
          case ParamType::INT:
            $route_string .= self::makeParam(self::INT_PARAM, $is_optional);
            break;
          case ParamType::FLOAT:
            $route_string .= self::makeParam(self::FLOAT_PARAM, $is_optional);
            break;
        }
      } else {
        $route_string .= $component;
      }
    }
    $route_string .= self::ROUTE_END;
    return $route_string;
  }

  private async function generateURLMapAsync(): Awaitable<void> {
    $cg = new HackCodegenFactory(new HackCodegenConfig());
    $cg->codegenFile(self::getURLClassFilePath())
      ->addClass(
        $cg->codegenClass('UrlMap')
          ->addConst(
            'dict<string, classname<BaseController>> URL_GET_PATTERNS',
            $this->get_routes,
            '',
            HackBuilderValues::dict(
              HackBuilderKeys::lambda(
                // But why not use regex prefix strings?!
                // Because they are type Pattern under the hood,
                // so they're not allowed as arraykeys.
                // womp womp
                ($_config, $value) ==> '"'.Str\replace_every(
                    (string)$value,
                    dict['$' => '\\$', '"' => '\\"'],
                  ).
                  '"',
              ),
              HackBuilderValues::classname(),
            ),
          )
          ->addConst(
            'dict<string, classname<BaseController>> URL_POST_PATTERNS',
            $this->post_routes,
            '',
            HackBuilderValues::dict(
              HackBuilderKeys::lambda(
                ($_config, $value) ==> '"'.Str\replace_every(
                    (string)$value,
                    dict['$' => '\\$', '"' => '\\"'],
                  ).
                  '"',
              ),
              HackBuilderValues::classname(),
            ),
          )
          ->addMethod(
            $cg->codegenMethod('getPatternsForMethod')
              ->setIsOverride(false)
              ->setIsStatic(true)
              ->setReturnType('dict<string, classname<BaseController>>')
              ->addParameter('HttpMethod $method')
              ->setBody(
                $cg->codegenHackBuilder()
                  ->startSwitch('$method')
                  ->addCase('HttpMethod::GET', HackBuilderValues::literal())
                  ->returnCase(
                    'self::URL_GET_PATTERNS',
                    HackBuilderValues::literal(),
                  )
                  ->addCase('HttpMethod::POST', HackBuilderValues::literal())
                  ->returnCase(
                    'self::URL_POST_PATTERNS',
                    HackBuilderValues::literal(),
                  )
                  ->addDefault()
                  ->addReturn(
                    'self::URL_GET_PATTERNS',
                    HackBuilderValues::literal(),
                  )
                  ->endDefault()
                  ->endSwitch()
                  ->getCode(),
              ),
          ),
      )
      ->setIsSignedFile(true)
      ->save();
  }

  private static function getURLClassFilePath(): string {
    return MirUtil::getGeneratedPath().'/'.self::URL_MAP_CLASS_NAME.'.php';
  }

  private static function makeParam(
    string $pattern,
    bool $optional = false,
  ): string {
    return Str\format('(\/%s)%s', $pattern, $optional ? '?' : '');
  }

}

class ParamNotFoundException extends Exception {}
