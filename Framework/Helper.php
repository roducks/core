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

abstract class Helper {

  public static function pre($obj, $die = FALSE): void
  {
    echo '<pre>';
    print_r($obj);
    echo '</pre>';

    if ($die) {
      exit;
    }
  }

  public static function debug($obj): void
  {
    self::pre($obj, TRUE);
  }

  public static function getEnvVar($name)
  {
    return preg_replace('/^@env:([a-zA-Z_]+)$/', '$1', $name);
  }

  public static function getClassName($class)
  {
    $split = explode('\\', $class);

    return end($split);
  }

  public static function getBlock($class)
  {
    $split = explode('\\', $class);
    $count = count($split);

    return $split[$count-2];
  }

  public static function getModule(string $dispatcher): string
  {
    preg_match('/Modules\\\([a-zA-Z]+)/', $dispatcher, $info);
    return $info[1] ?? NULL;
  }

  public static function removeTrailingSlash($str)
  {
    return preg_replace('/^(.+)\/$/', '$1', $str);
  }

  public static function getCapitalWord($str)
  {
    $str = preg_replace_callback('/[\-_]([a-z])/', function ($match) {
      return strtoupper($match[1]);
    }, $str);

    return $str;
  }

  public static function getDashedWord($str)
  {
    $str = preg_replace_callback('/([A-Z])/', function ($match) {
      return '-' . strtolower($match[1]);
    }, $str);

    return $str;
  }

  public static function setDispatcher(array $config)
  {
    return implode('::', $config);
  }

  public static function getDispatcher($class, $method = 'index', $delimiter = '::')
  {
    if (preg_match('/' . $delimiter .'/', $class)) {
      list($class, $method) = explode($delimiter, $class);
    }

    $method = self::getCapitalWord($method);

    return [
      $class,
      $method,
    ];
  }

  public static function getVar($index, array $data, $returnArray = FALSE, $tpl = FALSE)
  {
    $cache = [];
    $error = (App::errors() && !$returnArray) 
      ? "'{$index}' variable is not a string." 
      : '';

    if (preg_match('/\./', $index)) {
      $values = explode('.', $index);

      foreach ($values as $key => $value)
      {
        if (empty($value)) {
          $error = (App::errors()) 
          ? "'{$index}' is malformed." 
          : '';

          return (App::errors() && !$returnArray) ? $error : [];
        }

        if ($key == 0) {
          if (isset($data[$value])) {
            $cache = $data[$value];
          }
        }
        else {
          if (isset($cache[$value])) {
            $cache = $cache[$value];
          }
          else {
            $error = (App::errors()) 
            ? "'{$index}' variable is not set."
            : '';

            if (!empty($error) && $tpl) {
              return '{{ ' . $index . ' }}';
            }

          }
        }
      }
    }
    else {
      if (isset($data[$index])) {
        $cache = $data[$index];
      }
      else {
        $error = (App::errors()) 
        ? "'{$index}' variable is not set." 
        : '';

        if (!empty($error) && $tpl) {
          return '{{ ' . $index . ' }}';
        }
      }
    }

    $output = ($returnArray)
      ? empty($error)
      : !is_array($cache);

    return $output ? $cache : $error;
  }

  public static function sum(array $values, $index) {
    $total = 0;

    foreach ($values as $value) {
      $total += $value[$index];
    }

    return $total;
  }

  public static function addZero($n) {
    return strlen($n) < 2 ? "0{$n}" : $n;
  }

  public static function getValue($value)
  {
    $value = trim($value);

    if (preg_match('/^\d+$/', $value)) {
      $value = intval($value);
    }
    else if (strtolower($value) == 'null') {
      $value = NULL;
    }

    return $value;
  }

}
