<?hh

require_once(__DIR__.'/../src/lib/init.php');

try {
  if (
    !\HH\Lib\C\contains(HttpMethod::getValues(), Router::getRequestMethod())
  ) {
    throw new NotImplementedException();
  }
  $controller = \HH\Asio\join(Router::genController());
  if (Router::getRequestMethod() !== HttpMethod::HEAD) {
    // TODO: Return headers if it's a HEAD request
    echo \HH\Asio\join($controller->renderAsync());
  }
} catch (Exception $e) {
  if ($e instanceof BaseException) {
    // Something we've implemented and have a handle on
    \http_response_code($e->getCode());
    echo (new BaseErrorPage())->render(
      <div>{$e->getCode()}: {$e->getMessage()}</div>,
    );
  } else {
    // Something went really wrong
    \http_response_code(ErrorCode::INTERNAL_ERROR);
    \error_log($e);
  }
}
