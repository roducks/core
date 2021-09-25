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
	$file = File::manager();
	$file
		->type([File::TYPE_JPG',File::TYPE_PNG]);
		->kb(150);
		->path(Path::getData())
		->input($input)
		->name('profile')
		->upload();

	if ($file->success()) {
		echo $file->getName();
	}
	else {
		echo $file->getMessage();
	}
*/

namespace Roducks\Lib\Files;

class File {
	const TYPE_PLAIN = 'text/plain';
	const TYPE_CSV = 'text/csv';
	const TYPE_GIF = 'image/gif';
	const TYPE_JPG = 'image/jpeg';
	const TYPE_PNG = 'image/png';
	const TYPE_MP3 = 'audio/mp3';
	const TYPE_MPEG = 'video/mpeg';
	const TYPE_QUICKTIME = 'video/quicktime';
	const TYPE_ZIP = 'application/zip';
	const TYPE_PDF = 'application/pdf';
	const TYPE_XML = 'application/xml';
	const TYPE_JSON = 'application/json';
	const TYPE_OTHER = 'application/octet-stream';
	const TYPE_XLS = 'application/vnd.ms-excel';
	const TYPE_PPT = 'application/vnd.ms-powerpoint';
	const TYPE_WORD = 'application/msword';
	const TYPE_TAR = 'application/x-tar';
	const TYPE_RAR_COMPROSSED = 'application/x-rar-compressed';
	const REGEXP = '/^(.+)(\.\w{3,4})$/';

	private $_limit = 1024; // 1 MB by default
	private $_success = FALSE;
	private $_message = "Ok.";
	private $_name = NULL;
	private $_rename = NULL;
	private $_path = NULL;
	private $_input = NULL;
	private $_ext = [];

	public static function getExt($filename)
	{
		return preg_replace(static::REGEXP, '$2', $filename);
	}

	public static function removeExt($filename)
	{
		return preg_replace(static::REGEXP, '$1', $filename);
	}

  public static function exists($file)
  {
    return file_exists($file);
  }

  public static function getContent($file, $exists = FALSE)
  {
    return (self::exists($file) || $exists) ? file_get_contents($file) : [];
  }

  public static function putContent($file, $value)
  {
    file_put_contents($file, $value);
  }

  public static function remove($file)
  {
    if (self::exists($file)) {
      unlink($file);
    }
  }

  public static function manager()
  {
    return new static();
  }

	private function _getAttribute($file, $attr)
	{
		return $_FILES[$file][$attr] ?? NULL;
	}

	private function _getSize($f)
	{
		return ceil($this->_getAttribute($f, 'size') / 1024);
	}

	private function _setSize($n)
	{
		$this->_limit = $n;
	}

	public function type($arr)
	{
		$this->_ext = $arr;
		return $this;
	}

	public function kb($n)
	{
		$this->_setSize($n);
		return $this;
	}

	public function mb($n)
	{
		$cal = ceil($n * 1024);
		$this->_setSize($cal);
		return $this;
	}

	public function success()
	{
		return $this->_success;
	}

	public function error()
	{
		return !$this->_success;
	}

	public function getMessage()
	{
		return $this->_message;
	}

	public function getCode()
	{
		return $this->_code;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getTmp()
	{
		return $this->_getAttribute($this->_input, 'tmp_name');
	}

	public function getTmpContent()
	{
		return file_get_contents($this->getTmp());
	}

	public function path($dir)
	{
		$this->_path = $dir;
		return $this;
	}

	public function input($name)
	{
		$this->_input = $name;
		return $this;
	}

	public function name($name)
	{
		$this->_rename = $name;
		return $this;
	}

	public function upload()
	{
		$this->_rename = $this->_name;

	  if (empty($this->_rename)) {
	    $this->_rename = 'file_' . time();
	  }

	  $this->_name = $this->_getAttribute($this->_input, 'name');
	  // if upload is successed
	  if (!empty($this->_name) && $this->_getAttribute($this->_input, 'error') == 0) {

	    // Allowed size
	    if ($this->_getSize($this->_input) <= $this->_limit) {

	      // Allowed type
	      if (in_array($this->_getAttribute($this->_input, 'type'), $this->_ext) || empty($this->_ext)) {
	        $this->_name = $this->_rename . self::getExt($this->_name);

	        if (move_uploaded_file($this->_getAttribute($this->_input, 'tmp_name'), $this->_path . $this->_name)) {
	          $this->_success = TRUE;
	          $this->_message = "File was uploaded successfully.";
	          $this->_code = 1;
	        }
					else {
	          $this->_message = "File couln't be moved to destination.";
	          $this->_code = 5;
	        }
	      }
				else {
	        $this->_message = "Type: " . $this->_getAttribute($this->_input, 'type') . " is not allowed.";
	        $this->_code = 2;
	      }
	    }
			else {
	      $this->_message = "File size is too heavy: " . $this->_getSize($this->_input) . " KB.";
	      $this->_code = 3;
	    }
	  }
		else {
	    $this->_message = "There was an error:  #" . $this->_getAttribute($this->_input, 'error');
	    $this->_code = 4;
	  }

	  return $this;
	}

	public function update()
	{
		$filename = $this->_getAttribute($this->_input, 'name');
		$copy = $_POST[$this->_input . '_copy'];

		if (!empty($filename)) {
			if (empty($copy)) {
				$this->upload();
			} else {
				if ($copy != $filename) {
					$this->upload();
					self::remove($this->_path . $copy);
				}
			}
		}
	}

}
