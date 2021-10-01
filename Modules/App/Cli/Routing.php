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

namespace Roducks\Modules\App\Cli;

use Roducks\Files\Cache;
use Roducks\Framework\App;
use Roducks\Lib\Output\Cli;

class Routing extends Cli {
  public function run($cmd)
  {
    $routing = Cache::getRouting(App::getSite());
    $data = [];
    $root = $routing['/'] ?? NULL;
    $length = [];
    $tail = [];

    if (!empty($root)) {
      $data[] = $root;
      $length[] = strlen($root['/']);
      $tail[] = strlen($root['dispatch']);
      unset($routing['/']);
    }

    foreach ($routing as $route) {
      $data[] = $route;
      $length[] = strlen($route['uri']);
      $tail[] = strlen($root['dispatch']);
    }

    $max = max($length) + 10;
    $space = str_repeat(' ', $max - strlen('URI'));

    $this->output([
      $this->colorYellow('TYPE     '),
      $this->colorYellow('HTTP     '),
      $this->colorYellow('URI' . $space),
      $this->colorYellow('DISPATCHER'),
    ]);
    $this->output([
      str_repeat('-', $max + max($tail) + 24),
    ]);

    foreach ($data as $item) {
      $space = str_repeat(' ', $max - strlen($item['uri']));
      $api = $item['api'] ?? NULL;
      $type = $api ? 'API ' : 'PAGE';
      $method = $item['type'] ?? 'GET';
      $http = $api ? ' * ' : $method;
      $color = $api ? 'colorLightBlue' : 'colorGreen';

      if ($this->getFlag('--api', NULL) && !$api) {
        continue;
      }

      if ($this->getFlag('--web', NULL) && $api) {
        continue;
      }

      $this->output([
        $this->$color("{$type}     "),
        $this->$color($http . str_repeat(' ', 9 - strlen($http))),
        $this->$color($item['uri'] . $space),
        $this->$color($item['dispatch']),
      ]);
    }
  }
}
