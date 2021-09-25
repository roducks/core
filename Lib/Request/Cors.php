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

class Cors {
  protected $headers = [];

  public static function init()
  {
    return new static();
  }

  public function setHeader($name, $value)
  {
    $this->headers[$name] = $value;
  }

	public function unsetHeader($name)
	{
		unset($this->headers[$name]);
	}

  public function reset()
  {
    $this->headers = [];
  }

  public function apply()
  {
    foreach ($this->headers as $key => $value) {
      Http::setHeader($key, $value);
    }
  }

	public function accessControl($key, $value)
	{
		$this->setHeader("Access-Control-{$key}:", $value);
	}

	/**
   * @example GET, POST, OPTIONS, PUT, DELETE
   */
	public function allowedMethods(array $values = [])
	{
		if (!empty($values)) {
			$this->accessControl("Allow-Methods", implode(', ', $values));
		}
	}

	public function allowDomains($domains = '*')
	{
		$domains = (is_array($domains)) ? implode(' ', $domains) : $domains;
		$this->accessControl("Allow-Origin", $domains);
	}

	public function allowedHeaders(array $values)
	{
		$headers = (is_array($values)) ? implode(', ', $values) : $values;
		$this->accessControl("Allow-Headers", $headers);
	}

	public function exposeHeaders(array $values)
	{
		$headers = (is_array($values)) ? implode(' ', $values) : $values;
		$this->accessControl("Expose-Headers", $headers);
	}

	public function credentails()
	{
		$this->accessControl("Allow-Credentials", "true");
	}

  public function maxAge($value = 1728000)
	{
		$this->accessControl("Max-Age", $value);
	}

}
