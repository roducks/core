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

namespace Roducks\Modules\App\Blocks\Languages;

use Roducks\Page\View;
use Roducks\Page\Block;
use Roducks\Services\Db;
use Roducks\Lib\Utils\Date;
use Roducks\Services\Language;
use Roducks\Di\ContainerInterface;

class Menu extends Block {

  /**
   * @var \Roducks\Services\Language $lang
   */
  protected $lang;

  public function __construct(array $settings, Db $db, View $pageView, Language $lang)
  {
    parent::__construct($settings, $db, $pageView);
    $this->lang = $lang;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('view'),
      $container->get('lang')
    );
  }

  public function index()
  {
    $this->view->data('time', Date::getTime());
    $this->view->data('items', $this->lang->getCatalog());
    $this->view->load('menu');

    return $this->view->output();
  }
}
