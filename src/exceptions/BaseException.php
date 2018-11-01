<?hh // strict

class BaseException extends Exception {

  protected string $message;
  protected ErrorCode $error_code;

  // Redefine the exception so message isn't optional
  public function __construct(string $message, ErrorCode $error_code): void {
    $this->message = $message;
    $this->error_code = $error_code;
    parent::__construct($message, $error_code);
  }

  <<__Override>>
  public function __toString(): string {
    return __CLASS__.": [{$this->code}]: {$this->message}\n";
  }

  <<__Override>>
  public function getCode(): ErrorCode {
    return $this->error_code;
  }

}
