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
namespace Roducks\Modules\Setup\Db\Schema;

use Roducks\Db\Schema;
use Roducks\Framework\Manager;
use Roducks\Lib\Data\Hash;
use Roducks\Lib\Sql\Table;

class Setup extends Schema {
  protected $name = 'setup';

  public function install(Table $table)
  {
    $table->create($this->name)
      ->id()
      ->varchar('module')->notNull()
      ->timestamps()
      ->pk('id')
      ->execute();

    Manager::getContainer('storage')->putYml('install', ['hash' => Hash::getToken('r0duck5')]);
  }

  public function uninstall(Table $table)
  {
    $table->forceDrop(0);
    $table->drop($this->name);
    $table->forceDrop(1);
  }
}
