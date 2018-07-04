<?hh // strict

class BaseException extends Exception {

  protected string $message;
  protected ErrorCode $code;

  // Redefine the exception so message isn't optional
  public function __construct(string $message, ErrorCode $code): void {
    // make sure everything is assigned properly
    $this->message = $message;
    $this->code = $code;
    parent::__construct($message, $code);
  }

  public function __toString(): string {
    return __CLASS__.": [{$this->code}]: {$this->message}\n";
  }

  <<__Override>>
  public function getCode(): ErrorCode {
    return $this->code;
  }

}
