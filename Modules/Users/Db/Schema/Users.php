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
namespace Roducks\Modules\Users\Db\Schema;

use Roducks\Db\Schema;
use Roducks\Lib\Sql\Table;
use Roducks\Lib\Utils\Date;

class Users extends Schema {

  public function install(Table $table)
  {
    $table->create('eav')
      ->id()
      ->bigint('id_rel')->unsigned()->notNull()
      ->varchar('entity')->notNull()
      ->varchar('attribute')->notNull()
      ->text('value')->notNull()
      ->timestamps()
      ->pk()
      ->execute();

    $table->create('roles_types')
      ->id()
      ->varchar('name')->notNull()
      ->timestamps()
      ->pk()
      ->execute();

    $table->create('roles')
      ->id()
      ->bigint('id_role_type')->unsigned()->notNull()
      ->varchar('name')->notNull()
      ->varchar('config')->notNull()
      ->timestamps()
      ->pk()
      ->fk('id_role_type', 'roles_types', 'id')
      ->execute();

    $table->create('users')
      ->id()
      ->bigint('id_role')->unsigned()->notNull()
      ->varchar('name')->notNull()
      ->varchar('email')->notNull()
      ->varchar('first_name')->notNull()
      ->varchar('last_name')->notNull()
      ->varchar('picture')->null()
      ->enum('gender', ['male', 'female', 'none'])->default('none')
      ->varchar('password')->notNull()
      ->varchar('salt')->notNull()
      ->tinyint('active')->notNull()->default('1')
      ->varchar('token')->null()
      ->tinyint('logged_in')->null()->default('0')
      ->varchar('location')->null()
      ->tinyint('expires')->default('0')
      ->date('expiration_date')->null()
      ->timestamps()
      ->pk('id')
      ->fk('id_role', 'roles', 'id')
      ->execute();
  }

  public function uninstall(Table $table)
  {
    $table->forceDrop(0);
    $table->drop('eav');
    $table->drop('users');
    $table->drop('roles');
    $table->drop('roles_types');
    $table->forceDrop(1);
  }

  public function data()
  {
    $this->getCsvData('roles_types', function ($row) {
      $query = $this->db->query();
      $row['created_at'] = Date::getDateTime();
      $query
        ->insert($row)
        ->into('roles_types')
        ->execute();
    });

    $this->getCsvData('roles', function ($row) {
      $query = $this->db->query();
      $row['created_at'] = Date::getDateTime();
      $query
        ->insert($row)
        ->into('roles')
        ->execute();
    });

  }

  public function update_1()
  {
    print_r([
      __FUNCTION__,
    ]);
  }
}
