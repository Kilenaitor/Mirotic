<?hh // strict

final class NotImplementedException extends BaseException {
  <<__Override>>
  public function __construct(): void {
    parent::__construct('Not Implemented', ErrorCode::NOT_IMPLEMENTED);
  }
}
