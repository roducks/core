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

use Roducks\Files\Cache;
use Roducks\Page\Service;
use Roducks\Framework\App;
use Roducks\Framework\Helper;
use Roducks\Services\Language;
use Roducks\Di\ContainerInterface;

class i18n extends Service {

  /**
   * @var \Roducks\Services\Language $lang
   */
  protected $lang;

  public function __construct(array $settings, $db, Language $lang)
  {
    parent::__construct($settings, $db);
    $this->lang = $lang;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('lang')
    );
  }

  public function get($index, array $placeholders = [])
  {
    $i18n = Cache::get(App::getSite(), $this->lang->get());
    
    if (empty($i18n)) {
      $i18n = Cache::get(App::getSite(), Language::DEFAULT_LANG);
    }

    $text = Helper::getVar($index, $i18n);
    $i = 0;

    $text = preg_replace_callback('/(\{\?\})/', function ($match) use (&$i, $placeholders) {
      $ret = $placeholders[$i] ?? $match[1];
      $i++;

      return $ret;
    }, $text);

    return $text;
  }

}
