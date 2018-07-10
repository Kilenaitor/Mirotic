<?hh // strict

class BlankPage extends BasePage {

  <<__Override>>
  public function render(dict<string, mixed> $args): :xhp {
    return <div></div>;
  }

}
