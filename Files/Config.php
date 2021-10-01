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

namespace Roducks\Files;

use Roducks\Routing\Path;
use Roducks\Lib\Files\File;
use Roducks\Framework\Autoload;
use Roducks\Framework\Helper;
use Roducks\Page\Json;
use Symfony\Component\Yaml\Yaml;

abstract class Config {
  public static function getContent($file, $ext = Path::YML_EXT)
  {
    $data = [];

    if (empty($file)) {
      return $data;
    }

    $paths = [
      "{$file}.local" . $ext,
      $file . $ext
    ];

    foreach ($paths as $path) {
      if (file_exists($path)) {
        switch ($ext) {
          case Path::YML_EXT:
            $data = Yaml::parseFile($path);
            break;
          case Path::JSON_EXT:
            $data = Json::decode(File::getContent($path));
            break;
        }

        break;
      }
    }

    return $data;
  }

  public static function getServices($path)
  {
    return self::getContent($path . 'services');
  }

  public static function getCoreServices()
  {
    return self::getContent(Path::getCoreModuleConfig(Autoload::DEFAULT_MODULE, 'services'));
  }

  public static function getModule($site, $name)
  {
    $file = 'module' . Path::YML_EXT;
    $path = Path::get([
      Path::getAppSiteModuleConfig($site, $name, $file),
      Path::getCommunityModuleConfig($name, $file),
      Path::getCoreModuleConfig($name, $file)
    ]);

    return self::getContent(File::removeExt($path));
  }

  public static function getSite($name)
  {
    return self::getContent(Path::getAppSiteConfig($name, 'config'));
  }

}
