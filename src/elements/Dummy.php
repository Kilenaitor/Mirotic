<?hh // strict

class :dummy extends :x:element {

  attribute string app-name;

  use XHPHelpers;

  protected function render(): XHPRoot {
    return
      <div style="font-family: Helvetica, sans-serif;">
        <h1>Welcome to {$this->:app-name}!</h1>
        <p>
          {$this->:app-name} is a lightweight, configurable, easy-to-use MVC
          framework built on the powerful Hack language running on HHVM.
        </p>
      </div>;
  }
}
