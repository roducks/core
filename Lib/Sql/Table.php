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

class Table extends Sql {
  private $_columns = [];
  private $_engine = NULL;
  private $_charset = NULL;
  private $_i = 0;

  private function _resetTbl()
  {
    $this->_columns = [];
    $this->_i = 0;
  }

  public function create($table, $engine = 'InnoDB', $charset = 'utf8')
  {
    $this->_table = $table;
    $this->_engine = $engine;
    $this->_charset = $charset;
    $this->_statment['table'] = "CREATE TABLE IF NOT EXISTS `{$table}`";
    return $this;
  }

  public function int($column, $size = 8)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` int({$size})";

    return $this;
  }

  public function bigint($column, $size = 8)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` bigint({$size})";

    return $this;
  }

  public function tinyint($column, $size = 1)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` tinyint({$size})";

    return $this;
  }

  public function float($column, $size = 16, $decimal = 2)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` float({$size},{$decimal})";

    return $this;
  }

  public function varchar($column, $size = 255)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` varchar({$size})";

    return $this;
  }

  public function text($column)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` text";

    return $this;
  }

  public function blob($column)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` blob";

    return $this;
  }

  public function date($column)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` date";

    return $this;
  }

  public function datetime($column)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "`{$column}` datetime";

    return $this;
  }

  public function enum($column, array $values)
  {
    $this->_i++;
    $values = array_map(function($value) {
      return "'{$value}'";
    }, $values);
    $list = implode(',', $values);
    $this->_columns[$this->_i] = "`{$column}` enum({$list})";

    return $this;
  }

  public function default($value)
  {
    $value = ($value == 'NULL') ? $value : "'{$value}'";
    $this->_columns[$this->_i] .= " DEFAULT {$value}";

    return $this;
  }

  public function unsigned()
  {
    $this->_columns[$this->_i] .= ' UNSIGNED';
    return $this;
  }

  public function autoIncrement()
  {
    $this->_columns[$this->_i] .= ' AUTO_INCREMENT';
    return $this;
  }

  public function null()
  {
    $this->_columns[$this->_i] .= ' NULL';
    return $this;
  }

  public function notNull()
  {
    $this->_columns[$this->_i] .= ' NOT NULL';
    return $this;
  }

  public function timestamps()
  {
    $this
      ->datetime('created_at')->notNull()
      ->datetime('updated_at')->null()
      ->datetime('deleted_at')->null();

    return $this;
  }

  public function index($column)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "INDEX `idx_{$this->_table}_{$column}` (`{$column}`)";
    return $this;
  }

  public function uniqueIndex($column)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "UNIQUE INDEX `idx_{$this->_table}_{$column}` (`{$column}`)";
    return $this;
  }

  public function pk($column = 'id')
  {
    $this->_i++;
    $this->_columns[$this->_i] = "PRIMARY KEY (`{$column}`)";
    return $this;
  }

  public function fk($id, $fTable, $fId)
  {
    $this->_i++;
    $this->_columns[$this->_i] = "CONSTRAINT `fk_{$fTable}_{$id}` FOREIGN KEY (`{$id}`) REFERENCES `{$fTable}` (`{$fId}`) ON DELETE CASCADE ON UPDATE CASCADE";
    return $this;
  }

  public function id($column = 'id')
  {
    $this->bigint($column)->unsigned()->autoIncrement()->notNull();

    return $this;
  }

  public function getOutput()
  {
    $columns = implode(",\n", $this->_columns);
    return $this->_statment['table'] . " (\n" . $columns . "\n) ENGINE={$this->_engine} DEFAULT CHARSET={$this->_charset}\n";
  }

  public function execute()
  {
    $this->raw($this->getOutput());
    $this->_resetTbl();
  }

  public function drop($table)
  {
    $this->raw("DROP TABLE IF EXISTS `{$table}`");
  }

  public function forceDrop($value = 0)
  {
    $this->raw("SET FOREIGN_KEY_CHECKS = {$value}");
  }

  public function forceUpdate($value = 0)
  {
    $this->raw("SET SQL_SAFE_UPDATES = {$value}");
  }
}
