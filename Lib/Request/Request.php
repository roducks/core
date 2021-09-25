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

namespace Roducks\Lib\Request;

use Roducks\Lib\Files\File;
use Roducks\Lib\Utils\Utils;

class Request {
  protected $params = [];

  public static function init(array $params)
  {
    return new static($params);
  }

  public function __construct(array $params)
  {
    $this->params = $params;
  }

  public function get($index, $value = NULL)
  {
    return $this->params[$index] ?? $value;
  }

  public function getValues()
  {
    return $this->params;
  }

  public static function getBody()
  {
    $input = File::getContent('php://input', TRUE);
    $body = (is_array($input)) ? $input[0] : Utils::serialize($input);

    return $body;
  }

  public static function getPost()
  {
    return array_map(function ($v) {
      return trim($v);
    }, $_POST);
  }

  public function http($type, $url)
  {
    return HttpRequest::init($type, $url);
  }

}
