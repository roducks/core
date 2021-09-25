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

abstract class Http {
  private static function _get($name) {
    return $_SERVER[$name] ?? '';
  }

  public static function setHeader($key, $value)
  {
    header("{$key}: {$value}");
  }

  public static function setHttpHeader($code, $message, $die = FALSE)
  {
    self::setHeader("HTTP/1.1","{$code} {$message}");
		if ($die) die("<h1>{$message}</h1>");
  }

  public static function sendHeaderMovedPermanently($die = TRUE)
	{
		self::setHttpHeader(301, "Moved Permanently", $die);
	}

	public static function sendHeaderAuthenticationFailed($die = TRUE)
	{
		self::setHttpHeader(401, "Unauthorized", $die);
	}

	public static function sendHeaderForbidden($die = TRUE)
	{
		self::setHttpHeader(403, "Forbidden Request", $die);
	}

	public static function sendHeaderNotFound($die = TRUE)
	{
		self::setHttpHeader(404, "Not Found", $die);
	}

	public static function sendMethodNotAllowed($die = TRUE)
	{
		self::setHttpHeader(405, "Method Not Allowed", $die);
	}

	public static function setHeaderInvalidRequest($die = TRUE)
	{
		self::setHttpHeader(501, "Invalid Request", $die);
	}

  public static function setJsonHeader()
  {
    self::setHeader('Content-Type', 'application/json; charset=utf-8');
  }

  public static function redirect($uri)
  {
    self::setHeader('Location', $uri);
  }

  public static function getServerName()
  {
    return self::_get('SERVER_NAME');
  }

  public static function getUri()
  {
    return self::_get('REQUEST_URI');
  }

  public static function getScheme()
  {
    return self::_get('REQUEST_SCHEME');
  }

  public static function getRequestMethod()
  {
    return self::_get('REQUEST_METHOD');
  }

  public static function getPort()
	{
		return self::_get('SERVER_PORT');
	}

	public static function getIPClient()
	{
		return self::_get('REMOTE_ADDR');
	}

	public static function getOrigin()
	{
		return self::_get('HTTP_ORIGIN');
	}

	public static function getBrowserLanguage($defaultLanguage)
	{
		if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return $defaultLanguage;
		}

		return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	}

	public static function getRequestHeader($name)
	{
		$name = str_replace('-', '_', $name);

		return self::_get('HTTP_X_' . strtoupper($name));
	}

	public static function getAuthorizationHeader()
	{
		return self::_get('HTTP_AUTHORIZATION');
	}

	public static function getEnv($name)
	{
		return $_ENV[$name] ?? NULL;
	}
}
