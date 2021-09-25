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

abstract class Html {

  private static function _headElement($fx, $content, $key, $value, array $attrs)
  {
    $def = [];
    $def[$key] = $value;

    unset($attrs[$key]);

    return self::tag($fx, $content, $def + $attrs);
  }

  public static function getAttrs(array $attrs)
  {
    $data = [];

    if (empty($attrs)) {
      return '';
    }

    foreach ($attrs as $key => $value) {
      $data[] = $key . '=' . '"' . $value . '"';
    }

    return ' ' . implode(' ', $data);
  }

  public static function tag($name, $content = NULL, array $attrs = [])
  {
    $attrs = self::getAttrs($attrs);
    $tag = "<{$name}{$attrs}";
    $tag .= (!is_null($content)) ? ">{$content}</{$name}>" : ' />';

    return $tag;
  }

  public static function meta(array $attrs)
  {
    return self::tag(__FUNCTION__, NULL, $attrs);
  }

  public static function link($href, array $attrs)
  {
    return self::_headElement(__FUNCTION__, NULL, 'href', $href, $attrs);
  }

  public static function script($src, array $attrs)
  {
    return self::_headElement(__FUNCTION__, '', 'src', $src, $attrs);
  }

  public static function scriptInline($content)
  {
    return self::tag('script', $content, ['type' => 'text/javascript']);
  }
}
