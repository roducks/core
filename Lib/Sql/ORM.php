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

abstract class ORM extends Sql {
  protected $table = NULL;
  protected $id = 'id';

  public function __construct(mysqli $mysqli)
  {
    parent::__construct($mysqli);
    $this->from($this->table);

    return $this;
  }

  public function filter(array $conditions)
  {
    return parent::conditions($conditions);
  }

  public function getAll(array $fields = [])
  {
    $this
      ->select($fields)
      ->execute();

    return $this;
  }

  private function _sort($n, $sort)
  {
    $this
      ->orderBy($this->id, $sort)
      ->limit($n)
      ->execute();

    return $this;
  }

  public function first($n = 1)
  {
    return $this->_sort($n, 'asc');
  }

  public function last($n = 1)
  {
    return $this->_sort($n, 'desc');
  }
  
  public function rows()
  {
    return $this->hasRecords();
  }

  public function hasResults(array $conditions)
  {
    $this
      ->select()
      ->conditions($conditions)
      ->execute();

    $this->fetch();

    return $this->rows();
  }

  public function getQuery()
  {
    return $this->getString();
  }

}
