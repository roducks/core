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
use Roducks\Framework\App;
use Roducks\Di\ContainerInterface;
use Roducks\Lib\Utils\Utils;

class Link extends Service {
  /**
   * @var \Roducks\Services\Url $url
   */
  protected $url;

  public function __construct(array $settings, $db, Url $url)
  {
    parent::__construct($settings, $db);
    $this->url = $url;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('url')
    );
  }

  public function get($index, array $params = [])
  {
    $links = App::getLinks();
    $uri = $links[$index] ?? NULL;

    $uri = preg_replace_callback('/\{([a-z]+):[a-z]+\}/', function($match) use ($params) {
      $key = $match[1];
      return $params[$key] ?? '#';
    }, $uri);

    return !empty($uri) ? $uri : "#{$index}";
  }

  public function set($uri, array $params = [])
  {
    $queryString = !empty($params) ? Utils::serialize($params) : '';
    return $this->url->getAbsolute(FALSE) . $uri . $queryString;
  }
}
