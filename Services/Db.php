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

namespace Roducks\Services;

use Roducks\Di\Di;
use Roducks\Files\Cache;
use Roducks\Framework\App;
use Roducks\Lib\Sql\Query;
use Roducks\Lib\Sql\Table;
use Roducks\Lib\Output\Error;

class Db extends Di {
  protected $mysqli = NULL;

  public function open(array $conn)
  {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
      $mysqli = new \mysqli($conn[0], $conn[1], $conn[2], $conn[3], $conn[4]);

      if ($mysqli->connect_errno) {
        throw new \Exception("{$mysqli->connect_errno} :: {$mysqli->connect_error}", 1);
      }

      $mysqli->set_charset('utf8');

      return $mysqli;
    }
    catch (\mysqli_sql_exception $e) {
      throw new \Exception($e->getMessage(), 1);
    }
  }

  private function _connect(array $conn)
  {
    $this->mysqli = $this->open($conn);
  }

  /**
   * 
   */
  public function __construct(array $settings)
  {
    $creds = $settings['credentials'];
    
    try {
      $this->_connect([
        $creds['host'],
        $creds['user'],
        $creds['password'],
        $creds['db'],
        $creds['port'],
      ]);
    }
    catch (\Exception $e) {
      Error::debug([$e->getMessage()]);
    }

    # ** IMPORTANT ** For security measures credentials are removed.
    unset($settings['credentials']);
    $this->settings = $settings;
  }

  public function get()
  {
    return $this->mysqli;
  }

  public function query()
  {
    return Query::init($this->get());
  }

  public function table()
  {
    return Table::init($this->get());
  }

  public function model($name)
  {
    $models = Cache::getModels(App::getSite());
    $model = NULL;

    if (isset($models[$name]['dispatch'])) {
      $class = $models[$name]['dispatch'];
      $model = $class::init($this->get());
    }

    return $model;
  }
}
