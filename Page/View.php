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

use Roducks\Files\Cache;
use Roducks\Services\Db;
use Roducks\Files\Config;
use Roducks\Routing\Path;
use Roducks\Framework\App;
use Roducks\Services\i18n;
use Roducks\Lib\Files\File;
use Roducks\Lib\Output\Html;
use Roducks\Lib\Utils\Utils;
use Roducks\Framework\Helper;
use Roducks\Lib\Output\Error;
use Roducks\Lib\Request\Cors;
use Roducks\Traits\DataTrait;
use Roducks\Services\Language;
use Roducks\Framework\Autoload;
use Roducks\Di\ContainerInterface;

class View extends Frame {
  use DataTrait;

  const LN = "\n";
  
  protected $name = NULL;
  protected $_dispatch = [];
  protected $_container = [];
  protected $_layout = NULL;
  protected $_theme = 'roducks';
  protected $_title = 'Roducks';
  protected $_meta = '';
  protected $_cssView = '';
  protected $_cssTheme = '';
  protected $_cssModule = '';
  protected $_cssLibrary = '';
  protected $_cssBlock = '';
  protected $_jsView = '';
  protected $_jsTheme = '';
  protected $_jsModule = '';
  protected $_jsLibrary = '';
  protected $_jsBlock = '';
  protected $_body = '';
  protected $_scriptsInlineTheme = '';
  protected $_scriptsInlineModule = '';
  protected $_scriptsInlineLibrary = '';
  protected $_scriptsInlineBlock = '';
  protected $_scriptsInlineView = '';
  protected $_scriptsReadyTheme = '';
  protected $_scriptsReadyModule = '';
  protected $_scriptsReadyLibrary = '';
  protected $_scriptsReadyBlock = '';
  protected $_scriptsReadyView = '';
  protected $_settings = [];
  protected $_libraries = [];
  protected $_loadedLibraries = [];

  /**
   * @var \Roducks\Services\Language $lang
   */
  protected $lang;

  /**
   * @var \Roducks\Lib\Request\Cors $cors
   */
  protected $httpHeaders;

  /**
   * @var \Roducks\Services\i18n $i18n
   */
  protected $i18n;

  public function __construct(array $settings, Db $db, Language $lang, Cors $cors, i18n $i18n)
  {
    parent::__construct($settings, $db);
    $this->lang = $lang;
    $this->cors = $cors;
    $this->i18n = $i18n;
    $this->_libraries = Cache::getLibraries(App::getSite());
    $this->settings('_data', [
      'lang' => $this->lang->get(),
      'autheticated' => false,
      'user' => [
        'name' => 'Guest',
      ],
      'links' => Autoload::$links,
    ]);
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('lang'),
      $container->get('cors'),
      $container->get('i18n')
    );
  }

  private function _getViewPath($file)
  {
    return [
      Path::getAppSiteModulePageView(App::getSite(), App::getModule(), $file),
      Path::getCommunityModulePageView(App::getModule(), $file),
      Path::getCoreModulePageView(App::getModule(), $file),
    ];
  }

  private function _getLayoutPath($theme, $file)
  {
    return [
      Path::getAppSiteThemeLayout(App::getSite(), $theme, $file),
      Path::getCommunityThemeLayout($theme, $file),
      Path::getCoreThemeLayout($theme, $file),
    ];
  }

  private function _getName()
  {
    $file = $this->name . Path::HTML_EXT;

    return Path::get($this->_getViewPath($file));
  }

  private function _getLayout($name)
  {
    $theme = $this->_theme;
    $file = $name . Path::HTML_EXT;

    return Path::get($this->_getLayoutPath($theme, $file));
  }

  private static function getTimeStamp($file)
  {
    if (!Utils::isHttp($file) && App::errors()) {
      $symbol = preg_match('/\?/', $file) ? '&' : '?';
      $file .= "{$symbol}t=" . time();
    }

    return $file;
  }

  private function _script($key, $type, $name, array $settings = [])
  {
    $path = '';

    switch ($type) {
      case 'Block':
          $block = $settings['blockName'];
          $module = $settings['module'];
          $file = Path::DIR_SCRIPTS . $name;
          $path = Path::get([
            Path::getAppSiteModuleBlockAssets(App::getSite(), $module, $block, $file),
            Path::getCommunityModuleBlockAssets($module, $block, $file),
            Path::getCoreModuleBlockAssets($module, $block, $file),
          ]);
        break;
      case 'Theme':
          $path = $this->_getThemeScript($name);
        break;
      case 'Module':
      case 'View':
          $path = $this->_getModuleScript($name);
        break;
      case 'Library':
          $path = Path::getLibraryScript($settings['library'], $name);
        break;
    }

    $file = File::getContent($path);

    if (!empty($file)) {
      $var = "_scripts{$key}";
      $this->$var .= $file;
    }
  }

  private function __inline($type, $name, array $settings = [])
  {
    $this->_script("Inline{$type}", $type, $name, $settings);
  }

  private function __ready($type, $name, array $settings = [])
  {
    $this->_script("Ready{$type}", $type, $name, $settings);
  }

  private function __css($key, $href, array $attrs = [])
  {
    $var = "_css{$key}";
    $this->$var .= Html::link(self::getTimeStamp($href), ['rel' => 'stylesheet', 'type' => 'text/css'] + $attrs) . self::LN;
  }

  private function __js($key, $src, array $attrs = [])
  {
    $var = "_js{$key}";
    $this->$var .= Html::script(self::getTimeStamp($src), ['type' => 'text/javascript'] + $attrs) . self::LN;
  }

  protected function _assets($type, $config, array $settings = [])
  {
    if (!empty($config)) {
      foreach ($config as $key => $asset) {
        if ($key == 'libraries') {
          foreach ($asset as $library) {
            if (isset($this->_libraries[$library]) && !isset($this->_loadedLibraries[$library])) {
              $this->_loadedLibraries[$library] = 1;
              $resource = $this->_libraries[$library]['resource'];
              unset($this->_libraries[$library]['resource']);
              $settings['library'] = $resource . $library;
              $this->_assets('Library', $this->_libraries[$library], $settings);
            }
          }
        }

        foreach ($asset as $method => $item) {
          if ($key == 'scripts') {
            $fx = "__{$method}";

            if (method_exists($this, $fx)) {
              foreach ($item as $script) {
                $this->$fx($type, $script, $settings);
              }
            }
          }
          else {
            $fx = "__{$key}";
            $attrs = $item['attributes'] ?? [];

            if (method_exists($this, $fx) && isset($item['src'])) {
              $file = Assets::getFile($type, $item['src'], $settings);
              $this->$fx($type, $file, $attrs);
            }
          }

        }
      }
    }
  }

  private static function _getModuleAssetsConfig()
  {
    $file = 'assets' . Path::YML_EXT;
    $path = Path::get([
      Path::getAppSiteModuleConfig(App::getSite(), App::getModule(), $file),
      Path::getCommunityModuleConfig(App::getModule(), $file),
      Path::getCoreModuleConfig(App::getModule(), $file),
    ]);

    return Config::getContent(File::removeExt($path));
  }

  private function _assetsTheme()
  {
    $theme = $this->_theme;
    $file = 'assets' . Path::YML_EXT;
    $path = Path::get([
      Path::getAppSiteThemeConfig(App::getSite(), $theme, $file),
      Path::getCommunityThemeConfig($theme, $file),
      Path::getCoreThemeConfig($theme, $file),
    ]);

    $this->_assets('Theme', Config::getContent(File::removeExt($path)));
  }

  private function _assetsModule()
  {
    $this->_assets('Module', self::_getModuleAssetsConfig());
  }

  private function _getThemeScript($file)
  {
    $theme = $this->_theme;

    return Path::get([
      Path::getAppSiteThemeScripts(App::getSite(), $theme, $file),
      Path::getCommunityThemeScripts($theme, $file),
      Path::getCoreThemeScripts($theme, $file),
    ]);
  }

  private function _getModuleScript($file)
  {
    return Path::get([
      Path::getAppSiteModuleScripts(App::getSite(), App::getModule(), $file),
      Path::getCommunityModuleScripts(App::getModule(), $file),
      Path::getCoreModuleScripts(App::getModule(), $file),
    ]);
  }

  public function setDispatch(array $dispatch)
  {
    $this->_dispatch = $dispatch;
  }

  public function getDispatch()
  {
    return $this->_dispatch;
  }

  public function container($name, array $blocks)
  {
    $this->_container[$name] = $blocks;
  }

  public function getContainer($name)
  {
    return $this->_container[$name] ?? [];
  }

  public function load($name = NULL)
  {
    $dispatch = $this->getDispatch();
    $view = $name ?? Helper::getDashedWord($dispatch['method']);

    $this->name = $view;
  }

  public function theme($name)
  {
    $this->_theme = $name;
  }

  public function layout($name, $view = NULL)
  {
    if ($name == 'doctype') {
      return FALSE;
    }

    $this->_layout = $name;
    $this->load($view);
  }

  public function title($name)
  {
    $this->_title = $name;
  }

  public function metaTag(array $attrs)
  {
    $this->_meta .= Html::meta($attrs) . self::LN;
  }

  public function meta($name, $content)
  {
    $attrs = [
      'name' => $name,
      'content' => $content,
    ];
    $this->metaTag($attrs);
  }

  public function viewport()
  {
    $this->meta('viewport', 'width=device-width, initial-scale=1.0');
  }

  public function css($href, array $attrs = [])
  {
    $this->__css('View', Assets::getFile('Module', $href), $attrs);
  }

  public function js($src, array $attrs = [])
  {
    $this->__js('View', Assets::getFile('Module', $src), $attrs);
  }

  public function scriptInline($name)
  {
    $this->__inline('Module', $name);
  }

  public function scriptReady($name)
  {
    $this->__ready('Module', $name);
  }

  public function asset($view)
  {
    $config = self::_getModuleAssetsConfig();

    if (isset($config['view'][$view])) {
      $this->_assets('View', $config['view'][$view]);
    }
  }

  public function body(array $attrs)
  {
    $this->_body = Html::getAttrs($attrs);
  }

  public function settings($key, $value = NULL)
  {
    if (is_array($key)) {
      $this->_settings = $key;
    }
    else {
      $this->_settings[$key] = $value;
    }
  }

  private function _getDocumentReady()
  {
    $documentReady = [
      'path' => Path::getTemplate($this->_theme, 'document.ready' . Path::JS_EXT),
      'content' => '',
    ];

    if (file_exists($documentReady['path'])) {
      $scripts = 
        self::LN . ' ' . $this->_scriptsReadyLibrary .
        self::LN . ' ' . $this->_scriptsReadyTheme . 
        self::LN . ' ' . $this->_scriptsReadyModule . 
        self::LN . ' ' . $this->_scriptsReadyView .
        self::LN . ' ' . $this->_scriptsReadyBlock;
      $documentReady['content'] = Duckling::render($documentReady['path']);
      $documentReady['content'] = str_replace('// {{ scripts }}', '{{ scripts }}', $documentReady['content']);
      $documentReady['content'] = Duckling::parse($documentReady['content'], [
        'scripts' => $scripts,
      ]);
    }

    $settingsData = (!empty($this->_settings)) ? Json::encode($this->_settings) : '{}';
    $settings = self::LN . '$roducks.settings.init(' . $settingsData . ');' . self::LN;

    return 
      Html::scriptInline(
        $settings . 
        self::LN . $this->_scriptsInlineLibrary .
        self::LN . $this->_scriptsInlineTheme . 
        self::LN . $this->_scriptsInlineModule .
        self::LN . $this->_scriptsInlineView .
        self::LN . $this->_scriptsInlineBlock .
        self::LN . $documentReady['content']);
  }

  private function _getCss()
  {
    return 
      $this->_cssLibrary . 
      $this->_cssTheme . 
      $this->_cssModule . 
      $this->_cssView . 
      $this->_cssBlock;
  }

  private function _getJs()
  {
    return 
      $this->_jsLibrary . 
      $this->_jsTheme . 
      $this->_jsModule . 
      $this->_jsView . 
      $this->_jsBlock;
  }

  public function viewer($type, $tpl, $content)
  {
    $ret = '';

    if (App::inLocal()) {
      $ret .= "<!-- @{$type}:start " . self::LN;

      if (in_array($type, ['module', 'block'])) {
        $dispatch = $this->getDispatch();
        $class = Helper::setDispatcher([$dispatch['class'], $dispatch['method']]);

        if (isset($dispatch['id'])) {
          $ret .= " ID: {$dispatch['id']}" . self::LN;
        }

        $ret .= " CLASS: {$class}" . self::LN;
      }

      $ret .= " VIEW: {$tpl}";
      $ret .= self::LN . "-->" . self::LN;
    }

    $ret .= $content;

    if (App::inLocal()) {
      $ret .= self::LN . "<!-- @{$type}:end -->";
    }

    return $ret;
  }

  public function output()
  {
    $this->observer('http.headers', [$this->cors]);
    $this->cors->apply();
    $viewPath = !empty($this->name) ? $this->_getName() : NULL;
    $layout = !empty($this->_layout) ? $this->_getLayout($this->_layout) : NULL;
    $doctype = $this->_getLayout('doctype');
    $debug = 'debug';

    // Avoid looping.
    if (Autoload::$debugger) {
      $viewPath = Path::getCoreModulePageView(Autoload::DEFAULT_MODULE, $this->name . Path::HTML_EXT);
      $debug = 'fatal';
    }

    if (!empty($this->name)) {
      try {
        if (!empty($doctype)) {
          Autoload::$theme = $this->_theme;
          Autoload::$text = $this->i18n;
          Autoload::$blocks = Cache::getBlocks(App::getSite());

          $this->_assetsTheme();
          $this->_assetsModule();

          $lang = $this->lang->get() ?? App::DEFAULT_LANG;
          $html = Duckling::render($doctype, [
            'lang' => $lang,
            'title' => $this->_title,
            'meta' => $this->_meta,
            'attrs' => $this->_body,
            'css' => '{{ css }}',
            'js' => '{{ js }}',
            'scripts' => '{{ scripts }}',
          ]);

          if (File::exists($viewPath)) {
            $view = $this->viewer('module', $viewPath, File::getContent($viewPath));

            if (!empty($this->_layout)) {
              try {
                $body = Duckling::render($layout, [
                  'view' => $view,
                ]);
              } catch (\Exception $e) {
                $layout = $this->_getLayoutPath($this->_theme, $this->_layout . Path::HTML_EXT);
                Error::$debug([
                  $layout[0],
                  "No layout",
                ]);
              }
            }
            else {
              $body = $view;
            }

            $page = Duckling::parse($html, [
              'body' => $body,
              'css' => $this->_getCss(),
              'js' => $this->_getJs(),
              'scripts' => $this->_getDocumentReady(),
            ]);

            echo $this->viewer('layout', $doctype, Duckling::parse($page, $this->getData()));
          }
          else {
            $viewPath = $this->_getViewPath($this->name . Path::HTML_EXT);
            Error::$debug([
              "File: {$viewPath[0]}",
              "was not defined."
            ]);
          }
        }
        else {
          Error::$debug([
            "{$doctype} was not found."
          ]);
        }
      }
      catch (\Exception $e) {
        die($e->getMessage());
      }
    }
    else {
      Error::$debug([
        "No view was not defined."
      ]);
    }
  }
}
