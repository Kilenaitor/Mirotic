<?hh // strict

final class PermissionDeniedException extends BaseException {
  <<__Override>>
  public function __construct(): void {
    parent::__construct(
      'You do not have permission to view this content or it does not exist',
      ErrorCode::UNAUTHORIZED,
    );
  }
}
