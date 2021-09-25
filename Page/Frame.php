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

use Roducks\Di\Di;
use Roducks\Services\Db;
use Roducks\Routing\Path;
use Roducks\Lib\Files\File;
use Roducks\Lib\Utils\Utils;
use Roducks\Framework\Autoload;
use Roducks\Di\ContainerInterface;

abstract class Frame extends Di {
  /**
   * 
   */
  protected $db = [];

  /**
   * 
   */
  public function __construct(array $settings, Db $db)
  {
    $this->settings = $settings;
    $this->db = $db;
  }

  public static function init(ContainerInterface $container)
  {
    return new static(
      $container->get('settings'),
      $container->get('db')
    );
  }

  protected function observer($name, array $params = [])
  {
    Autoload::observer($name, $params);
  }

  protected function getJsonData($name)
  {
    $file = Path::getModuleDataFromClass($this, $name . Path::JSON_EXT);
    $data = [];

    if (File::exists($file)) {
      $content = File::getContent($file);
      $data = Json::decode($content);
    }

    return $data;
  }

  protected function getCsvData($name, callable $callback)
  {
    $file = Path::getModuleDataFromClass($this, $name . Path::CSV_EXT);

    return Utils::getCsvData($file, $callback);
  }
}
