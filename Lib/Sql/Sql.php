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

abstract class Sql {
  const DATA_ASSOC = 'assoc';
  const DATA_OBJECT = 'object';

  private $_mysqli = NULL;
  private $_transaction = NULL;
  private $_queryString = '';
  private $_start = 0;
  private $_totalPages = 0;
  private $_where = TRUE;
  private $_result = NULL;
  private $_select = FALSE;
  private $_paramTypes = [
    'int' => 'i',
    'float' => 'd',
    'str' => 's',
    'blob' => 'b', 
  ];
  private $_bindParams = [];
  private $_values = [];
  private $_conditions = [];

  protected $_statment = [];
  protected $_table = NULL;
  protected $fields = [];

  protected function _getField($column, $value)
  {
    $field = $column;
    $type = 'str';

    if (preg_match('/:/', $column)) {
      list($field, $type) = explode(':', $column);
    }

    $args = [
      'field' => $field,
      'type' => $this->_paramTypes[$type],
    ];

    $this->_bindParams[] = $args['type'];
    $this->_values[] = $value;

    return $args['field'];
  }

  private function _operator($name, $column)
  {
    $name = strtoupper($name);
    return $this->select([self::field("{$name}({$column})", 'total')]);
  }

  protected function _reset()
  {
    // $this->_statment = [];
    $this->_values = [];
    $this->_bindParams = [];
    $this->_where = TRUE;
    $this->_start = 0;
    $this->_conditions = [];
  }

  public function __construct(mysqli $mysqli)
  {
    $this->_mysqli = $mysqli;

    return $this;
  }

  public static function init(mysqli $mysqli)
  {
    return new static($mysqli);
  }

  public static function field($column, $alias = NULL)
  {
    $as = (!empty($alias)) ? " AS {$alias}" : '';
    return "{$column}{$as}";
  }

  public static function contact(array $values, $alias, $sep = ' ')
  {
    $concat = implode(',', $values);
    return self::field("CONCAT({$concat})", $alias);
  }

  public function db()
  {
    $this->_mysqli;
  }

  public function raw($statment)
  {
    $this->_queryString = $statment;
    $this->_transaction = $this->_mysqli->query($this->_queryString);
    return $this->_transaction;
  }

  private function _prepare($statment)
	{
		$this->_queryString = $statment;
    $this->_transaction = $this->_mysqli->prepare($this->_queryString);
    $bindParams = implode('', $this->_bindParams);
    $params = [];

    if (!empty($this->_values)) {
      array_unshift($this->_values, $bindParams);

      foreach ($this->_values as $i => $value) {
        $params[] = &$this->_values[$i];
      }

      call_user_func_array([$this->_transaction, 'bind_param'], $params);
    }

    $this->_transaction->execute();

    if ($this->_select) {
      $this->_result = $this->_transaction->get_result();
    }

		return $this->_transaction;
	}

  public function fetch($type = self::DATA_ASSOC)
  {
    if (!$this->hasRecords()) {
      return [];
    }

    $method = 'fetch_assoc';

    if ($type == self::DATA_OBJECT) {
      $method = 'fetch_object';
    }

    return $this->_result->$method();
  }

  public function getString()
  {
    return $this->_queryString;
  }

	public function getTransaction()
	{
		return $this->_transaction;
	}

  public function close()
  {
    $this->getTransaction()->close();
  }

	public function getAffectedRecords()
	{
		return $this->getTransaction()->affected_rows;
	}

  public function hasRecords()
  {
    return $this->getAffectedRecords() > 0;
  }

  public function getId()
	{
		return $this->_mysqli->insert_id;
	}

  public function autocommit($option = false)
	{
		$this->_mysqli->autocommit($option);
	}

	public function commit()
	{
		$this->_mysqli->commit();
	}

	public function rollback()
	{
		$this->_mysqli->rollback();
	}

  // After calling a store procedure it's good to call free()
	public function free()
	{
		$this->getTransaction()->free();
	}

  public function select(array $fields = [])
  {
    $this->_select = TRUE;
    $this->fields = $fields;
    $fields = (!empty($fields)) ? implode(',', $fields) : '*';
    $this->_statment['query'] = "SELECT {$fields}";

    return $this;
  }

  private static function _getRelation(array $relation)
  {
    $key = array_keys($relation)[0];
    $value = array_values($relation)[0];

    return "ON {$key} = {$value}";
  }

  private function _getTable()
  {
    if (is_array($this->_table)) {
      $join = '';
      $i = 1;

      foreach ($this->_table as $value) {
        $table = self::field($value['table'], $value['alias']);
        $relation = !empty($value['relation']) ? self::_getRelation($value['relation']) : '';
        $inner = (isset($this->_table[$i]['type'])) ? " {$this->_table[$i]['type']} " : '';
        $space = ($i > 1) ? ' ' : '';
        $join .= "{$table}{$space}{$relation}{$inner}";
        $i++;
      }

      return $join;
    }
    else {
      return $this->_table;
    }
  }

  private function _link()
  {
    $table = $this->_getTable();
    $this->_statment['from'] = " FROM {$table}";
  }

  public function from($table)
  {
    $this->_table = $table;
    $this->_link();

    return $this;
  }

  public function where($column, $operator, $value)
  {
    $this->_conditions[] = [$column, $operator, $value];

    if ($this->_where) {
      $this->_statment['conditions'] = '';
    }

    $cond = $this->_where ? 'WHERE' : 'AND';
    $this->_where = FALSE;
    $field = $this->_getField($column, $value);

    $this->_statment['conditions'] .= " {$cond} {$field} {$operator} ?";
    return $this;
  }

  public function and($column, $operator, $value)
  {
    $field = $this->_getField($column, $value);
    $this->_statment['conditions'] .= " AND {$field} {$operator} ?";
    return $this;
  }

  public function or($column, $operator, $value)
  {
    $field = $this->_getField($column, $value);
    $this->_statment['conditions'] .= " OR {$field} {$operator} ?";
    return $this;
  }

  public function conditions(array $conditions)
  {
    $this->_conditions[] = $conditions;
    $this->_reset();

    foreach ($conditions as $condition) {
      $this->where($condition[0], $condition[1], $condition[2]);
    }

    return $this;
  }

  public function having($column, $operator, $value)
  {
    $field = $this->_getField($column, $value);
    $this->_statment['having'] = " HAVING {$field} {$operator} ?";
    return $this;
  }

  public function groupBy($column)
  {
    $this->_statment['group_by'] = " GROUP BY {$column}";
    return $this;
  }

  public function orderBy($column, $sort = 'asc')
  {
    if (in_array($sort, ['asc', 'desc'])) {
      $sort = strtoupper($sort);
      $this->_statment['order_by'] = " ORDER BY {$column} {$sort}";
    }

    return $this;
  }

  public function limit($end = 1)
  {
    $this->_statment['limit'] = " LIMIT {$this->_start},{$end}";
    return $this;
  }

  public function getTotalPages()
  {
    return $this->_totalPages;
  }

  public function paginate($page = 1, $limit = 15)
  {
    $sql = Query::init($this->_mysqli);
    $sql->count('*');
    $sql->from($this->table);
    $sql->conditions($this->_conditions);
    $sql->execute();

    $result = $sql->fetch();
    $totalRows = intval($result['total']);

    // make sure total of rows is greater than limit
    if ($totalRows > $limit) {
      $this->_totalPages = ceil($totalRows / $limit);
      // if page is greater than total of pages reset to 1
      if ($page > $this->_totalPages) $page = 1;

      $this->_start = ceil($limit * $page) - $limit;
    }

    $this->limit($limit);

    return $this;
  }

  public static function getPageFromOffset($offset, $limit)
	{
		$perPage = intval($limit);
		$offset = intval($offset);
		$page = 1;

		if ($offset > 0 && $offset >= $perPage) {
			$page = ($offset / $perPage) + 1;
		}

		return $page;
	}

	public function offset($offset, $limit = 15)
	{
		return $this->paginate(self::getPageFromOffset($offset, $limit), $limit);
	}

  public function count($column)
  {
    return $this->_operator(__FUNCTION__, $column);
  }

  public function sum($column)
  {
    return $this->_operator(__FUNCTION__, $column);
  }

  public function max($column)
  {
    return $this->_operator(__FUNCTION__, $column);
  }

  public function min($column)
  {
    return $this->_operator(__FUNCTION__, $column);
  }

  public function avg($column)
  {
    return $this->_operator(__FUNCTION__, $column);
  }

  public function distinct($column)
  {
    return $this->_operator(__FUNCTION__, $column);
  }

  public function execute()
  {
    if (empty($this->_statment)) {
      return NULL;
    }

    if (is_array($this->_table)) {
      $this->_link();
    }

    $statment = $this->_statment['query'];

    if (isset($this->_statment['from'])) {
      $statment .= $this->_statment['from'];
    }

    if (isset($this->_statment['conditions'])) {
      $statment .= $this->_statment['conditions'];
    }

    if (isset($this->_statment['having'])) {
      $statment .= $this->_statment['having'];
    }

    if (isset($this->_statment['group_by'])) {
      $statment .= $this->_statment['group_by'];
    }

    if (isset($this->_statment['order_by'])) {
      $statment .= $this->_statment['order_by'];
    }

    if (isset($this->_statment['limit'])) {
      $statment .= $this->_statment['limit'];
    }

    $this->_statment = [];

    return $this->_prepare($statment);
  }
}
