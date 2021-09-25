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

namespace Roducks\Lib\Utils;

use Roducks\Lib\Output\Csv;

abstract class Utils {
  public static function isHttp($url)
  {
    return preg_match('/^https?/', $url);
  }

  public static function serialize(string $query): array
  {
    $data = [];
    $params = [$query];

    if (preg_match('/[&]+/', $query)) {
      $params = explode('&', $query);
    }

    foreach ($params as $param) {
      if (preg_match('/[=]/', $param)) {
        $pair = explode('=', $param);
        $key = $pair[0];
        $value = $pair[1];
        $data[$key] = urldecode(trim($value)); 
      }
    }

    return $data;
  }

  public static function getCsvData($file, callable $callback)
  {
    $csv = Csv::init($file);
    $read = $csv->read();

    if ($read) {
      $csv->getData($callback);
    }

    return $read;
  }
}
