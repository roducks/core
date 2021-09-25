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

namespace Roducks\Lib\Sql;

use mysqli;

abstract class Join extends ORM {

  public function __construct(mysqli $mysqli)
  {
    parent::__construct($mysqli);
    $this->union();

    return $this;
  }

  /**
   *  $this
   *   ->table('users', 'u')
   *   ->join('roles', 'r', ['u.id_role' => 'r.id'])
   *   ->join('roles_types', 'rt', ['r.id_role_type' => 'rt.id']);
   */
  protected function union() {}

  private function _union($alias, $name, $type, $relation)
  {
    $this->_table[] = [
      'alias' => $alias,
      'table' => $name,
      'type' => $type,
      'relation' => $relation,
    ];

    return $this;
  }

  protected function table($name, $alias)
  {
    return $this->_union($alias, $name, '', NULL);
  }

  protected function join($name, $alias, array $relation)
  {
    return $this->_union($alias, $name, 'INNER JOIN', $relation);
  }

  protected function leftJoin($name, $alias, array $relation)
  {
    return $this->_union($alias, $name, 'LEFT JOIN', $relation);
  }

  protected function rightJoin($name, $alias, array $relation)
  {
    return $this->_union($alias, $name, 'RIGHT JOIN', $relation);
  }

}
