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

namespace Roducks\Di;

class Container implements ContainerInterface {
  private $_items = [];
  private $_lock = [];
  private $_name = NULL;

  public static function init()
  {
    return new static();
  }

  public function set($name, $value)
  {
    $this->_name = $name;

    if (!isset($this->_lock[$this->_name])) {
      $this->_items[$name] = $value;
    }

    return $this;
  }

  public function lock()
  {
    $this->_lock[$this->_name] = 1;
  }

  public function unlock()
  {
    // do nothing
  }

  public function get($name)
  {
    return $this->_items[$name] ?? NULL;
  }

  public function getAll()
  {
    return $this->_items;
  }
}
