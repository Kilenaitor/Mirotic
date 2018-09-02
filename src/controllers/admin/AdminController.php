<?hh // strict

class AdminController extends BaseGetController {

  <<__Override>>
  public static function getURL(): string {
    return '/admin/{name}/{?role}';
  }

  <<__Override>>
  public static function getParamDefinitions(): dict<string, ParamType> {
    return dict[
      'name' => ParamType::STRING,
      'role' => ParamType::STRING,
    ];
  }

  <<__Override>>
  public async function renderAsync(): Awaitable<?:xhp> {
    return (new BlankPage())->getHTML(dict[]);
  }
}
