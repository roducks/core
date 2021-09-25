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

namespace Roducks\Modules\Users\Services;

use Roducks\Services\Db;
use Roducks\Page\Service;
use Roducks\Services\Eav;
use Roducks\Lib\Data\Hash;
use Roducks\Di\ContainerInterface;

class User extends Service {
  protected $_data = [];
  protected $_model = NULL;
  
  /**
   * @var \Roducks\Services\Eav $_eav
   */
  protected $_eav = NULL;

  public function __construct(array $settings, Db $db, Eav $eav)
  {
    parent::__construct($settings, $db);
    $this->_model = $this->db->model('users.join');
    $this->_eav = $eav;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('eav')
    );
  }

  public function load(int $id)
  {
    $this->_eav->setEntity('users', $id);
    $this->_data = $this->_model->getById($id);
    return $this;
  }

  public function data()
  {
    return $this->_data;
  }

  public function get($key)
  {
    return $this->_data[$key] ?? NULL;
  }

  public function getName()
  {
    return $this->get('name');
  }

  public function getFullName()
  {
    return $this->get('full_name');
  }

  public function getGender()
  {
    return $this->get('gender');
  }

  public function getPicture()
  {
    return $this->get('name');
  }

  public function getEmail()
  {
    return $this->get('email');
  }

  public function getRole()
  {
    return $this->get('role');
  }

  public function getRoleId()
  {
    return $this->get('role_id');
  }

  public function eav()
  {
    return $this->_eav;
  }

  public function create(array $values)
  {
    if (!isset($values['password'])) {
      return FALSE;
    }

    $data = Hash::getSaltPassword($values['password']);
    $data['id_role'] = $values['id_role'] ?? 2;
    $data['name'] = $values['name'] ?? NULL;
    $data['email'] = $values['email'] ?? NULL;
    $data['first_name'] = $values['first_name'] ?? NULL;
    $data['last_name'] = $values['last_name'] ?? NULL;
    $data['picture'] = $values['picture'] ?? NULL;
    $data['gender'] = $values['gender'] ?? NULL;
    $data['created_at'] = date('Y-m-d H:i:s');

    return $this->db->model('users')->insert($data);
  }

  public function getByEmail($email)
  {
    $user = $this->db->model('users');
    $data = $user->getByEmail($email);

    if ($user->rows()) {
      return $data;
    }
    
    return [];
  }

  public function auth($email, $password)
  {
    $user = $this->_model->getByEmail($email);
    $valid = FALSE;

    if ($this->_model->rows()) {
      $valid = Hash::validatePassword($password, $user);
    }

    if ($valid) {
      // Remove secrets for security.
      unset($user['password']);
      unset($user['salt']);
    }
    else {
      return [];
    }

    return $user;
  }

}
