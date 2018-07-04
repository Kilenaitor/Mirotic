<?hh // strict

final class PageNotFoundException extends BaseException {
  <<__Override>>
  public function __construct(): void {
    parent::__construct('Page Not Found', ErrorCode::NOT_FOUND);
  }
}
