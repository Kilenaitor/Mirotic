<?hh // strict

namespace Mirotic\Util;

class Cache {

  public static function getFromCache<T>(
    string $key,
    (function(): T) $callback,
    int $timeout = 0,
  ): T {
    $val = \apc_fetch($key);
    if ($val === false) {
      $val = $callback();
      \apc_store($key, $val, $timeout);
    }
    return $val;
  }

  public static async function genFromCache<T>(
    string $key,
    (function(): Awaitable<T>) $callback,
    int $timeout = 0,
  ): Awaitable<T> {
    $val = \apc_fetch($key);
    if ($val === false) {
      $val = await $callback();
      \apc_store($key, $val, $timeout);
    }
    return $val;
  }

  public static function invalidateKey(string $key): void {
    \apc_delete($key);
  }

}
