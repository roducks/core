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

namespace Roducks\Lib\Utils;

abstract class Date {
  private static function _get($format, $date = NULL)
  {
    return (empty($date)) ? date($format) : date($format, strtotime($date));
  }

  private static function _move($sign, $type, $days, $date)
  {
    $d = (empty($date)) ? self::getDate() : $date;

    return self::getDate("{$d} {$sign}{$days} {$type}");
  }

  private static function _add($type, $days, $date)
  {
    return self::_move('+', $type, $days, $date);
  }

  private static function _substract($type, $days, $date)
  {
    return self::_move('-', $type, $days, $date);
  }

  public static function getDate($date = NULL)
  {
    return self::_get('Y-m-d', $date);
  }

  public static function getTime($date = NULL)
  {
    return self::_get('H:i:s', $date);
  }

  public static function getDateTime()
  {
    return implode(' ', [self::getDate(), self::getTime()]);
  }

  public static function getTimeStamp()
  {
    return time();
  }

  public static function getYear($date = NULL)
  {
    return self::_get('Y', $date);
  }

  public static function getMonth($date = NULL)
  {
    return self::_get('m', $date);
  }

  public static function getDay($date = NULL)
  {
    return self::_get('d', $date);
  }

  public static function getHour($date = NULL)
  {
    return self::_get('H', $date);
  }

  public static function getMinute($date = NULL)
  {
    return self::_get('i', $date);
  }

  public static function getSecond($date = NULL)
  {
    return self::_get('s', $date);
  }

  public static function addYears($years = 1, $date = NULL)
  {
    return self::_add('year', $years, $date);
  }

  public static function substractYears($years = 1, $date = NULL)
  {
    return self::_substract('year', $years, $date);
  }

  public static function addMonths($months = 1, $date = NULL)
  {
    return self::_add('month', $months, $date);
  }

  public static function substractMonths($months = 1, $date = NULL)
  {
    return self::_substract('month', $months, $date);
  }

  public static function addDays($days = 1, $date = NULL)
  {
    return self::_add('day', $days, $date);
  }

  public static function substractDays($days = 1, $date = NULL)
  {
    return self::_add('day', $days, $date);
  }

  private static function _isDay($n, $date = NULL)
  {
    return self::_get('N', $date) == $n;
  }

  public static function isMonday($date = NULL)
  {
    return self::_isDay(1, $date);
  }

  public static function isTuesday($date = NULL)
  {
    return self::_isDay(2, $date);
  }

  public static function isWednesday($date = NULL)
  {
    return self::_isDay(3, $date);
  }

  public static function isThursday($date = NULL)
  {
    return self::_isDay(4, $date);
  }

  public static function isFriday($date = NULL)
  {
    return self::_isDay(5, $date);
  }

  public static function isSaturday($date = NULL)
  {
    return self::_isDay(6, $date);
  }

  public static function isSunday($date = NULL)
  {
    return self::_isDay(7, $date);
  }

  public static function isWeekend($date = NULL)
  {
    return in_array(self::_get('N', $date), [6, 7]);
  }

  public static function isWeekday($date = NULL)
  {
    return self::_get('N', $date) < 6;
  }

  public static function getLastDayOfMonth($date = NULL)
  {
    $d = self::getDate($date);
    $year = self::getYear($d);
    $month = self::getMonth($d);
    $fdate = implode('-', [$year, $month, '01']);

    return self::_get('t', $fdate);
  }

  /**
   * @param $from datetime -> 2021-09-08 18:18:09
   * @param $to datetime -> 2022-10-12 19:20:24
   */
  public static function getDiff($from, $to)
  {
    $datetime1 = date_create($from);
    $datetime2 = date_create($to);
    $interval = date_diff($datetime1, $datetime2);

    $replace = [' ', ':', '-'];
    $pd1 = str_replace($replace, '', $from);
    $pd2 = str_replace($replace, '', $to);

    $years = $interval->format('%y');
    $months = $interval->format('%m');
    $days = $interval->format('%d');
    $hours = $interval->format('%h');
    $minutes = $interval->format('%i');
    $seconds = $interval->format('%s');

    if (intval($pd1) > intval($pd2)) {
      $years = 0;
      $months = 0;
      $days = 0;
      $hours = 0;
      $minutes = 0;
      $seconds = 0;
    }

    return [
      'years' => $years,
      'months' => $months,
      'days' => $days,
      'hours' => $hours,
      'minutes' => $minutes,
      'seconds' => $seconds,
    ];
  }
}
