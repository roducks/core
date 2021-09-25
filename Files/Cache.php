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

use Roducks\Page\Json;
use Roducks\Routing\Path;
use Roducks\Files\Resource;
use Roducks\Lib\Files\File;
use Roducks\Lib\Files\Directory;

abstract class Cache {

  private static function _set($site, $name, $content)
  {
    $folder = $site ? $site . DIRECTORY_SEPARATOR : '';
    Directory::make(Path::getRoot(), Path::DIR_CACHE . $folder);
    File::putContent(Path::getCache($folder . $name . Path::JSON_EXT), Json::encode($content));
  }

  public static function clear($site)
  {
    $content = [
      [
        'name' => 'routing',
        'data' => array_merge(Resource::getWebRouting($site), Resource::getApiRouting($site)),
      ],
      [
        'name' => 'links',
        'data' => Resource::getLinks($site),
      ],
      [
        'name' => 'services',
        'data' => Resource::getServices($site),
      ],
      [
        'name' => 'cli',
        'data' => Resource::getCli($site),
      ],
      [
        'name' => 'observers',
        'data' => Resource::getObservers($site),
      ],
      [
        'name' => 'models',
        'data' => Resource::getModels($site),
      ],
      [
        'name' => 'schema',
        'data' => Resource::getSchema($site),
      ],
      [
        'name' => 'modules',
        'data' => Resource::getModules($site),
      ],
      [
        'name' => 'blocks',
        'data' => Resource::getBlocks($site),
      ],
      [
        'name' => 'libraries',
        'data' => Resource::getLibraries(),
      ],
    ];

    foreach ($content as $item) {
      self::_set($site, $item['name'], $item['data']);
    }

    $translations = Resource::getTranslations($site);

    foreach ($translations as $lang => $translation) {
      self::_set($site, $lang, $translation);
    }

    self::_set(NULL, 'sites', Resource::getSites());
  }

  public static function get($site, $name)
  {
    $folder = $site ? $site . DIRECTORY_SEPARATOR : '';
    $file = Path::getCache($folder . $name . Path::JSON_EXT);

    if (File::exists($file)) {
      return Json::decode(File::getContent($file));
    }

    return [];
  }

  public static function getLinks($site)
  {
    return self::get($site, 'links');
  }

  public static function getRouting($site)
  {
    return self::get($site, 'routing');
  }

  public static function getServices($site)
  {
    return self::get($site, 'services');
  }

  public static function getCli($site)
  {
    return self::get($site, 'cli');
  }

  public static function getModels($site)
  {
    return self::get($site, 'models');
  }

  public static function getSchema($site)
  {
    return self::get($site, 'schema');
  }

  public static function getModules($site)
  {
    return self::get($site, 'modules');
  }

  public static function getBlocks($site)
  {
    return self::get($site, 'blocks');
  }

  public static function getLibraries($site)
  {
    return self::get($site, 'libraries');
  }

  public static function getSites()
  {
    return self::get(NULL, 'sites');
  }

}
