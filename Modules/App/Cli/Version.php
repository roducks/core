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

use Roducks\Framework\App;
use Roducks\Lib\Output\Cli;

class Version extends Cli {
  protected function header()
  {
    $this->output([
      $this->colorLightBlue("   ____          __          _  __ __"),
      $this->colorLightBlue('  /  _ \___  ___/ /_  __,___| |/ // _\\'),
      $this->colorLightBlue(' /  .__/ _ \/ _  / /_/ / __/| | /_\ \\'),
      $this->colorLightBlue('/_/\_\ \___/\_,_/\____/\___\|_|\_\__/'),
      '',
    ], TRUE);
  }

  public function run($cmd)
  {
    $this->header();
    $this->output([
      $this->bgLightBlue('Version:'),
      $this->colorYellow(App::VERSION),
    ]);
  }
}
