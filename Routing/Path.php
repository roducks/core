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

namespace Roducks\Routing;

use Roducks\Framework\App;
use Roducks\Lib\Files\File;

abstract class Path {
  const DIR_PUBLIC = 'public/';
  const DIR_APP = 'app/';
  const DIR_COMMUNITY = 'community/';
  const DIR_CONFIG = 'config/';
  const DIR_CORE = 'core/';
  const DIR_CACHE = 'cache/';
  const DIR_STORAGE = 'storage/';
  const DIR_ASSETS = 'assets/';
  const DIR_SCRIPTS = 'scripts/';
  const DIR_DATA = 'data/';
  const DIR_BLOCKS = 'Blocks/';
  const DIR_CLI = 'Cli/';
  const DIR_MODELS = 'Models/';
  const DIR_OBSERVERS = 'Observers/';
  const DIR_SITES = 'Sites/';
  const DIR_MODULES = 'Modules/';
  const DIR_PAGE = 'Page/';
  const DIR_DB = 'Db/';
  const DIR_VIEWS = 'views/';
  const DIR_THEMES = 'themes/';
  const DIR_LAYOUT = 'layout/';
  const DIR_TEMPLATES = 'templates/';
  const DIR_LIBRARIES = 'libraries/';
  const DIR_TRANSLATIONS = 'translations/';

  const PHP_EXT = '.php';
  const YML_EXT = '.yml';
  const JSON_EXT = '.json';
  const HTML_EXT = '.html';
  const JS_EXT = '.js';
  const CSV_EXT = '.csv';

  public static function get(array $paths)
  {
    foreach ($paths as $path) {
      if (File::exists($path)) {
        return $path;
      }
    }

    return NULL;
  }

  public static function getRoot($path = '')
  {
    return RDKS_ROOT . $path;
  }

  public static function getRelative($path)
  {
    return str_replace(self::getRoot(), '', $path);
  }

  public static function getCache($path = '')
  {
    return self::getRoot(static::DIR_CACHE . $path);
  }

  public static function getStorage($path = '')
  {
    return self::getRoot(static::DIR_STORAGE . $path);
  }

  /**
   * @example /app/
   */
  public static function getApp($path = '')
  {
    return self::getRoot(static::DIR_APP . $path);
  }

  /**
   * @example /app/Sites/
   */
  public static function getAppSites($path = '')
  {
    return self::getApp(static::DIR_SITES . $path);
  }

  /**
   * @example /app/Sites/${SITE}/
   */
  public static function getAppSite($site, $path = '')
  {
    return self::getAppSites($site . DIRECTORY_SEPARATOR . $path);
  }

  public static function getAppSiteThemes($site, $path = '')
  {
    return self::getAppSite($site, static::DIR_THEMES . $path);
  }

  public static function getAppSiteTheme($site, $theme, $path = '')
  {
    return self::getAppSiteThemes($site, $theme . DIRECTORY_SEPARATOR. $path);
  }

  public static function getAppSiteThemeLayout($site, $theme, $path = '')
  {
    return self::getAppSiteTheme($site, $theme, static::DIR_LAYOUT . $path);
  }

  public static function getAppSiteThemeTemplates($site, $theme, $path = '')
  {
    return self::getAppSiteTheme($site, $theme, static::DIR_TEMPLATES . $path);
  }

  public static function getAppSiteThemeConfig($site, $theme, $path = '')
  {
    return self::getAppSiteTheme($site, $theme, static::DIR_CONFIG . $path);
  }

  public static function getAppSiteThemeAssets($site, $theme, $path = '')
  {
    return self::getAppSiteTheme($site, $theme, static::DIR_ASSETS . $path);
  }

  public static function getAppSiteThemeScripts($site, $theme, $path = '')
  {
    return self::getAppSiteThemeAssets($site, $theme, static::DIR_SCRIPTS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/config/
   */
  public static function getAppSiteConfig($site, $path = '')
  {
    return self::getAppSite($site, static::DIR_CONFIG . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/
   */
  public static function getAppSiteModules($site, $path = '')
  {
    return self::getAppSite($site, static::DIR_MODULES . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/
   */
  public static function getAppSiteModule($site, $module, $path = '')
  {
    return self::getAppSiteModules($site, $module . DIRECTORY_SEPARATOR . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/config/
   */
  public static function getAppSiteModuleConfig($site, $module, $path = '')
  {
    return self::getAppSiteModule($site, $module, static::DIR_CONFIG . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/config/
   */
  public static function getAppSiteModulePageView($site, $module, $path = '')
  {
    return self::getAppSiteModule($site, $module, static::DIR_PAGE . static::DIR_VIEWS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/assets/
   */
  public static function getAppSiteModuleAssets($site, $module, $path = '')
  {
    return self::getAppSiteModule($site, $module, static::DIR_ASSETS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/config/
   */
  public static function getAppSiteModuleScripts($site, $module, $path = '')
  {
    return self::getAppSiteModuleAssets($site, $module, static::DIR_SCRIPTS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Blocks/
   */
  public static function getAppSiteModuleBlocks($site, $module, $path = '')
  {
    return self::getAppSiteModule($site, $module, static::DIR_BLOCKS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Blocks/${block}/
   */
  public static function getAppSiteModuleBlock($site, $module, $block, $path = '')
  {
    return self::getAppSiteModuleBlocks($site, $module, $block . DIRECTORY_SEPARATOR . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Blocks/${block}/
   */
  public static function getAppSiteModuleBlockAssets($site, $module, $block, $path = '')
  {
    return self::getAppSiteModuleBlock($site, $module, $block, static::DIR_ASSETS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Models/
   */
  public static function getAppSiteModuleModels($site, $module, $path = '')
  {
    return self::getAppSiteModule($site, $module, static::DIR_MODELS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Models/${model}/
   */
  public static function getAppSiteModuleModel($site, $module, $model, $path = '')
  {
    return self::getAppSiteModuleModels($site, $module, $model . DIRECTORY_SEPARATOR . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Observers/
   */
  public static function getAppSiteModuleObservers($site, $module, $path = '')
  {
    return self::getAppSiteModule($site, $module, static::DIR_OBSERVERS . $path);
  }

  /**
   * @example /app/Sites/${SITE}/Modules/${module}/Observers/${observer}/
   */
  public static function getAppSiteModuleObserver($site, $module, $observer, $path = '')
  {
    return self::getAppSiteModuleObservers($site, $module, $observer . DIRECTORY_SEPARATOR . $path);
  }

  public static function getCommunity($path = '')
  {
    return self::getRoot(static::DIR_COMMUNITY . $path);
  }

  public static function getCommunityModules($path = '')
  {
    return self::getCommunity(static::DIR_MODULES . $path);
  }

  public static function getCommunityModule($module, $path = '')
  {
    return self::getCommunityModules($module . DIRECTORY_SEPARATOR . $path);
  }

  public static function getCommunityModuleAssets($module, $path = '')
  {
    return self::getCommunityModule($module, static::DIR_ASSETS . $path);
  }

  public static function getCommunityModuleConfig($module, $path = '')
  {
    return self::getCommunityModule($module, static::DIR_CONFIG . $path);
  }

  public static function getCommunityModulePageView($module, $path = '')
  {
    return self::getCommunityModule($module, static::DIR_PAGE . static::DIR_VIEWS . $path);
  }

  public static function getCommunityModuleBlocks($module, $path = '')
  {
    return self::getCommunityModule($module, static::DIR_BLOCKS . $path);
  }

  public static function getCommunityModuleBlock($module, $block, $path = '')
  {
    return self::getCommunityModuleBlocks($module, $block . DIRECTORY_SEPARATOR . $path);
  }

  public static function getCommunityModuleBlockAssets($module, $block, $path = '')
  {
    return self::getCommunityModuleBlock($module, $block, static::DIR_ASSETS . $path);
  }

  public static function getCommunityThemeAssets($theme, $path = '')
  {
    return self::getCommunityTheme($theme, static::DIR_ASSETS . $path);
  }

  public static function getCommunityModuleScripts($module, $path = '')
  {
    return self::getCommunityThemeAssets($module, static::DIR_ASSETS . $path);
  }

  public static function getCommunityThemes($path = '')
  {
    return self::getCommunity(static::DIR_THEMES . $path);
  }

  public static function getCommunityTheme($theme, $path = '')
  {
    return self::getCommunityThemes($theme . DIRECTORY_SEPARATOR . $path);
  }

  public static function getCommunityThemeTemplates($theme, $path = '')
  {
    return self::getCommunityTheme($theme, static::DIR_TEMPLATES . $path);
  }

  public static function getCommunityThemeScripts($theme, $path = '')
  {
    return self::getCommunityThemeAssets($theme, static::DIR_SCRIPTS . $path);
  }

  public static function getCommunityThemeConfig($theme, $path = '')
  {
    return self::getCommunityTheme($theme, static::DIR_CONFIG . $path);
  }

  public static function getCommunityThemeLayout($theme, $path = '')
  {
    return self::getCommunityTheme($theme, static::DIR_LAYOUT . $path);
  }

  /**
   * @example /core/
   */
  public static function getCore($path = '')
  {
    return self::getRoot(static::DIR_CORE . $path);
  }

  /**
   * @example /core/
   */
  public static function getCoreTemplates($path = '')
  {
    return self::getCore(static::DIR_TEMPLATES . $path);
  }

  /**
   * @example /core/Modules/
   */
  public static function getCoreModules($path = '')
  {
    return self::getCore(static::DIR_MODULES . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModule($module, $path = '')
  {
    return self::getCoreModules($module . DIRECTORY_SEPARATOR . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModuleBlocks($module, $path = '')
  {
    return self::getCoreModule($module, static::DIR_BLOCKS . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModuleBlock($module, $block, $path = '')
  {
    return self::getCoreModuleBlocks($module, $block . DIRECTORY_SEPARATOR . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModuleBlockAssets($module, $block, $path = '')
  {
    return self::getCoreModuleBlock($module, $block, static::DIR_ASSETS . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModuleAssets($module, $path = '')
  {
    return self::getCoreModule($module, static::DIR_ASSETS . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModuleScripts($module, $path = '')
  {
    return self::getCoreModuleAssets($module, static::DIR_SCRIPTS . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModuleConfig($module, $path = '')
  {
    return self::getCoreModule($module, static::DIR_CONFIG . $path);
  }

  /**
   * @example /core/Modules/${module}/
   */
  public static function getCoreModulePageView($module, $path = '')
  {
    return self::getCoreModule($module, static::DIR_PAGE . static::DIR_VIEWS . $path);
  }

  /**
   * 
   */
  public static function getCoreThemes($path = '')
  {
    return self::getCore(static::DIR_THEMES . $path);
  }

  /**
   * 
   */
  public static function getCoreTheme($theme, $path = '')
  {
    return self::getCoreThemes($theme . DIRECTORY_SEPARATOR . $path);
  }

  /**
   * 
   */
  public static function getCoreThemeConfig($theme, $path = '')
  {
    return self::getCoreTheme($theme, static::DIR_CONFIG . $path);
  }

  public static function getCoreThemeLayout($theme, $path = '')
  {
    return self::getCoreTheme($theme, static::DIR_LAYOUT . $path);
  }

  public static function getCoreThemeTemplates($theme, $path = '')
  {
    return self::getCoreTheme($theme, static::DIR_TEMPLATES . $path);
  }

  public static function getCoreThemeAssets($theme, $path = '')
  {
    return self::getCoreTheme($theme, static::DIR_ASSETS . $path);
  }

  /**
   * 
   */
  public static function getCoreThemeScripts($theme, $path = '')
  {
    return self::getCoreThemeAssets($theme, static::DIR_SCRIPTS . $path);
  }

  public static function getLibraryScript($library, $file)
  {
    return self::getRoot(self::DIR_PUBLIC . self::DIR_ASSETS . self::DIR_LIBRARIES . $library . DIRECTORY_SEPARATOR . self::DIR_SCRIPTS . $file);
  }

  public static function getTemplate($theme, $file)
  {
    return self::get([
      self::getAppSiteThemeTemplates(App::getSite(), $theme, $file),
      self::getCommunityThemeTemplates($theme, $file),
      self::getCoreThemeTemplates($theme, $file),
    ]);
  }

  /**
   * 
   */
  public static function getFromClass($class)
  {
    $folders = explode('\\', get_class($class));
    $path = NULL;

    switch ($folders[0]) {
      case 'App':
        $folders[0] = 'app';
        $path = [
          $folders[0],
          $folders[1],
          $folders[2],
          $folders[3],
          $folders[4],
        ];
        break;
      case 'Roducks':
        $folders[0] = 'core';
        $path = [
          $folders[0],
          $folders[1],
          $folders[2],
        ];
        break;
      case 'Community':
        $folders[0] = 'community';
        $path = [
          $folders[0],
          $folders[1],
          $folders[2],
        ];
        break;
    }

    return self::getRoot(implode('/', $path)) . DIRECTORY_SEPARATOR;
  }

  public static function getDbDataFromClass($class, $path = '')
  {
    return self::getFromClass($class) . static::DIR_DB . static::DIR_DATA . $path;
  }

  public static function getPageViewFromClass($class, $path = '')
  {
    return self::getFromClass($class) . static::DIR_PAGE . static::DIR_VIEWS . $path;
  }

  public static function getModuleDataFromClass($class, $path = '')
  {
    return self::getFromClass($class) . static::DIR_DATA . $path;
  }

  public static function getBlockFromClass($class, $path = '')
  {
    $ns = get_class($class);
    $folders = explode('\\', $ns);

    switch ($folders[0]) {
      case 'App':
        $folders[0] = 'app';
        break;
      case 'Community':
        $folders[0] = 'community';
        break;
      case 'Roducks':
        $folders[0] = 'core';
        break;
    }

    array_pop($folders);

    $folder = implode('/', $folders) . DIRECTORY_SEPARATOR . $path;

    return self::getRoot($folder);
  }

  public static function getAssetsLibraries($path = '')
  {
    return self::getRoot(implode('', [
      static::DIR_PUBLIC,
      static::DIR_ASSETS,
      static::DIR_LIBRARIES,
    ])) . $path;
  }

}
