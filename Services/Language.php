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
use Roducks\Services\Cookie;
use Roducks\Di\ContainerInterface;

class Language extends Service {
  const COOKIE_NAME = 'RDKS_LANG';
  const DEFAULT_LANG = 'en';

  /**
   * @var \Roducks\Services\Cookie $cookie
   */
  protected $cookie;

  public function __construct(array $settings, $db, Cookie $cookie)
  {
    parent::__construct($settings, $db);
    $this->cookie = $cookie;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('cookie')
    );
  }

  public function set($iso)
  {
    $this->cookie->set(self::COOKIE_NAME, $iso);
  }

  public function get()
  {
    return $this->cookie->get(self::COOKIE_NAME) ?? self::DEFAULT_LANG;
  }

  public function getCatalog()
  {
    $languages = [
      'en' => [
        'name' => 'English',
        'icon' => 'us.png',
      ],
      'es' => [
        'name' => 'Español',
        'icon' => 'mx.png',
      ],
    ];

    $this->observer('languages.init', [&$languages]);

    return $languages;
  }

}
