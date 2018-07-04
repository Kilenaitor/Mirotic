<?hh // strict

class BaseErrorPage {

  public function render(?:xhp $body): :xhp {
    return
      <html>
        <head>
          <meta charset="UTF-8" />
          <link rel="stylesheet" href="/public/css/base.css" />
        </head>
        <body>
          <h1> Error </h1>
          {$body}
        </body>
      </html>;
  }

}
