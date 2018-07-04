<?hh // strict

abstract class PlainPage {

  abstract public function render(dict<string, mixed> $args): :xhp;

  final public function getHTML(dict<string, mixed> $args): :xhp {
    return
      <html>
        <head>
          <meta charset="UTF-8" />
        </head>
        <body>
        </body>
      </html>;
  }

}
