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
use Roducks\Framework\App;

class Help extends Version {
  public function run($cmd)
  {
    $this->header();
    $this->output([
      $this->colorGreen('Commands:', ''),
    ]);

    $cli = Cache::getCli(App::getSite());
    unset($cli['help']);
    $lists = [];
    $length = [];

    foreach ($cli as $module => $config) {
      $name = $config['name'] ?? '--';
      $index = $config['parent'] ?? $module;
      
      if (!isset($config['parent'])) {
        $lists[$index]['name'] = $this->colorYellow($name);
      }

      $mod = $this->colorLightBlue($module);
      $mod = str_replace('_', '-', $mod);
      $command = "  php roducks{$mod}";
      $commandArgs = "";

      if (isset($config['args'])) {
        $commandArgs = " {$config['args']}";
      }

      if (!isset($config['skip'])) {
        $lists[$index]['commands'][] = [
          'command' => $command . $commandArgs,
          'description' => $config['description'] ?? '',
          'options' => $config['options'] ?? [],
        ];
        $length[] = strlen($command);
      }

      if (isset($config['commands'])) {
        foreach ($config['commands'] as $_cmd => $cnf) {
          $_cmd = str_replace('_', '-', $_cmd);
          $subcmd = $this->colorYellow($_cmd, '');
          $args = $cnf['args'] ?? '';
          $call = "{$command}:{$subcmd} {$args}";
          $lists[$index]['commands'][] = [
            'command' => $call,
            'description' => $cnf['description'] ?? '',
            'options' => $cnf['options'] ?? [],
          ];
          $length[] = strlen($call);
        }
      }
    }

    $max = max($length);

    foreach ($lists as $list) {
      $this->output([
        $list['name']
      ]);

      $commands = array_map(function ($config) use ($max) {
        $lng = strlen($config['command']);
        $space = $max - $lng;
        $extra = preg_match('/:/', $config['command']) ? 5 : -6;
        $options = '';

        if (!empty($config['options'])) {
          $options .= self::LN;
          $options .= $this->colorPurple(' Options:') . self::LN;

          foreach ($config['options'] as $option) {
            $name = "   {$option['name']}";
            $sp = $max - strlen($name) + $extra - 11;
            $options .= $name . str_repeat(' ', $sp) . $option['description'] . self::LN;
          }
        }

        return "{$config['command']}" . str_repeat(' ', $space + $extra) . $config['description'] . $options;
      }, $list['commands']);

      $this->output($commands, TRUE);
      $this->output([
        '',
      ]);
    }

    $this->output([
      $this->colorGreen('Options:', ''),
      ' --site=<site>',
      ' --verbose, [-vvv]',
      '',
    ], TRUE);

  }
}
