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
/*

----------------------------------------
|		ZIP
----------------------------------------
	$filename = "profiles_";
	$tmp = Path::getStorage("zip/");
	$files = [];
	$files[] = [
		'path' => $tmp,
		'folder' => "package/",
		'name' => $filename . ".csv"
	];

	Zip::create($files, $tmp . "new_package.zip");

----------------------------------------
|		UNZIP
----------------------------------------
	$tmp = Path::getStorage("zip/");
	Zip::extract($tmp . "package.zip");

*/

namespace Roducks\Lib\Files;

class Zip {

	/**
	 * Zip folder of files
	 */
	public static function create($path, array $listing = [], $destination = '')
	{
		$overwrite = (file_exists($destination)) ? true : false;
		$valid_files = [];
	
		//if files were passed in...
		if (is_array($listing)) {
			foreach ($listing as $folders => $files) {
				$route = ($folders != '/') ? $folders : '';
				//cycle through each file
				foreach ($files as $file) {
					//make sure the file exists
					$filename = $route . $file;
					$filepath = $path . $filename;

					if (file_exists($filepath)) {
						$valid_files[] = [
							'path' => $filepath,
							'name' => $filename
						];
					}
				}
			}

		}

		//if we have good files...
		if (count($valid_files)) {
			//create the archive
			$zip = new \ZipArchive();

			if ($zip->open($destination, $overwrite ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
				return false;
			}

			//add the files
			foreach ($valid_files as $file) {
				$zip->addFile($file['path'], $file['name']);
			}
			//debug
			//echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

			$zip->close();

			return file_exists($destination);
		} else {
			return false;
		}
	}

	/**
	 * Unzip a folder
	 */
	public static function extract($zipName)
	{
		$new_dir = explode('.', $zipName);
		$zip = new \ZipArchive;
		$res = $zip->open($zipName);

		if ($res === TRUE){
			$zip->extractTo($new_dir[0]);
			$zip->close();
		}

		return $res;
	}
}
