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

namespace Roducks\Page;

use Roducks\Lib\Request\Http;
use Roducks\Traits\DataTrait;

abstract class Json extends Frame {

  use DataTrait;

  public static function encode($data)
  {
    return json_encode($data);
  }

  public static function decode($data)
  {
    return json_decode($data, TRUE);
  }

  protected function setError($message, $code)
  {
    $this->data('error', TRUE);
    $this->data('message', $message);

    return $this->output($code);
  }

  protected function _output($code)
  {
    Http::setJsonHeader();

    if ($code !== 200) {
      switch ($code) {
        case 401:
          Http::sendHeaderAuthenticationFailed(FALSE);
          break;
      }
    }

    echo self::encode($this->getData());
  }

  protected function output($code = 200)
  {
    $this->_output($code);
    exit;
  }
}
