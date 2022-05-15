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

namespace Roducks\Services;

use Roducks\Page\Service;
use Roducks\Routing\Path;
use Roducks\Framework\App;
use Roducks\Lib\Data\Hash;
use Roducks\Lib\Files\File;
use Roducks\Lib\Files\Directory;
use Roducks\Page\Json;
use Symfony\Component\Yaml\Yaml;

class Storage extends Service {
  private static function _prepare($subfolder)
  {
    if (preg_match('/\//', $subfolder)) {
      $folders = explode('/', $subfolder);
      $index = count($folders) - 1;
      unset($folders[$index]);
      $subfolder = implode('/', $folders);
    }
    else {
      $subfolder = '';
    }

    $folder = App::getSite() . DIRECTORY_SEPARATOR;
    Directory::make(Path::getRoot(), Path::DIR_STORAGE . $folder . $subfolder);
  }

  private static function _getFile($name, $ext)
  {
    return Path::getStorage(App::getSite() . DIRECTORY_SEPARATOR . $name . $ext);
  }

  public function putYml($name, array $content)
  {
    self::_prepare($name);
    $data = Yaml::dump($content);
    File::putContent(self::_getFile($name, Path::YML_EXT), $data);
  }

  public function putHash($name)
  {
    $this->putYml($name, ['hash' => Hash::getToken('r0duck5')]);
  }

  public function getYml($name)
  {
    return Yaml::parse(self::_getFile($name, Path::YML_EXT));
  }

  public function existsYml($name)
  {
    return File::exists(self::_getFile($name, Path::YML_EXT));
  }

  public function removeYml($name)
  {
    File::remove(self::_getFile($name, Path::YML_EXT));
  }

  public function putJson($name, array $content)
  {
    self::_prepare($name);
    $data = Json::encode($content);
    File::putContent(self::_getFile($name, Path::JSON_EXT), $data);
  }

  public function getJson($name, $format = TRUE)
  {
    $content = File::getContent(self::_getFile($name, Path::JSON_EXT));

    if (empty($content)) {
      return [];
    }

    $data = ($format) ? Json::decode($content) : $content;
    return $data;
  }
}
