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

namespace Roducks\Framework;

abstract class App {
  const VERSION = '2.0';
  const DEFAULT_LANG = 'en';

  public static function errors()
  {
    return Autoload::$errors;
  }

  public static function getCmd()
  {
    return Autoload::$cmd;
  }

  public static function getModule()
  {
    return Autoload::$module;
  }

  public static function getSite()
  {
    return Autoload::$site;
  }

  public static function getTheme()
  {
    return Autoload::$theme;
  }

  public static function getLinks()
  {
    return Autoload::$links;
  }

  public static function getEnv()
  {
    return Autoload::$env;
  }

  public static function inEnv($name)
  {
    return self::getEnv() == $name;
  }

  public static function inLocal()
  {
    return self::inEnv('@local');
  }

  public static function inDev()
  {
    return self::inEnv('@dev');
  }

  public static function inUat()
  {
    return self::inEnv('@uat');
  }

  public static function inProd()
  {
    return self::inEnv('@prod');
  }
}
