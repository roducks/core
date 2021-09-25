<?php
/**
 *
 * This file is part of Roducks.
 *
 *    Roducks is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation, either version 3 of the License, or
 *    (at your option) any later version.
 *
 *    Roducks is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    You should have received a copy of the GNU General Public License
 *    along with Roducks.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace Roducks\Framework;

use Roducks\Files\Cache;
use Roducks\Di\Container;
use Roducks\Files\Config;
use Roducks\Routing\Path;
use Roducks\Lib\Output\Error;
use Roducks\Lib\Request\Http;

abstract class Autoload {
  const DEFAULT_MODULE = 'App';

  public static $container = NULL;
  public static $errors = FALSE;
  public static $site = 'Portal';
  public static $env = '@prod';
  public static $module = NULL;
  public static $theme = NULL;
  public static $text = NULL;
  public static $links = [];
  public static $blocks = [];
  public static $cmd = NULL;
  public static $debugger = FALSE;

  /**
   * 
   */
  private static function _invokeMethod($obj, $method, $params)
  {
    if (method_exists($obj, $method)) {
      call_user_func_array([$obj, $method], $params);
    }
    else {
      Error::debug(["'{$method}' method does not exist."]);
    }
  }

  public static function getDefaultModule()
  {
    return 'Roducks\\Modules\\'. static::DEFAULT_MODULE .'\\Page\\'. static::DEFAULT_MODULE;
  }

  /**
   * 
   */
  public static function dispatch($dispatcher, array $settings = [], array $params = [])
  {
    list($class, $method) = Helper::getDispatcher($dispatcher);

    if (class_exists($class)) {
      $settings['dispatch']['class'] = $class;
      $settings['dispatch']['method'] = $method;
      static::$container->set('settings', $settings);
      $obj = $class::init(static::$container);

      if (isset($settings['di'])) {
        static::$container->set($settings['di'], $obj)->lock();

        if (isset($settings['observer'])) {
          self::_invokeMethod($obj, $method, $params);
        }
      }
      else {
        self::_invokeMethod($obj, $method, $params);
      }
    }
    else {
      Error::debug(["'{$class}' class does not exist."]);
    }
  }

  /**
   * 
   */
  public static function database()
  {
    $dbCreds = Config::getContent(Path::getAppSiteConfig(static::$site, 'database'));
    $services = Config::getCoreServices();
    $host = $dbCreds['host'] ?? NULL;
    $db = $dbCreds['db'] ?? NULL;
    $user = $dbCreds['user'] ?? NULL;
    $password = $dbCreds['password'] ?? NULL;
    $port = $dbCreds['port'] ?? NULL;
    $settings = [
      'di' => 'db',
      'credentials' => [
        'host' => Http::getEnv(Helper::getEnvVar($host)) ?? 'localhost',
        'db' => Http::getEnv(Helper::getEnvVar($db)) ?? '',
        'user' => Http::getEnv(Helper::getEnvVar($user)) ?? '',
        'password' => Http::getEnv(Helper::getEnvVar($password)) ?? '',
        'port' => intval(Http::getEnv(Helper::getEnvVar($port))) ?? 3306,
      ],
    ];

    self::dispatch($services['db']['dispatch'], $settings);
  }

  /**
   * 
   */
  private static function _dependencies($services, $dispatchers, array &$list)
  {
    foreach ($services as $di) {
      if (isset($dispatchers[$di])) {
        $list[$di] = $dispatchers[$di]['dispatch'];
        if (isset($dispatchers[$di]['services'])) {
          self::_dependencies($dispatchers[$di]['services'], $dispatchers, $list);
        }
      }
    }
  }

  /**
   * 
   */
  public static function dependencies($services, $dispatchers)
  {
    $list = [];
    self::_dependencies($services, $dispatchers, $list);
    $list = array_reverse($list);

    foreach ($list as $di => $dispatch) {
      self::loadDependencies($dispatchers[$di]);
      self::dispatch($dispatch, ['di' => $di, 'services' => $services]);
    }
  }

  public static function loadDependencies(array $config)
  {
    if (isset($config['services'])) {
      self::dependencies($config['services'], Cache::getServices(static::$site));
    }
  }

  /**
   * 
   */
  public static function init($cors = NULL)
  {
    // Display errors
    Error::display(Autoload::$errors);

    // Initialize Container
    static::$container = Container::init();
    static::$container->set('settings', []);

    if ($cors) {
      static::$container->set('cors', $cors);
    }
  }

  public static function run($config)
  {
    // Connect to a MySQL database
    self::database();

    // Local dependency services
    self::loadDependencies($config);
  }

  /**
   * 
   */
  public static function observer($name, array $params = [])
  {
    $observers = Cache::get(Autoload::$site, 'observers');

    if (isset($observers[$name])) {
      foreach ($observers[$name] as $i => $observer) {
        self::loadDependencies($observer);
        self::dispatch($observer['dispatch'], ['di' => $name . $i, 'observer' => 1], $params);
      }
    }
  }

  public static function block($name, array $params = [])
  {
    $class = self::$blocks[$name]['dispatch'] ?? NULL;
    $settings['dispatch']['id'] = $name;
    ob_start();

    if (!empty($class)) {
      self::dispatch($class, $settings, $params);
    }
    else {
      Error::alert([
        'title' => "Block",
        'message' => "'{$name}' is not defined.",
      ]);
    }

    $block = ob_get_contents();
    ob_end_clean();

    return $block;
  }

}
