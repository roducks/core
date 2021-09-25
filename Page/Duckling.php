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

namespace Roducks\Page;

use Roducks\Files\Cache;
use Roducks\Page\Assets;
use Roducks\Routing\Path;
use Roducks\Framework\App;
use Roducks\Lib\Files\File;
use Roducks\Framework\Helper;
use Roducks\Lib\Output\Error;
use Roducks\Framework\Autoload;

final class Duckling {
  private static $cache = [];

  private static function _getIndex($i, $v)
  {
    return (is_int($i)) ? $i+1 : $v;
  }

  private static function _getParams($args)
  {
    $params = explode(',', $args);
    $params = array_map(function ($param) {
      return str_replace("'", '', $param);
    }, $params);

    return $params;
  }

  private static function _getPairs(array $params)
  {
    $pairs = [];

    if (!empty($params)) {
      foreach ($params as $param) {
        list($key, $value) = explode(':', $param);
        $pairs[$key] = $value;
      }
    }

    return $pairs;
  }

  public static function links($view)
  {
    $view = preg_replace_callback('/\{% link \'([a-z0-9_\.]+)\'(\s\|([a-zA-Z0-9:,\s\'\-\._]+)\|)? %\}/', function($matches) {
      $index = $matches[1];
      $uri = Autoload::$links[$index] ?? NULL;
      $hasParams = $matches[3] ?? NULL;
      $params = (!empty($hasParams)) ? Duckling::_getParams($hasParams) : [];
      $pairs = Duckling::_getPairs($params);

      if (!empty($uri)) {
        $uri = preg_replace_callback('/\{([a-z]+):[a-z]+\}/', function($match) use ($pairs) {
          $key = $match[1];
          return $pairs[$key] ?? '#';
        }, $uri);
      }

      return $uri ?? "#{$index}";
    }, $view);

    return $view;
  }

  public static function assets($view, array $settings = [])
  {
    $view = preg_replace_callback('/\{% asset(:(theme|module|library|block))? \'([a-zA-Z0-9\.\-_\/]+\.(jpg|jpeg|png|svg))\' %\}/', function ($matches) use ($settings) {
      $type = !empty($matches[2]) ? $matches[2] : 'global';

      switch ($type) {
        case 'theme':
          $file = $matches[3];
          return Assets::getFile('Theme', $file);
          break;
        case 'module':
          $file = $matches[3];
          return Assets::getFile('Module', $file);
          break;
        case 'block':
          $file = $matches[3];
          return Assets::getFile('Block', $file, $settings);
          break;
        case 'library':
          $split = explode('/', $matches[3]);
          $library = $split[0];
          unset($split[0]);
          $file = implode('/', $split);
          return Assets::getFile('Library', $file, ['library' => $library]);
          break;
        case 'global':
          $file = $matches[3];
          return "/assets/{$file}";
          break;
      }

      return '';
    }, $view);

    return $view;
  }

  public static function text($view)
  {
    $view = preg_replace_callback('/\{% text "([a-z_\.]+)"(\s\|([a-zA-Z0-9,\s\'\-\._]+)\|)? %\}/', function ($matches) {
      $index = $matches[1] ?? NULL;
      $hasParams = $matches[3] ?? NULL;
      $params = (!empty($hasParams)) ? Duckling::_getParams($hasParams) : [];

      return Autoload::$text->get($index, $params);
    }, $view);

    return $view;
  }

  public static function includeTpl($view)
  {
    $view = preg_replace_callback('/\{% include "([a-zA-Z\-_]+)"(\s\|([a-zA-Z0-9:,\s\'\-\._]+)\|)? %\}/', function ($matches) {
      $path = Path::getTemplate(App::getTheme(), $matches[1] . Path::HTML_EXT);
      $hasParams = $matches[3] ?? NULL;
      $params = (!empty($hasParams)) ? Duckling::_getParams($hasParams) : [];
      $pairs = Duckling::_getPairs($params);

      return !empty($path) && File::exists($path) ? Duckling::render($path, $pairs) : '';
    }, $view);

    return $view;
  }

  public static function invokeBlock($view)
  {
    $view = preg_replace_callback('/\{% block "([a-zA-Z\-_\.]+)"(\s\|([a-zA-Z0-9,\s\'\-\._]+)\|)? %\}/', function ($matches) {
      $name = $matches[1];
      $hasParams = $matches[3] ?? NULL;
      $params = (!empty($hasParams)) ? Duckling::_getParams($hasParams) : [];
      $block = Autoload::block($name, $params);

      return $block;
    }, $view);

    return $view;
  }

  public static function getVar($view, array $data)
  {
    $view = preg_replace_callback('/\{\{\s?([a-z0-9\._+]+)\s?\}\}/', function ($matches) use ($data) {
      $key = $matches[1] ?? NULL;

      if (isset($data[$key])) {
        if (is_array($data[$key])) {
          return '{{ ' . $key . ' }}';
        }
      }

      return Helper::getVar($key, $data, FALSE, TRUE);
    }, $view);

    return $view;
  }

  public static function forLoop($regexp, $view, $data)
  {
    $view = preg_replace_callback('#' . $regexp . '#sm', function ($matches) use ($data) {
      preg_match('/^([a-zA-Z,\s]+) in ([a-zA-Z\.]+)$/', $matches[1], $match);

      $i = 'i';
      $block = $matches[2];
      $key = $match[1];
      $value = $match[2];

      if (preg_match('/,/', $key)) {
        list($i, $key) = explode(', ', $key);
      }

      if (isset($data[$value])) {
        if (is_array($data[$value])) {
          $tpl = '';

          foreach ($data[$value] as $index => $item) {
            static::$cache[$key][] = $item;
            $test = $block;
            $test = self::getVar($test, [$key => $item, $i => $index, "{$i}+" => self::_getIndex($i, $index)]);
            $tpl .= $test;
          }
        }

        // Helper::debug(static::$cache);
      }

      return $tpl;
    }, $view);

    return $view;
  }

  public static function clean($view)
  {
    return preg_replace('/(\n\n|\s\s+\n)/', "\n", $view);
  }

  private static function _loops($view)
  {
    $lines = explode("\n", $view);
    $cache = [];
    $end = 0;
    $c = 0;
    $pair = [];

    foreach ($lines as $line) {
      if (preg_match('/{\% (end)?for /', $line)) {
        if (preg_match('/{\% for/', $line)) {
          array_push($cache, $line);
        }
        else if (preg_match('/\{% endfor %\}/', $line)) {
          $index = count($cache) - 1;
          $for = array_pop($cache);
          unset($cache[$index]);

          preg_match('/\{% for (([a-zA-Z,\s]+) in ([a-zA-Z\.]+)) %\}/', $for, $match);
          $pair[$end] = $match[1];
          $end++;
        }
      }
    }

    $view = preg_replace_callback('/\{% endfor %\}/', function () use (&$c, $pair) {
      $ret = '{% endfor ' . $pair[$c] . ' %}';
      $c++;

      return $ret;
    }, $view);

    return $view;
  }

  private static function _loopsMap($view, array $data)
  {
    preg_match_all('/\{% for ([a-zA-Z,\s]+ in [a-zA-Z]+) %\}/', $view, $matches);

    $loops = [];
    $head = $matches[0];
    $tail = $matches[1];

    for ($i=0; $i < count($head); $i++) {
      $for = "{% for ({$tail[$i]}) %}(.*?){% endfor {$tail[$i]} %}";
      $loops[$i] = [
        'parent' => $for,
        'children' => [],
      ];

      preg_match_all('#' . $for . '#sm', $view, $inner);
      preg_match_all('/\{% for ([a-zA-Z,\s]+ in [a-zA-Z\.]+) %\}/', $inner[2][0], $children);

      $loops[$i]['children'] = array_map(function ($value) {
        preg_match('/^([a-zA-Z,\s]+) in ([a-zA-Z\.]+)$/', $value, $match);
        $split = explode('.', $match[2]);

        return [
          'rule' => "{% for ($value) %}(.*?){% endfor {$value} %}",
          'args' => [
            'key' => $match[1],
            'value' => end($split),
          ],
        ];
      }, $children[1]);
    }

    // Helper::debug($loops);

    foreach ($loops as $loop) {
      $view = self::forLoop($loop['parent'], $view, $data);
    }

    foreach ($loops as $loop) {
      foreach ($loop['children'] as $r => $child) {
        $c = 0;
        $view = preg_replace_callback('#' . $child['rule'] . '#sm', function ($matches) use (&$c, $r) {
          preg_match('/^([a-zA-Z,\s]+) in ([a-zA-Z\.]+)$/', $matches[1], $match);

          $i = 'i';
          $key = $match[1];

          if (preg_match('/,/', $key)) {
            list($i, $key) = explode(', ', $key);
          }

          list($index, $value) = explode('.', $match[2]);

          $data = static::$cache[$index][$c][$value];
          $block = $matches[2];
          $block = self::getVar($block, [$key => $data]);

          static::$cache[$key][$c] = $data;

          if (is_int(array_keys($data)[0])) {
            $tpl = '';

            foreach ($data as $idx => $item) {
              $tpl .= self::getVar($block, [$key => $item, $i => $idx, "{$i}+" => self::_getIndex($idx, $i)]);
            }

            $block = $tpl;
          }

          $block = self::getVar($block, [$i => $c, "{$i}+" => self::_getIndex($c, $i)]);

          $c++;

          return $block;
        }, $view);
      }
    }

    // Helper::debug(static::$cache);

    return $view;
  }

  public static function render($path, array $data = [], array $settings = [])
  {
    if (!empty($path) && File::exists($path)) {
      $view = File::getContent($path);
      $view = self::parse($view, $data, $settings);

      return $view;
    }
    else {
      throw new \Exception("No view was found.");
    }
  }

  public static function parse($view, array $data, array $settings = [])
  {
    $view = self::_loops($view);
    $view = self::_loopsMap($view, $data);
    $view = self::text($view);
    $view = self::links($view);
    $view = self::getVar($view, $data);
    $view = self::includeTpl($view);
    $view = self::invokeBlock($view);
    $view = self::assets($view, $settings);
    $view = self::clean($view);

    return $view;
  }
}
