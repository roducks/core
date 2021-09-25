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

use Roducks\Framework\Helper;

use mysqli;

abstract class Model extends ORM implements ModelInterface {

  private $_data = [];
  private $_values = [];
  private $_action = NULL;
  private $_orm = FALSE;
  private $_id = 0;

  protected $query = NULL;

  public function __construct(mysqli $mysqli)
  {
    parent::__construct($mysqli);
    $this->query = Query::init($mysqli);

    return $this;
  }

  public static function init(mysqli $mysqli)
  {
    return new static($mysqli);
  }

  public function __call($name, $args)
  {
    $data = NULL;
    $i = 0;

    // get || set match exactly.
    if (strlen($name) == 3) {
      $action = $name;
      $field = $args[$i] ?? $data;
      $i++;
    }
    else {
      $action = substr($name, 0, 3);
      $letter = strtolower(substr($name, 3, 1));
      $field = substr($name, 4);
      $field = preg_replace_callback('/([A-Z]+)/', function($match) {
        $letter = strtolower($match[1]);
        return "_{$letter}";
      }, $field);
      $field = $letter . $field;
    }

    switch ($action) {
      case 'get':
          $data = $this->_data[$field] ?? NULL;
        break;
      case 'set':
          $value = $args[$i] ?? NULL;
          $key = ($this->_action == 'insert') ? '_data' : '_values';
          $this->$key[$field] = $value;
        break;
    }

    return $data;
  }

  private function _prepare()
  {
    $this->_orm = TRUE;

    return $this;
  }

  public function prepare()
  {
    $this->_action = 'insert';

    return $this->_prepare();
  }

  public function load($id)
  {
    $this
      ->select()
      ->from($this->table)
      ->where("{$this->id}:int", '=', $id)
      ->execute();

    $this->_data = $this->fetch();
    $this->_id = $id;
    $this->_action = 'update';

    return $this->_prepare();
  }

  public function save()
  {
    switch ($this->_action) {
      case 'insert':
        $this->insert();
        break;
      case 'update':
        $this->update();
        break;
    }
  }

  public function insert(array $values = [])
  {
    if ($this->_orm) {
      $values = $this->_data;
    }

    $this->query
      ->insert($values)
      ->into($this->table)
      ->execute();

    return $this;
  }

  public function update(int $id = 0, array $values = [])
  {
    if ($this->_orm) {
      $id = $this->_id;
      $values = $this->_values;
    }

    return $this->query
      ->update($this->table)
      ->set($values)
      ->where("{$this->id}:int", '=', $id)
      ->execute();
  }

  public function delete(int $id = 0)
  {
    if ($this->_orm) {
      $id = $this->_id;
    }

    return $this->query
      ->delete($this->table)
      ->where("{$this->id}:int", '=', $id)
      ->execute();
  }

  public function setValues(array $values)
  {
    foreach ($values as $key => $value) {
      $this->set($key, $value);
    }
  }

}
