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

use Roducks\Services\Db;
use Roducks\Files\Config;
use Roducks\Routing\Path;
use Roducks\Services\i18n;
use Roducks\Lib\Output\Error;
use Roducks\Lib\Request\Cors;
use Roducks\Services\Language;
use Roducks\Di\ContainerInterface;

class BlockView extends View {
  protected $_path = NULL;

  /**
   * @var \Roducks\Page\View $pageView
   */
  protected $view;

  /**
   * 
   */
  public function __construct(array $settings, Db $db, Language $lang, Cors $cors, i18n $i18n, View $view)
  {
    parent::__construct($settings, $db, $lang, $cors, $i18n);
    $this->view = $view;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('lang'),
      $container->get('cors'),
      $container->get('i18n'),
      $container->get('view')
    );
  }

  private function _getName()
  {
    return $this->_path . Path::DIR_VIEWS . $this->name . Path::HTML_EXT;
  }

  private function _getConfig()
  {
    return Config::getContent($this->_path . Path::DIR_CONFIG . 'assets');
  }

  private function _getSettings()
  {
    $path = Path::getRelative($this->_path);
    $split = explode('/', $path);
    $resource = $split[0];

    switch ($resource) {
      case 'app':
        $module = $split[4];
        $blockName = $split[6];
        break;
      case 'community':
      case 'core':
        $module = $split[2];
        $blockName = $split[4];
        break;
    }

    return [
      'module' => $module,
      'blockName' => $blockName,
    ];
  }

  public function setPath($path)
  {
    $this->_path = $path;
  }

  public function getBlocks($name)
  {
    return $this->view->getContainer($name);
  }

  public function output()
  {
    $view = $this->_getName();

    try {
      $config = $this->_getConfig();
      $settings = $this->_getSettings();
      $this->view->_assets('Block', $config, $settings);
      echo $this->viewer('block', $view, Duckling::render($view, $this->getData(), $settings));
    } catch (\Exception $e) {
      $error = Error::alert([
        'title' => "'{$settings['blockName']}' block",
        'message' => "File: '{$view}' not found.",
      ], TRUE);
      echo $this->viewer('block', $view, $error);
    }
  }
}
