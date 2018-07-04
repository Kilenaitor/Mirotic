<?hh // strict

abstract class BaseAjaxController extends BasePostController {

  protected abstract function genResponse(): Awaitable<?string>;

  <<__Override>>
  final public async function genRender(): Awaitable<?:xhp> {
    $response_body = await $this->genResponse();
    // We explicitly want this to be plain text. Not XHP.
    echo $response_body;
    return null;
  }

}
