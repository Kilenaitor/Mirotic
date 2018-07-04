<?hh // strict

class HomePage extends BasePage {

  <<__Override>>
  public function render(dict<string, mixed> $args): :xhp {
    $project_name = Mirotic::APP_NAME;
    return <dummy app-name={$project_name} />;
  }

}
