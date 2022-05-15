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

use Roducks\Framework\Helper;
use Roducks\Lib\Files\File;
use Roducks\Lib\Utils\Utils;
use Roducks\Page\Json;

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
    $param = $this->params[$index] ?? $value;
    return preg_match('/^\d+$/', $param) ? intval($param) : $param;
  }

  public function getValues()
  {
    return array_map(function ($item) {
      return preg_match('/^\d+$/', $item) ? intval($item) : $item;
    }, $this->params);
  }

  public static function getBody()
  {
    $input = File::getContent('php://input', TRUE);
    $body = (is_array($input)) ? $input[0] : Utils::unserialize($input);

    return Http::getRequestHeader('Content-Type') == "application/json" ? Json::decode($input) : $body;
  }

  public static function getPost()
  {
    return array_map(function ($v) {
      return !is_array($v) ? trim($v) : $v;
    }, $_POST);
  }

  public function http($type, $url)
  {
    return HttpRequest::init($type, $url);
  }

  public function getCustomHeader($name)
	{
		return Http::getRequestCustomHeader($name);
	}

	public function getHeader($name)
	{
		return Http::getRequestHeader($name);
	}

  public function method()
  {
    return Http::getRequestMethod();
  }

  public function isGet()
  {
    return Http::getRequestMethod() == 'GET';
  }

  public function isPost()
  {
    return Http::getRequestMethod() == 'POST';
  }

}
