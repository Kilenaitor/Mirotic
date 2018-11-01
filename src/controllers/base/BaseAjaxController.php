<?hh // strict

abstract class BaseAjaxController extends BaseController {

  <<__Override>>
  public static function getHttpMethod(): HttpMethod {
    return HttpMethod::POST;
  }

  protected abstract /* async */ function getResponseAsync(): Awaitable<?string>;

  <<__Override>>
  final public async function renderAsync(): Awaitable<?:xhp> {
    $response_body = await $this->getResponseAsync();
    // We explicitly want this to be plain text. Not XHP.
    echo $response_body;
    return null;
  }

}
