<?hh

session_start();
require_once (__DIR__.'/../vendor/hh_autoload.php');
require_once (__DIR__.'/../config/mirotic.php');

// The URL provided
$request_path = $_SERVER['REQUEST_URI'];
$request_method = HTTPMethod::assert($_SERVER['REQUEST_METHOD']);

try {
  $controller = \HH\Asio\join(
    URLResolver::genControllerForURLAndMethod($request_path, $request_method)
  );
  echo \HH\Asio\join($controller->genRender());
} catch (Exception $e) {
  if ($e instanceof BaseException) {
    // Something we've implemented and have a handle on
    http_response_code($e->getCode());
    echo (new BaseErrorPage())->render(
     <div>{$e->getCode()}: {$e->getMessage()}</div>
    );
  } else {
    // Something went really wrong
    http_response_code(500);
    error_log($e);
  }
}
