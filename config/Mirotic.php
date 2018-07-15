<?hh // strict

abstract class Mirotic {
  /* Framework */
  const VERSION = 1.0;

  /* App */
  const APP_NAME = 'Mirotic';
  const DOMAIN = 'localhost';

  const vec<string>
    CONTROLLERS_TO_IGNORE_FROM_URLMAP = vec[
      'BaseAjaxController.php',
      'BaseController.php',
      'BaseGetController.php',
    ];
}
