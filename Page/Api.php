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

use Firebase\JWT\JWT;
use Roducks\Services\Db;
use Roducks\Lib\Request\Http;
use Firebase\JWT\ExpiredException;

abstract class Api extends Json {

	const JWT_SECRET_KEY = "R0duck5";
	const JWT_EXPIRATION = 3600;
	const JWT_ISS = "http://example.org";
	const JWT_AUD = "http://example.com";

	private $_jwt = [];

  /**
   * 
   */
  public function __construct(array $settings, Db $db)
  {
		parent::__construct($settings, $db);

    if (isset($settings['jwt'])) {
			$this->_verifyToken();
		}
  }

	private function _verifyToken()
	{
		$this->_jwt['encoded'] = preg_replace('/^Bearer\s(.+)$/', '$1', Http::getAuthorizationHeader());

		try {
			$this->_jwt['decoded'] = JWT::decode($this->_jwt['encoded'], static::JWT_SECRET_KEY, ['HS256']);
		}
		catch (ExpiredException $e) {
      $this->data('error', TRUE);
      $this->data('message', $e->getMessage());
      $this->output(401);
		}
		catch (\Exception $e) {
      $this->data('error', TRUE);
      $this->data('message', $e->getMessage());
      $this->output(401);
		}

	}

	protected function setToken(array $data = [], $timeout = 3600, $leeway = 720000)
	{
		$time = time();
		$token = [
			"iss" => static::JWT_ISS,
			"aud" => static::JWT_AUD,
			"iat" => $time,
			"exp" => $time + $timeout,
			"nbf" => $time,
			"data" => [],
		];

		if (count($data) > 0) {
			$token['data'] = $data;
		}

		JWT::$leeway = $leeway; // $leeway in seconds

		return JWT::encode($token, static::JWT_SECRET_KEY);
	}

	protected function getToken()
	{
		$this->_verifyToken();

		return $this->_jwt['decoded'];
	}

}
