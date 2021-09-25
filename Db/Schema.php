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
namespace Roducks\Db;

use Roducks\Page\Frame;
use Roducks\Routing\Path;
use Roducks\Lib\Sql\Table;
use Roducks\Lib\Utils\Utils;

abstract class Schema extends Frame {

  public function install(Table $table) {}

  public function unistall(Table $table) {}

  public function data() {}

  protected function getCsvData($name, callable $callback)
  {
    $file = Path::getDbDataFromClass($this, $name . Path::CSV_EXT);
  
    return Utils::getCsvData($file, $callback);
  }
}
