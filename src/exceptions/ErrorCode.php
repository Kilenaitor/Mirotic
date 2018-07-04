<?hh // strict

enum ErrorCode: int as int {

  // Reserved by HTTP
  BAD_REQUEST = 400;
  UNAUTHENTICATED = 401;
  UNAUTHORIZED = 403;
  NOT_FOUND = 404;
  INVALID_METHOD = 405;

}
