<?hh

require_once(__DIR__.'/../src/lib/init.php');

// The URL provided
$request_path = $_SERVER['REQUEST_URI'];
$request_method = HttpMethod::assert($_SERVER['REQUEST_METHOD']);

try {
  $controller = \HH\Asio\join(Router::genController());
  if (Router::getRequestMethod() !== HttpMethod::HEAD) {
    // TODO: Return headers if it's a HEAD requ4est
    echo \HH\Asio\join($controller->renderAsync());
  }
} catch (Exception $e) {
  if ($e instanceof BaseException) {
    // Something we've implemented and have a handle on
    http_response_code($e->getCode());
    echo (new BaseErrorPage())->render(
      <div>{$e->getCode()}: {$e->getMessage()}</div>,
    );
  } else {
    // Something went really wrong
    http_response_code(500);
    error_log($e);
  }
}
