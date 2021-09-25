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

/**
 * @example
 */
/*
  $csv = Csv::init("path/to/file");

  # Headers
  $csv->headers([
    "id",
    "title",
    "desc"
  ]);

  # Rows
  for($i = 1; $i<=10; $i++) {
    $csv->row([
      $i,
      "Lorem Ipsum",
      "This is an example"
    ]);
  }

  # Save & Download
  $csv->save();
  $csv->download();
*/

namespace Roducks\Lib\Output;

class Csv {
  private $_delimiter = ',';
  private $_handle;
  private $_name;
  private $_file;
  private $_rows = '';

  private function _escape($fields)
  {
    $fill = [];

    foreach ($fields as $f) {
      $fill[] = '"' . utf8_encode($f) . '"';
    }

    return implode($this->_delimiter, $fill);
  }

  private static function _ext($str)
  {
    $ext = ".csv";
    if (!preg_match('/\.csv$/', $str)) return $str . $ext;

    return $str;
  }

  static function init($name)
  {
    return new static($name);
  }

  public function __construct($name)
  {
    $this->_file = self::_ext($name);
    $this->_name = preg_replace('/^.+\/([a-zA-Z0-9\-_]+\.csv)$/', '$1', $this->_file);
  }

  public function row(array $rows = [])
  {
    $raw = '';

    if (is_array($rows) && count($rows) > 0) {
      $raw .= $this->_escape($rows);
    }

    $this->_rows .= $raw . "\n";
  }

  public function headers($obj)
  {
    $this->_rows .= $this->row($obj);
  }

  public function save()
  {
    $csv_file = fopen($this->_file, "w");
                fwrite($csv_file, $this->_rows);
                fclose($csv_file);
  }

  public function download($name = NULL)
  {
    $name = (empty($name)) ? $this->_name : $name;
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="'. $name .'.csv"');
    echo $this->_rows;
  }

  /*
    $csv = Csv::init("path/to/file.csv");

    if ($csv->read()) {
      while (($row = $csv->fetch()) !== FALSE) {
        Helper::pre($row);
      }

      $csv->stop();
    }
  */
  public function read()
  {
    if (file_exists($this->_file)) {
      if (($this->_handle = fopen($this->_file, "r")) !== FALSE) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
  *   Get row
  *   @return object
  */
  public function fetch()
  {
    return fgetcsv($this->_handle);
  }

  /**
  *   Close the file
  */
  public function stop()
  {
    fclose($this->_handle);
  }

  /**
   * It returns a mapped row 
   * 
   * @example:
   * 
   * $this->getData(function ($row) {
   *   print_r($row);
   * });
   */
  public function getData(callable $callback)
  {
    $headers = [];
    $index = 0;

    if ($this->read()) {
      while (($row = $this->fetch()) !== FALSE) {
        if ($index == 0) {
          $headers = $row;
        }
        else {
          $content[] = $row;
          $values = [];
  
          for ($i=0; $i < count($headers); $i++) { 
            $values[$headers[$i]] = $row[$i];
          }
  
          $callback($values);
        }

        $index++;
      }

      $this->stop();
    }
  }

}
