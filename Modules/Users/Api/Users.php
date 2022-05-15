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

namespace Roducks\Modules\Users\Api;

use Roducks\Page\Api;
use Roducks\Services\Db;
use Roducks\Modules\Users\Services\User;
use Roducks\Lib\Request\Request;
use Roducks\Di\ContainerInterface;

class Users extends Api {

  /**
   * @var \Roducks\Modules\Users\Services\User $user
   */
  protected $user;

  /**
   * 
   */
  public function __construct(array $settings, Db $db, User $user)
  {
		parent::__construct($settings, $db);
    $this->user = $user;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('user')
    );
  }

  public function me(Request $request)
  {
    $this->data($this->getToken()->data);

    return $this->output();
  }

  /**
   * @type GET
   */
  public function catalog(Request $request)
  {
    $this->data('name', __METHOD__);
    $this->data('duke', $request->getCustomHeader('duke'));

    return $this->output();
  }

  /**
   * @type GET
   */
  public function get(Request $request, $id)
  {
    return $this->me($request);
  }

  /**
   * @type PUT
   */
  public function update(Request $request)
  {
    $this->data('name', __METHOD__);

    return $this->output();
  }

  /**
   * @type POST
   */
  public function store(Request $request)
  {
    $res = $this->user->create($request->getValues());
    $this->data('message', 'User was created.');

    return $this->output();
  }

}
