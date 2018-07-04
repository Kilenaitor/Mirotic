<?hh // strict

abstract class BasePage {

  const string BASE_TITLE = 'Mirotic';
  private string $title = self::BASE_TITLE;

  final protected function setTitle(string $title): void {
    $this->title = $title;
  }

  final protected function setTitleWithDivider(string $title): void {
    $this->title = self::BASE_TITLE.' | '.$title;
  }

  protected function pageMeta(): vec<:xhp> {
    return vec[];
  }

  protected function pageCSS(): vec<:xhp> {
    return vec[];
  }

  protected function pageJS(): vec<:xhp> {
    return vec[];
  }

  private function renderHead(): :xhp {
    $head = <head></head>;
    foreach ($this->pageMeta() as $meta_tag) {
      $head->appendChild($meta_tag);
    }
    foreach ($this->pageCSS() as $stylesheet) {
      $head->appendChild($stylesheet);
    }
    return $head;
  }

  private function renderJS(): :xhp {
    $js = <div></div>;
    foreach ($this->pageJS() as $script) {
      $js->appendChild($script);
    }
    return $js;
  }

  abstract public function render(dict<string, mixed> $args): :xhp;

  final public function getHTML(dict<string, mixed> $args): :xhp {
    /*
     * This needs to get called **first** because of how metadata works.
     */
    $page_contents = $this->render($args);

    // Now that the page content initialized, we can render the final doc
    return
      <html>
        {$this->renderHead()}
        <body>
          {$page_contents}
          {$this->renderJS()}
        </body>
      </html>;
  }
}
