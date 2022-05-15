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

namespace Roducks\Routing;

use Roducks\Page\Json;
use Roducks\Files\Cache;
use Roducks\Routing\Url;
use Roducks\Files\Config;
use Roducks\Framework\App;
use Roducks\Files\Resource;
use Roducks\Lib\Files\File;
use Roducks\Framework\Helper;
use Roducks\Lib\Data\Session;
use Roducks\Lib\Output\Error;
use Roducks\Lib\Request\Cors;
use Roducks\Lib\Request\Http;
use Roducks\Framework\Autoload;
use Roducks\Lib\Request\Request;

abstract class Dispatcher {
  const CLI_METHOD = 'run';

  /**
   * 
   */
  private static function _init($dir)
  {
    $dir = str_replace('/public', '', $dir) . DIRECTORY_SEPARATOR;

    if (!defined('RDKS_ROOT')) {
      define('RDKS_ROOT', $dir);
    }

    // Set default module
    Autoload::$module = Autoload::DEFAULT_MODULE;
  }

  /**
   * 
   */
  private static function _getCliArgs($args)
  {
    unset($args[0]);
    unset($args[1]);
    $data = [
      'flags' => [],
      'shortcode' => [],
      'params' => [],
    ];

    foreach ($args as $arg) {
      if (preg_match('/^-/', $arg)) {
        $key = $arg;
        $value = 1;

        if (preg_match('/=/', $arg)) {
          list($key, $value) = explode('=', $arg);
        }

        $index = preg_match('/^--/', $arg) ? 'flags' : 'shortcode';
        $data[$index][$key] = $value;
      }
      else {
        $data['params'][] = $arg;
      }
    }

    return $data;
  }

  /**
   * 
   */
  private static function _getBody()
  {
    $requestedMethod = Http::getRequestMethod();
    $body = [];

    switch ($requestedMethod) {
      case 'GET':
        $body = Url::getQuery();
        break;
      case 'POST':
        $body = Request::getPost();
        break;
      case 'PUT':
      case 'DELETE':
        $body = Request::getBody();
        break;
    }

    return $body;
  }

  private static function _getPlaceholders($matches)
  {
    $data = [];

    foreach ($matches as $index => $match) {
      if (!is_int($index) && !empty($match)) {
        $data[] = preg_match('/^\d+$/', $match) ? intval($match) : $match;
      }
    }

    return $data;
  }

  private static function _pageSettings($method = 'notFound')
  {
    $siteConfig = Config::getContent(Path::getAppSiteConfig(Autoload::$site, 'config'));
    $class = (isset($siteConfig['page.not.found']['dispatch']) && $method != 'debugger') 
    ? $siteConfig['page.not.found']['dispatch'] 
    : Autoload::getDefaultModule();
    $services = (isset($siteConfig['page.not.found']['services']) && $method != 'debugger')
    ? $siteConfig['page.not.found']['services']
    : [];

    return [
      'id' => 'page.not.found',
      'dispatch' => Helper::setDispatcher([$class, $method]),
      'path' => Path::getCoreModuleConfig(Autoload::DEFAULT_MODULE),
      'services' => [
        'view',
      ] + $services,
    ];
  }

  public static function _dispatch($method, array $messages = [])
  {
    $settings = self::_pageSettings($method);
    Http::sendHeaderNotFound(FALSE);

    // Initialize
    Autoload::init(Cors::init());

    // Run
    Autoload::run([
      'services' => $settings['services'], 
    ]);

    // Dispatch
    Autoload::dispatch($settings['dispatch'], ['messages' => $messages]);

    exit;
  }

  public static function debugger(array $messages)
  {
    Autoload::$debugger = TRUE;
    self::_dispatch('debugger', $messages);
  }

  public static function pageNotFound()
  {
    self::_dispatch('notFound');
  }

  /**
   * 
   */
  public static function web($dir)
  {
    // Avoid dispatching public assets
    if (preg_match('/^\/assets/', Http::getUri())) {
      Http::sendHeaderNotFound(TRUE);
    }

    self::_init($dir);
    $sites = Cache::getSites();
    $serverName = Http::getServerName();

    if (empty($sites)) {
      Error::debug(["No site was configured."]);
    }

    if (isset($sites[$serverName])) {
      $siteConfig = $sites[$serverName];

      if (isset($siteConfig['errors'])) {
        Autoload::$errors = $siteConfig['errors'];
      }

      if (isset($siteConfig['site'])) {
        Autoload::$site = $siteConfig['site'];
      }

      if (isset($siteConfig['env'])) {
        Autoload::$env = $siteConfig['env'];
      }
    }
    else {
      Error::debug(["Domain is not registered."]);
    }

    $routing = Cache::getRouting(Autoload::$site);
    $siteConfig = Config::getContent(Path::getAppSiteConfig(Autoload::$site, 'config'));
    $uri = Http::getUri();
    $found = FALSE;
    $settings = [];

    if (empty($routing)) {
      Error::debug(["Routes are missing!"]);
    }

    foreach ($routing as $route => $config) {
      $route = preg_replace_callback('#{([a-zA-Z_]+):(int|chars|str|slug)}#', function ($match) {
        switch ($match[2]) {
          case 'int':
            $type = '\d';
            break;
          case 'chars':
            $type = '\w';
            break;
          case 'str':
            $type = '.';
            break;
          case 'slug':
            $type = '[a-z0-9\-]';
            break;
        }

        $name = $match[1];

        return "(?P<{$name}>{$type}+)";
      }, $route);

      if (preg_match('#^' . $route . Url::REGEXEP_QUERY_STRING, $uri, $matches)) {
        $found = TRUE;
        break;
      }

    }

    if (!$found) {
      $matches = [];
      $config = self::_pageSettings();
    }

    $placeholders = self::_getPlaceholders($matches);
    $dispatcher = $config['dispatch'];
    $moduleName = Helper::getModule($dispatcher);

    if (empty($moduleName)) {
      Error::debug(['Url cannot be dispatched.']);
    }

    $module = [
      'name' => $moduleName,
      'enabled' => File::exists(Path::getStorage(App::getSite() . "/modules/{$moduleName}" . Path::YML_EXT)),
    ];

    Autoload::$module = $module['name'];

    if (!$module['enabled']) {
      Error::debug(["'{$module['name']}' module not enabled."]);
    }

    // View dependency is required to dispatch a Page.
    if (!isset($config['services'])) {
      $config['services'] = ['view'];
    }

    if (!in_array('view', $config['services'])) {
      array_unshift($config['services'], 'view');
    }

    // Form dependency is required for Json pages.
    if (preg_match('/Json/', $dispatcher)) {
      array_push($config['services'], 'form');
    }

    $body = self::_getBody();
    $request = Request::init($body);
    $settings['dispatch']['id'] = $config['id'];
    $settings['request'] = $request;
    $params = [];
    $params[] = $request;
    $params = array_merge($params, $placeholders);
    $requestedMethod = Http::getRequestMethod();
    $type = $config['type'] ?? 'GET';
    $cors = Cors::init();
    $cors->allowedMethods([$type]);

    if (isset($config['api'])) {
      switch ($requestedMethod) {
        case 'GET':
          $method = empty($placeholders) ? 'catalog' : 'get';
          break;
        case 'POST':
          $method = 'store';
          break;
        case 'PUT':
          $method = 'update';
          break;
        case 'DELETE':
          $method = 'delete';
          break;
      }

      if (isset($config['jwt'])) {
        $settings['jwt'] = $config['jwt'];
      }

      if (isset($config['methods'])) {
        $cors->allowedMethods($config['methods']);

        if (!in_array($requestedMethod, $config['methods'])) {
          Http::sendMethodNotAllowed(FALSE);
          Http::setJsonHeader();
          echo Json::encode([
            'error' => TRUE,
            'message' => 'Method not allowed',
          ]);
          exit;
        }
      }

      // If no method is provided, set one automatic.
      if (!preg_match('/::/', $dispatcher)) {
        $dispatcher = Helper::setDispatcher([$dispatcher, $method]);
      }
    }
    else {
      if ($type !== $requestedMethod) {
        Http::sendMethodNotAllowed();
      }
    }

    // Load Links
    Autoload::$links = Cache::getLinks(Autoload::$site);

    // Set Http Headers
    $cors->apply();

    // Start session
    Session::start();

    // Initialize
    Autoload::init($cors);

    // Initialize
    Autoload::run($config);

    // Timezone
    if (isset($siteConfig['timezone'])) {
      // Allows to alter timezone by an Observer
      Autoload::observer('timezone', [&$siteConfig]);
      date_default_timezone_set($siteConfig['timezone']);
    }

    // Dispatch Html Page
    Autoload::dispatch($dispatcher, $settings, $params);
  }

  /**
   * 
   */
  public static function cli($args, $dir)
  {
    self::_init($dir);
    $cmd = $args[1] ?? NULL;
    $flag = FALSE;
    Autoload::$cmd = $cmd;

    if (empty($cmd)) {
      Autoload::$errors = TRUE;
      Error::debug([
        'No command was given.',
        '',
        'Run:',
        'php roducks help',
      ]);
    }

    $settings = self::_getCliArgs($args);
    $flags = array_merge($settings['flags'], $settings['shortcode']);
    $site = $settings['flags']['--site'] ?? Autoload::$site;
    Autoload::$errors = (isset($settings['flags']['--verbose']) || isset($settings['shortcode']['-vvv'])) ?? FALSE;
    $config = Cache::getCli($site);

    if (preg_match('/^\-/', $cmd)) {
      $cmdKey = str_replace('-', '_', $cmd);

      if (isset($config[$cmdKey]['parent'])) {
        $flag = TRUE;
        $cmd = $config[$cmdKey]['parent'];
      }
    }

    // the first time is run cache folder does not exist.
    if (empty($config)) {
      $config = Resource::getCli($site);
    }

    $cmdDispatcher = Helper::getDispatcher($cmd, static::CLI_METHOD, ':');
    $cmdName = $cmdDispatcher[0];
    $method = $cmdDispatcher[1];

    if (!isset($config[$cmdName])) {
      Error::debug(["Unknown Command {$cmd}"]);
    }

    $dispatcher = Helper::setDispatcher(Helper::getDispatcher($config[$cmdName]['dispatch'], $method));

    if ($method == static::CLI_METHOD && (!isset($config[$cmd]['parent']) || $flag)) {
      array_unshift($settings['params'], $cmd);
    }

    // Initialize
    Autoload::init();

    // Run
    Autoload::run($config[$cmdName]);

    // Run Command-Line
    Autoload::dispatch($dispatcher, $flags, $settings['params']);
  }
}
