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

class Status extends Version {
  public function run($cmd)
  {
    $exts = [
      'date',
      'curl',
      'gd',
      'json',
      'mbstring',
      'mysqlnd',
      'mysqli',
      'PDO',
      'pdo_mysql',
      'libxml',
      'SimpleXML',
      'xml',
      'xmlreader',
      'xmlrpc',
      'xmlwriter',
      'zip',
    ];
    $report = [];
    $report[] = $this->colorLightBlue('PHP Extensions:');

    $requiredVersion = '7.0.0';
    $phpVersion = PHP_VERSION;
    $compare = (version_compare($phpVersion, $requiredVersion) <= 0);
		$alert = $compare ? "Roducks requires version {$requiredVersion} or later." : "PHP {$phpVersion} version looks fine to run Roducks!";
    $dialog = $compare ? 'failure' : 'notice';
    $message = [];

    if ($compare) {
      $message[] = "PHP version: {$phpVersion}";
    }

    $message[] = $alert;

    foreach ($exts as $ext) {
      $status = extension_loaded($ext) ? 'ok' : 'wrong';
      $report[] = $this->$status($ext);
    }

    $this->header();
    $this->$dialog('Status', $message);
    $this->output($report, TRUE);
  }
}
