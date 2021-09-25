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

namespace Roducks\Modules\App\Page;

use Roducks\Page\Page;
use Roducks\Page\View;
use Roducks\Services\Url;
use Roducks\Services\Language as Lang;
use Roducks\Lib\Request\Request;
use Roducks\Di\ContainerInterface;

class Language extends Page {
  /**
   * @var \Roducks\Services\Url $url
   */
  protected $url;

  /**
   * @var \Roducks\Services\Language $lang
   */
  protected $lang;

  public function __construct(array $settings, $db, View $view, Url $url, Lang $lang)
  {
    parent::__construct($settings, $db, $view);
    $this->url = $url;
    $this->lang = $lang;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('view'),
      $container->get('url'),
      $container->get('lang')
    );
  }

  public function change(Request $request, $iso)
  {
    $uri = str_replace("/_lang/{$iso}", '', $this->url->getAbsolute());
    $this->lang->set($iso);
    $this->observer('language', [$iso]);
    $this->redirect($uri);
  }
}
