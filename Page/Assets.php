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

use Roducks\Routing\Path;
use Roducks\Framework\App;
use Roducks\Lib\Utils\Utils;

abstract class Assets {
  public static function getFile($type, $file, array $settings = [])
  {
    $theme = App::getTheme();
    $module = App::getModule();
    $site = App::getSite();
    $uri = NULL;
    $path = NULL;

    if (Utils::isHttp($file)) {
      $uri = $file;
    }
    else {
      switch ($type) {
        case 'Block':
          $folder = 'blocks';
          $block = $settings['blockName'];
          $module = $settings['module'];
          $subfolder = "{$module}/{$block}";
          $path = Path::get([
            Path::getAppSiteModuleBlockAssets(App::getSite(), $module, $block, $file),
            Path::getCommunityModuleBlockAssets($module, $block, $file),
            Path::getCoreModuleBlockAssets($module, $block, $file),
          ]);
          break;
        case 'Theme':
          $path = Path::get([
            Path::getAppSiteThemeAssets(App::getSite(), $theme, $file),
            Path::getCommunityThemeAssets($theme, $file),
            Path::getCoreThemeAssets($theme, $file),
          ]);
          $folder = 'themes';
          $subfolder = $theme;
          break;
        case 'Module':
        case 'View':
          $path = Path::get([
            Path::getAppSiteModuleAssets(App::getSite(), $module, $file),
            Path::getCommunityModuleAssets($module, $file),
            Path::getCoreModuleAssets($module, $file),
          ]);
          $folder = 'modules';
          $subfolder = $module;
          break;
        case 'Library':
          return "/assets/libraries/{$settings['library']}/{$file}";
          break;
      }

      if (!empty($path)) {
        $split = explode('/', Path::getRelative($path));
        $scope = $split[0];

        switch ($scope) {
          case 'app':
            $uri = "/assets/{$scope}/{$site}/{$folder}/{$subfolder}/{$file}";
            break;
          case 'community':
          case 'core':
            $uri = "/assets/{$scope}/{$folder}/{$subfolder}/{$file}";
            break;
        }
      }

    }

    return $uri;
  }
}