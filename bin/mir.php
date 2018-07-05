<?hh

/* Autoload the application for context */
require_once(__DIR__.'/../src/lib/init.php');

function run(): int {
  echo "Welcome to Mirotic!\n";
  return 0;
}

run();
