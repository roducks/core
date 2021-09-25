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

namespace Roducks\Modules\Setup\Cli;

use Roducks\Modules\App\Cli\Module;

class Setup extends Module {

  public function run($cmd)
  {
    if (!$this->storage->existsYml('install')) {
      $this->install('setup');
      $this->install('users');
      $this->install('app');
      $this->install('login');
      $this->install('home');
    }
    else {
      $this->success('Notice', [
        'Setup is up and running!',
      ]);
    }
  }

  public function reset()
  {
    if ($this->storage->existsYml('install')) {
      $this->uninstall('users');
      $this->uninstall('setup');
    }
    else {
      $this->failure('Error', [
        'Setup module is not installed.',
        '',
        'Run:',
        '',
        'php roducks setup',
      ]);
    }
  }

}
