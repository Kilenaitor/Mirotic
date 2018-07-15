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

final class MirURLMapCodegen {

  const type TUrlWithType = shape(
    'url' => string,
    'type' => HTTPMethod,
  );

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
    $urls_and_types = Vec\map(
      $controllers,
      $controller ==> self::getURLFromController($controller),
    );
    print_r($urls_and_types);
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

  private static function getURLFromController(
    classname<BaseController> $controller,
  ): self::TUrlWithType {
    return shape(
      'url' => $controller::getURL(),
      'type' => $controller::getHTTPMethod(),
    );
  }

}
