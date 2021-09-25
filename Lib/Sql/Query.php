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

class Query extends Sql {

  private $_statmentAlt = '';

  public function insert(array $values)
  {
    $placeholders = [];
    $fields = [];

    foreach ($values as $column => $value) {
      $fields[] = $this->_getField($column, $value);
      $placeholders[] = '?';
    }

    $columns = implode(',', $fields);
    $bindParams = implode(',', $placeholders);
    $this->_statmentAlt = " ({$columns}) VALUES ({$bindParams})";

    return $this;
  }

  public function into($table)
  {
    $this->_table = $table;
    $this->_statment['query'] = "INSERT INTO {$this->_table}";
    $this->_statment['query'] .= $this->_statmentAlt;

    return $this;
  }

  public function update($table)
  {
    $this->_table = $table;
    $this->_reset();
    $this->_statment['query'] = "UPDATE {$this->_table}";

    return $this;
  }

  public function set(array $values)
  {
    $fields = [];

    foreach ($values as $column => $value) {
      $fields[] = $this->_getField($column, $value) . ' = ?';
    }

    $set = implode(',', $fields);
    $this->_statment['query'] .= " SET {$set}";

    return $this;
  }

  public function delete($table)
  {
    $this->_table = $table;
    $this->_reset();
    $this->_statment['query'] = "DELETE FROM {$this->_table}";

    return $this;
  }

}
