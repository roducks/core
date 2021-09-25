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

namespace Roducks\Modules\Users\Cli;

use Roducks\Services\Db;
use Roducks\Modules\Users\Services\User as UserService;
use Roducks\Lib\Output\Cli;
use Roducks\Di\ContainerInterface;
use Roducks\Lib\Data\Hash;

class User extends Cli {

  /**
   * @var \Roducks\Services\User $user
   */
  protected $user;

  public function __construct(array $settings, Db $db, UserService $user)
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

  public function create()
  {
    $name = $this->setInput('User Name');
    $email = $this->setInput('Email');
    $firstName = $this->setInput('First Name');
    $lastName = $this->setInput('Last Name');
    $gender = $this->setOptions('Gender', [
      'male' => 'Male', 
      'female' => 'Female'
    ], TRUE);
    $password = $this->setPassword('Password');

    $data = [
      'id_role' => $this->getFlag('--admin') ? 1 : 2,
      'name' => $name,
      'email' => $email,
      'password' => $password,
      'first_name' => $firstName,
      'last_name' => $lastName,
      'gender' => $gender,
    ];

    $this->user->create($data);

    $this->success('Success', [
      'A user has been created for:',
      '',
      $email,
    ]);
  }

  public function changePassword()
  {
    $email = $this->setInput('Email');
    $data = $this->user->getByEmail($email);

    if (empty($data)) {
      $this->error([
        "Email: '{$email}' does not exist.",
      ]);
    }

    $password = $this->setPassword('Password');

    $id = intval($data['id']);
    $user = $this->db->model('users')->load($id);
    $hash = Hash::getSaltPassword($password);
    $user->setPassword($hash['password']);
    $user->setSalt($hash['salt']);
    $user->save();

    $this->success('Success', [
      'Password has been updated.',
    ]);
  }
}
