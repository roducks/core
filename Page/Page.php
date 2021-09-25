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

namespace Roducks\Page;

use Roducks\Services\Db;
use Roducks\Lib\Request\Http;
use Roducks\Di\ContainerInterface;

abstract class Page extends Frame {

  /**
   * @var \Roducks\Page\View $view
   */
  protected $view;

  /**
   * 
   */
  public function __construct(array $settings, Db $db, View $view)
  {
    parent::__construct($settings, $db);
    $this->view = $view;
    $this->view->setDispatch($settings['dispatch']);
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('view')
    );
  }

  protected function redirect($uri)
  {
    Http::redirect($uri);
  }

  public function debugger()
  {
    $this->view->data('messages', $this->settings['messages']);
    $this->view->load();

    return $this->view->output();
  }

  public function notFound()
  {
    $this->view->load();

    return $this->view->output();
  }
}
