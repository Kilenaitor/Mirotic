<?hh // strict

class UrlBuilder {

  private function __construct(
    private string $url,
  ) {}

  public static function for(classname<BaseController> $controller): this {
    return new static($controller::getURL());
  }

  public function set(string $param_name, string $param_value): this {
    $this->url =
      \preg_replace('/\{'.$param_name.'\}/', $param_value, $this->url, 1);
    return $this;
  }

  public function getURL(): string {
    $this->url = \preg_replace('/\{[A-z-_]+\}\/?/', '', $this->url);
    return $this->url;
  }

  public static function getCurrentURL(): string {
    $domain = Mirotic::DOMAIN;
    /* HH_IGNORE_ERROR[2050] It's defined. I checked. */
    $request_path = $_SERVER['REQUEST_URI'];
    return 'https://'.$domain.$request_path;
  }

}
