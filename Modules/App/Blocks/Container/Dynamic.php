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

namespace Roducks\Modules\App\Blocks\Container;

use Roducks\Page\View;
use Roducks\Page\Block;
use Roducks\Framework\App;
use Roducks\Framework\Autoload;

class Dynamic extends Block {
  public function index($name)
  {
    $blocks = $this->view->getBlocks($name);

    if (!empty($blocks)) {
      if (App::inLocal()) {
        echo "<!-- @container:start " . View::LN;
        echo " ID: {$name}";
        echo View::LN . "-->" . View::LN;
      }

      foreach ($blocks as $block => $params) {
        echo Autoload::block($block, $params);
      }

      if (App::inLocal()) {
        echo View::LN . "<!-- @container:end -->";
      }
    }
  }
}
