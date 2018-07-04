<?hh // strict

final class BadRequestException extends BaseException {
  <<__Override>>
  public function __construct(): void {
    parent::__construct('Bad Request', ErrorCode::BAD_REQUEST);
  }
}
