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

namespace Roducks\Services;

use Roducks\Page\Service;
use Roducks\Lib\Request\Http;
use Roducks\Routing\Url as UrlHelper;

class Url extends Service {

  public function getHost()
  {
    return Http::getServerName();
  }

  public function redirect($uri, $absolute = TRUE)
  {
    $host = $absolute ? self::getAbsolute(FALSE) : '';
    Http::redirect($host . $uri);
  }

  public function get()
  {
    return $this->getHost() . UrlHelper::get();
  }

  public static function getUri()
  {
    return UrlHelper::get();
  }

  public static function getAbsolute($uri = TRUE)
  {
    return UrlHelper::getAbsolute($uri);
  }

  public static function getQuery()
  {
    return UrlHelper::getQuery();
  }
}
