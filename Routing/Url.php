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

use Roducks\Lib\Request\Http;
use Roducks\Lib\Utils\Utils;

abstract class Url {
  const REGEXEP_QUERY_STRING = '(\?[a-zA-Z0-9&=\-_+%]+)?$#';

  private static function _getData($index)
  {
    $uri = self::get();
    $query = [];

    if (preg_match('/\?/', $uri)) {
      $split = explode('?', $uri);
      $uri = $split[0];
      $query = Utils::unserialize($split[1]);
    }

    $response = [
      'uri' => $uri,
      'query' => $query,
    ];

    return $response[$index] ?? NULL;
  }

  public static function get()
  {
    return Http::getUri();
  }

  public static function getAbsolute($uri = TRUE)
  {
    $end = ($uri) ? self::get() : '';
    return Http::getScheme() . '://' . Http::getServerName() . $end;
  }

  public static function getUri()
  {
    return self::_getData('uri');
  }

  public static function getQuery()
  {
    return self::_getData('query');
  }

}
