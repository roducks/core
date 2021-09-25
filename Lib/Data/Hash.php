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

namespace Roducks\Lib\Data;

abstract class Hash {
	const ENCRYPT = "sha512";

	public static function get($str)
	{
		return hash(self::ENCRYPT, $str);
	}

  public static function validatePassword($pswd, array $db)
  {
    return self::get($pswd . $db['salt']) == $db['password'];
  }

	public static function getSaltPassword($pswd)
	{
   	$salt = self::get(uniqid(mt_rand(1, mt_getrandmax()), TRUE));
    $password = self::get($pswd . $salt);

		return ['salt' => $salt, 'password' => $password];
	}

	public static function getToken($word = 'r0duck5')
	{
		$password = self::getSaltPassword($word);
		return $password['salt'];
	}

}
