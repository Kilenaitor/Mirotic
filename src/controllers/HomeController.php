<?hh // strict

final class HomeController extends BaseGetController {

  <<__Override>>
  public static function getURL(): string {
    return '/';
  }

  <<__Override>>
  public async function renderAsync(): Awaitable<?:xhp> {
    return (new HomePage())->getHTML(dict[]);
  }

}
