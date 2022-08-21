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

namespace Roducks\Modules\App\Cli;

use Roducks\Files\Cache;
use Roducks\Services\Db;
use Roducks\Framework\App;
use Roducks\Lib\Output\Cli;
use Roducks\Framework\Helper;
use Roducks\Framework\Autoload;
use Roducks\Di\ContainerInterface;
use Roducks\Services\Storage;

class Module extends Cli {

  /**
   * @var \Roducks\Services\Storage $storage
   */
  protected $storage;

  /**
   * 
   */
  public function __construct(array $settings, Db $db, Storage $storage)
  {
    parent::__construct($settings, $db);
    $this->storage = $storage;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db'),
      $container->get('storage')
    );
  }

  private function _getModule($module)
  {
    $list = Cache::getModules(App::getSite());

    return $list[$module] ?? NULL;
  }

  private function _execute($module, $method, $message, $params = [])
  {
    $output = TRUE;

    if (!empty($module)) {
      if ($method == 'install') {
        $modulesList = Cache::getModules(App::getSite());
        $moduleName = $modulesList[$module] ?? NULL;

        if (!empty($moduleName)) {
          $this->storage->putHash("modules/{$moduleName}");
        }
      }

      $list = Cache::getSchema($this->getFlag('--site', App::getSite()));
      $dispatch = $list[$module]['dispatch'] ?? NULL;

      if (!empty($dispatch)) {
        $class = Helper::setDispatcher([$dispatch, $method]);
        Autoload::dispatch($class, $this->settings, $params);
      }
      else {
        if ($method == 'data') {
          $output = FALSE;
        }
      }

      if ($output) {
        $this->output([
          $this->bgLightBlue("{$message}:"),
          $this->colorGreen($module),
        ]);
      }
    }
  }

  private function _isInstalled($schema)
  {
    $setup = $this->db->model('setup');
    
    return $setup->hasResults([
      ['module:str', '=', $schema]
    ]);
  }

  private function _register($module)
  {
    $setup = $this->db->model('setup');
    $setup->prepare();
    $setup->setModule($module);
    $setup->setCreatedAt(date('Y-m-d H:i:s'));
    $setup->save();
  }

  private function _delete($module)
  {
    $setup = $this->db->model('setup');
    $setup->select([
      'id',
    ]);
    $setup->where('module:str', '=', $module);
    $setup->execute();

    while ($row = $setup->fetch()) {
      $query = $this->db->model('setup');
      $query->load(intval($row['id']));
      $query->delete();
    }

    $moduleName = $this->_getModule($module);
    $this->storage->removeYml("modules/{$moduleName}");
  }

  private function _install($module = NULL)
  {
    if ($module !== 'setup') {
      $this->_register($module);
      $pass = TRUE;
    }
    else {
      $pass = !$this->storage->existsYml('install');
    }

    if ($pass) {
      $this->_execute($module, 'install', 'Installed', [$this->db->table()]);
    }
    else {
      $this->failure('Error', [
        'Setup module cannot be run twice.',
      ]);
    }

  }

  private function _uninstall($module = NULL)
  {
    if ($module == 'setup') {
      $this->storage->removeYml('install');
    }
    else {
      $this->_delete($module);
    }

    $this->_execute($module, 'uninstall', 'Uninstalled', [$this->db->table()]);
  }

  private function _data($module)
  {
    $this->_execute($module, 'data', 'Imported data');
  }

  private function _validate($module, callable $fx)
  {
    $list = Cache::getModules(App::getSite());

    if (!empty($module)) {
      if (isset($list[$module])) {
        $fx($module);
      }
      else {
        $this->failure('Error', [
          "'{$module}' module is not enabled.",
        ]);
      }
    }
    else {
      $this->failure('Error', [
        'No module was given.',
      ]);
    }
  }

  public function install($module = NULL)
  {
    $this->_validate($module, function ($module) {
      $pass = ($module == 'setup') ? TRUE : !$this->_isInstalled($module);

      if ($pass) {
        $this->_install($module);
        $this->_data($module);
      }
      else {
        $this->warning('Notice', [
          "'{$module}' module is already installed."
        ]);
      }
    });
  }

  public function uninstall($module = NULL)
  {
    $this->_validate($module, function ($module) {
      $pass = ($module == 'setup') ? TRUE : $this->_isInstalled($module);

      if ($pass) {
        $this->_uninstall($module);
      }
      else {
        $this->warning('Notice', [
          "'{$module}' module is already unstalled."
        ]);
      }
    });
  }

  public function update($module = NULL)
  {
    $this->_validate($module, function ($module) {
      $pass = ($module == 'setup') ? FALSE : $this->_isInstalled($module);

      if ($pass) {
        $version = $this->getFlag('--version', 1);
        $this->_execute($module, "update_{$version}", 'Updated data');
      }
      else {
        $this->failure('Error', [
          "'{$module}' module is not installed."
        ]);
      }
    });

  }
}
