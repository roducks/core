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
use Roducks\Framework\Helper;
use Roducks\Framework\Manager;
use Roducks\Lib\Files\Directory;

abstract class Resource {
  const TYPE_APP = 'app';
  const TYPE_CORE = 'core';
  const TYPE_COMMUNITY = 'community';

  private static function _getContent($path, array &$data, callable $type, callable $formatter, $ext = Path::YML_EXT)
  {
    $dir = Directory::open($path);

    foreach ($dir['folders'] as $folder) {
      $file = $type($folder);
      $formatter(Config::getContent($file['metadata']['path'] . $file['name'], $ext), $file['metadata'], $data);
    }

    return $data;
  }

  private static function _get($site, $file, callable $formatter, $dist = Path::DIR_CONFIG, $ext = Path::YML_EXT)
  {
    $data = [];
    $paths = [
      [
        'type' => static::TYPE_CORE,
        'dir' => Path::getCoreModules(),
      ],
      [
        'type' => static::TYPE_COMMUNITY,
        'dir' => Path::getCommunityModules(),
      ],
      [
        'type' => static::TYPE_APP,
        'dir' => Path::getAppSiteModules($site),
      ],
    ];

    foreach ($paths as $path) {
      self::_getContent($path['dir'], $data, function ($folder) use ($site, $path, $file, $dist) {
        $module = Helper::removeTrailingSlash($folder);

        switch ($path['type']) {
          case static::TYPE_APP:
            $filePath = Path::getAppSiteModule($site, $module, $dist);
            break;
          case static::TYPE_COMMUNITY:
            $filePath = Path::getCommunityModule($module, $dist);
            break;
          case static::TYPE_CORE:
            $filePath = Path::getCoreModule($module, $dist);
            break;
        }

        return [
          'metadata' => [
            'path' => $filePath,
            'module' => $module,
          ],
          'name' => $file,
        ];
      }, $formatter, $ext);
    }

    return $data;
  }

  private static function _getLibraries($dir, &$config)
  {
    $path = Path::getAssetsLibraries($dir);
    $libraries = Directory::open($path);

    foreach ($libraries['folders'] as $folder) {
      $lib = Helper::removeTrailingSlash($folder);
      $assets = Config::getContent($path . $folder . Path::DIR_CONFIG . 'assets');
      $assets['resource'] = $dir;
      $config[$lib] = $assets;
    }
  }

  public static function getLibraries()
  {
    $config = [];
    $resources = [
      Path::DIR_APP,
      Path::DIR_COMMUNITY,
    ];

    foreach ($resources as $resource) {
      self::_getLibraries($resource, $config);
    }

    return $config;
  }

  public static function getLinks($site)
  {
    return self::_get($site, 'routing', function ($configs, $metadata, &$data) {
      foreach ($configs as $index => $config) {
        $data[$index] = $config['uri'];
      }
    });
  }

  public static function getWebRouting($site)
  {
    return self::_get($site, 'routing', function ($configs, $metadata, &$data) {
      foreach ($configs as $id => $config) {
        $config['path'] = $metadata['path'];
        $config['id'] = $id;
        $data[$config['uri']] = $config;
      }
    });
  }

  public static function getApiRouting($site)
  {
    return self::_get($site, 'api', function ($configs, $metadata, &$data) {
      foreach ($configs as $config) {
        $config['path'] = $metadata['path'];
        $config['api'] = 1;
        $config['uri'] .= '(/{id:int})?';
        $data[$config['uri']] = $config;
      }
    });
  }

  public static function getServices($site)
  {
    return self::_get($site, 'services', function ($configs, $metadata, &$data) {
      foreach ($configs as $index => $config) {
        $data[$index] = $config;
      }
    });
  }

  public static function getCli($site)
  {
    return self::_get($site, 'cli', function ($configs, $metadata, &$data) {
      foreach ($configs as $index => $config) {
        $data[$index] = $config;
      }
    });
  }

  public static function getObservers($site)
  {
    return self::_get($site, 'observers', function ($configs, $metadata, &$data) {
      foreach ($configs as $index => $config) {
        $data[$index][] = $config;
      }
    });
  }

  public static function getModels($site)
  {
    return self::_get($site, 'models', function ($configs, $metadata, &$data) {
      if (is_array($configs)) {
        foreach ($configs as $index => $config) {
          $data[$index] = $config;
        }
      }
    });
  }

  public static function getBlocks($site)
  {
    return self::_get($site, 'blocks', function ($configs, $metadata, &$data) {
      if (is_array($configs)) {
        foreach ($configs as $index => $config) {
          $data[$index] = $config;
        }
      }
    });
  }

  public static function getSchema($site)
  {
    return self::_get($site, 'schema', function ($configs, $metadata, &$data) {
      foreach ($configs as $index => $config) {
        $data[$index] = $config;
      }
    });
  }

  public static function getTranslations($site)
  {
    $res = [];
    $languages = Manager::getContainer('lang')->getCatalog();
    $langs = array_keys($languages);

    foreach ($langs as $lang) {
      $res[$lang] = self::_get($site, $lang, function ($configs, $metadata, &$data) {
        $data = array_merge($data, $configs);
      }, Path::DIR_TRANSLATIONS, Path::JSON_EXT);
    }

    return $res;
  }

  public static function getModules($site)
  {
    return self::_get($site, 'module', function ($configs, $metadata, &$data) {
      $module = $configs['name'] ?? NULL;

      if (!empty($module)) {
        $data[$module] = $metadata['module'];
      }
    });
  }

  public static function getSites()
  {
    $data = [];

    return self::_getContent(Path::getAppSites(), $data, function ($folder) {
      $site = Helper::removeTrailingSlash($folder);
      $filePath = Path::getAppSiteConfig($site);

      return [
        'metadata' => [
          'path' => $filePath,
          'site' => $site,
        ],
        'name' => 'sites',
      ];
    }, function ($configs, $metadata, &$data) {
      foreach ($configs as $index => $config) {
        $config['site'] = $metadata['site'];
        $data[$index] = $config;
      }
    });
  }
}
