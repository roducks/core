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

namespace Roducks\Modules\Users\Db\Models;

use Roducks\Lib\Sql\Join;

class UsersRolesJoin extends Join {

  protected function union()
  {
    $this
      ->table('users', 'u')
      ->join('roles', 'r', ['u.id_role' => 'r.id'])
      ->join('roles_types', 'rt', ['r.id_role_type' => 'rt.id']);
  }

  public function getById(int $id)
  {
    $this
      ->select([
        self::field('u.name'),
        self::field('u.email'),
        self::field('u.first_name'),
        self::field('u.last_name'),
        self::contact(['u.first_name', "' '", 'u.last_name'], 'full_name'),
        self::field('u.picture'),
        self::field('u.gender'),
        self::field('u.password'),
        self::field('u.salt'),
        self::field('r.name', 'role'),
        self::field('r.id', 'role_id'),
      ])
      ->where('u.id:int', '=', $id)
      ->execute();

    return $this->fetch();
  }

  public function getByEmail($email)
  {
    $this
      ->select([
        self::field('u.name'),
        self::field('u.email'),
        self::field('u.first_name'),
        self::field('u.last_name'),
        self::contact(['u.first_name', "' '", 'u.last_name'], 'full_name'),
        self::field('u.picture'),
        self::field('u.gender'),
        self::field('u.password'),
        self::field('u.salt'),
        self::field('r.name', 'role'),
        self::field('r.id', 'role_id'),
      ])
      ->where('u.email:str', '=', $email)
      ->execute();

    return $this->fetch();
  }
}
