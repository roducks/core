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

namespace Roducks\Lib\Output;

use Roducks\Routing\Path;
use Roducks\Framework\App;
use Roducks\Page\Duckling;
use Roducks\Lib\Request\Http;
use Roducks\Routing\Dispatcher;

abstract class Error {

  public static function log($message, $file = NULL)
	{
		$code = (!empty($file)) ? 3 : 0;
		error_log($message, $code, $file);
	}

	public static function display($show)
	{
    $report = 0;
    $display = 0;

    if ($show) {
      $report = E_ALL;
      $display = 1;
    }

		error_reporting($report);
		ini_set("display_errors", $display);
	}

  public static function debug(array $messages)
  {
    if (php_sapi_name() == 'cli') {
      if (App::errors()) {
        Cli::error($messages);
      }
      else {
        Cli::error(['Invalid command -> ' . App::getCmd()]);
      }
    }
    else {
      if (App::errors()) {
        self::log(implode("\n", $messages));
        Dispatcher::debugger($messages);
      }
      else {
        Dispatcher::pageNotFound();
      }
    }
  }

  public static function message($path, array $data, $die = TRUE, $return = FALSE)
  {
    try {
      $html = Duckling::render($path, $data);

      if ($return) {
        return $html;
      }
      else {
        echo $html;
      }

      if ($die) {
        exit;
      }
    } catch (\Exception $e) {
      Http::sendHeaderNotFound();
    }
  }

  public static function alert(array $data, $return = FALSE)
  {
    $message = '';

    if (App::errors()) {
      $path = Path::getCoreTemplates('alert' . Path::HTML_EXT);
      $message = self::message($path, $data, FALSE, $return);
    }

    if ($return) {
      return $message;
    }
  }

  public static function fatal(array $messages)
  {
    $data = [
      'title' => "Error!",
      'messages' => [
        "sample",
      ],
    ];

    if (App::errors()) {
      self::log(implode("\n", $messages));
      $data['messages'] = $messages;
    }

    $path = Path::getCoreTemplates('error' . Path::HTML_EXT);
    self::message($path, $data);
  }
}
