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

use Roducks\Page\Frame;

abstract class Cli extends Frame {
	const SUCCESS = 1;
	const FAILURE = 2;
	const WARNING = 3;
	const NOTICE = 4;
	const LN = "\n";

	private $_input = NULL;

	private function _prompt($text, $stty = FALSE)
	{
		$this->_output([
			$this->colorYellow($text)
		]);
		
		if ($stty) {
			system('stty -echo');
		}

		$this->_input = trim( fgets( STDIN ));
	
		if ($stty) {
			system('stty echo');
		}
	}

	protected function promptYn($text)
	{
		$this->_prompt("{$text} [Y/n]: ");
		$input = $this->_input;
		$this->_input = strtoupper($input);

		if (!in_array($this->_input, ['Y','YES','N','NO'])) {
			$this->failure("Error", [
				"Invalid Option -> {$input}"
			]);
		}
	}

	protected function setInput($text, $output = FALSE)
	{
		$this->_prompt("{$text}: ");
		
		if ($output) {
			$this->output([
				$this->_input,
			], TRUE);
		}

		return $this->_input;
	}

	protected function setPassword($text)
	{
		$this->_prompt("{$text}: ", TRUE);
		$this->output([
			'',
		], TRUE);

		return $this->_input;
	}

	private function _setOptions($display, $title, array $values, $withKey = FALSE)
	{
		$data = [];
		$options = [];
		$labels = [];
		$index = 0;

		if ($display) {
			$this->output([
				$this->colorYellow("{$title}: "),
			]);

			foreach ($values as $key => $value)
			{
				$index++;
				$options[$index] = $withKey ? $key : $value;
				$labels[$index] = $value;
				$option = $this->colorLightBlue($index, '');
				$text = $this->colorLightBlue($value);
				$data[] = "  [{$option}]{$text}";
			}

			$this->output($data, TRUE);
		}
		else {
			$options = $values['options'];
			$labels = $values['labels'];
		}

		$this->setInput(' Type [?] option');

		$input = $options[$this->_input] ?? NULL;
		$label = $labels[$this->_input] ?? NULL;

		if (!empty($input)) {
			$this->output([
				$this->colorLightBlue($label),
			]);
		}
		else {
			$this->failure('Error', [
				"Invalid option -> {$this->_input}"
			]);

			return $this->_setOptions(FALSE, $title, ['options' => $options, 'labels' => $labels], $withKey);
		}

		return $input;
	}

	protected function setOptions($title, $values, $withKey = FALSE)
	{
		return $this->_setOptions(TRUE, $title, $values, $withKey);
	}

	protected function yes()
	{
		return in_array($this->_input, ['Y','YES']);
	}

	protected function no()
	{
		return in_array($this->_input, ['N','NO']);
	}

	private static function _getBg($status)
	{
		$out = "";

		switch($status) {
			case self::SUCCESS:
				$out = "[42m"; // Green background
				break;
			case self::FAILURE:
				$out = "[41m"; // Red background
				break;
			case self::WARNING:
				$out = "[43m"; // Yellow background
				break;
			case self::NOTICE:
				$out = "[44m"; // Blue background
				break;
		}

		return $out;
	}

	private static function _colorize($text, $status)
	{
		$out = self::_getBg($status);
		return chr(27) . "[0;37m\033{$out}{$text}" . chr(27);
	}

	private static function _dialog($title, $lines, $color)
	{
		$out = self::_getBg($color);
		$dialog = 55;
		$edge = 2;
		$border = $edge * 2;
		$margin = str_repeat(' ', $edge);
		array_unshift($lines, $title);
		$values = array_map(function($v) {
			return strlen($v);
		}, $lines);

		$max = max($values);

		if ($max > $dialog) {
			$dialog = $max + $border;
		}

		$space = str_repeat(' ', $dialog);

		echo self::_colorize($space, $color) . self::LN;

		foreach ($lines as $i => $line) {
			$length = strlen($line);
			$padding = $dialog - $border - $length;
			$fill = str_repeat(' ', $padding);
			echo self::_colorize($margin . "\033[0;37m\033{$out}{$line}" . $fill . $margin, $color) . self::LN;

			if ($i == 0) {
				echo self::_colorize($space, $color) . self::LN;
			}
		}

		echo self::_colorize($space, $color) . self::LN;

		echo "\033[0m" . self::LN;
	}

	protected function success($title, array $lines)
	{
		self::_dialog("[{$title}]:", $lines, self::SUCCESS);
	}

	protected function warning($title, array $lines)
	{
		self::_dialog("[{$title}]:", $lines, self::WARNING);
	}

	protected function notice($title, array $lines)
	{
		self::_dialog("[{$title}]:", $lines, self::NOTICE);
	}

	private static function _failure($title, array $lines)
	{
		self::_dialog("[{$title}]:", $lines, self::FAILURE);
	}

	protected function failure($title, array $lines)
	{
		self::_failure($title, $lines);
	}

	public static function error(array $messages)
	{
		self::_failure('Error', $messages);
		exit;
	}

	private static function _color($text, $code, $space = ' ')
	{
		return "\033[{$code}{$space}{$text}\033[0m";
	}

	private static function _bg($text, $bg)
	{
		return self::_color(" {$text} ", '0;37m', "\033[{$bg}");
	}

	protected function colorRed($text, $space = ' ')
	{
		return self::_color($text, "0;31m", $space);
	}

	protected function colorGreen($text, $space = ' ')
	{
		return self::_color($text, "0;32m", $space);
	}

	protected function colorYellow($text, $space = ' ')
	{
		return self::_color($text, "0;33m", $space);
	}

	protected function colorBlue($text, $space = ' ')
	{
		return self::_color($text, "0;34m", $space);
	}

	protected function colorPurple($text, $space = ' ')
	{
		return self::_color($text, "0;35m", $space);
	}

	protected function colorLightBlue($text, $space = ' ')
	{
		return self::_color($text, "0;36m", $space);
	}

	protected function bgRed($text)
	{
		return self::_bg($text, '41m');
	}

	protected function bgGreen($text)
	{
		return self::_bg($text, '42m');
	}

	protected function bgYellow($text)
	{
		return self::_bg($text, '43m');
	}

	protected function bgBlue($text)
	{
		return self::_bg($text, '44m');
	}

	protected function bgPurple($text)
	{
		return self::_bg($text, '45m');
	}

	protected function bgLightBlue($text)
	{
		return self::_bg($text, '46m');
	}

	protected function warn($text)
	{
		return $this->bgYellow('WARN') . " {$text}";
	}

	protected function ok($text)
	{
		return $this->bgGreen('OK') . " {$text}";
	}

	protected function wrong($text)
	{
		return $this->bgRed('ERR!') . " {$text}";
	}

	private function _output(array $lines, $ln = FALSE)
	{
		foreach ($lines as $line) {
			echo $line;
			echo ($ln) ? self::LN : '';
		}
	}

	protected function output(array $lines, $ln = FALSE)
	{
		$this->_output($lines, $ln);

		if (!$ln) {
			echo self::LN;
		}
	}

	public function run($cmd)
	{
    $this->output([
      $this->colorYellow("Command:"),
      $this->colorGreen($cmd),
    ]);
	}

	protected function getFlag($index, $value = NULL)
	{
		return $this->settings[$index] ?? $value;
	}
}
