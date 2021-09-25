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
use Roducks\Di\ContainerInterface;

class Cookie extends Service {
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

	// this cookie will be visible in any subdomain
	public function set($name, $value)
	{
		//$expire = time()+60*60*24*30; // a month
		$expire = time() + (10 * 365 * 24 * 60 * 60); // a year
		setcookie($name, $value, $expire, '/', $this->url->getHost());
	}

	public function delete($name)
	{
		$this->set($name, NULL);
	}

	public function get($name)
	{
		return $_COOKIE[$name] ?? NULL;
	}

	public function exists($name)
	{
		return !empty($this->get($name));
	}

}
