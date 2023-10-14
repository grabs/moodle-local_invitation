<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_invitation\helper;

/**
 * A helper class for time calculations.
 *
 * @package    local_invitation
 * @author     Andreas Grabs <info@grabs-edv.de>
 * @copyright  2020 Andreas Grabs EDV-Beratung
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class date_time {
    /** A minute in seconds */
    public const MINUTE = 60; // For easier usage of a 1 minute time range.
    /** An hour in seconds */
    public const HOUR = self::MINUTE * 60; // For easier usage of a 1 hour time range.
    /** A day in seconds */
    public const DAY = self::HOUR * 24; // For easier usage of a 24 hour time range.

    /**
     * Get the month from the given timestamp.
     *
     * @param  int $timestamp
     * @return int A number between 1 and 12
     */
    public static function get_month(int $timestamp): int {
        $month = date('n', $timestamp);

        return (int) $month;
    }

    /**
     * Get the Year from the given timestamp.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_year(int $timestamp): int {
        $year = date('Y', $timestamp);

        return (int) $year;
    }

    /**
     * Get a valid timestamp from parts of strings related to a date.
     *
     * @param  string $year
     * @param  string $month
     * @param  string $day
     * @param  string $hour
     * @param  string $minute
     * @return int    The timestamp
     */
    public static function get_stamp_from_parts($year, $month, $day, $hour = '0', $minute = '0') {
        return strtotime($year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute);
    }

    /**
     * Get the first day of the week the timestamp is in.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_first_day_of_week(int $timestamp): int {
        // First check the weekday of timestart.
        // We want to start on the Monday of the current week.
        $weekday = (int) date('N', $timestamp); // The function date('N'...) gets 1 to 7 for each day of week.
        // Floor the timestamp to the beginning of the day (0:00).
        $startday = self::floor_to_day($timestamp);
        // To get the first day of this week (starting by Monday) we have to go back in days by the number of weekday.
        $startday -= (($weekday - 1) * self::DAY);

        return $startday;
    }

    /**
     * Get the last day of the week the timestamp is in.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_last_day_of_week(int $timestamp): int {
        // First check the weekday of timestart.
        // We want to end on the Sunday of the current week.
        $weekday = (int) date('N', $timestamp); // The function date('N'...) gets 1 to 7 for each day of week.
        // Floor the timestamp to the beginning of the day (0:00).
        $endday = self::floor_to_day($timestamp);
        // To get the last day of this week (ending on Sunday) we have to go forward in days by the difference of 7 and weekday.
        $endday += ((7 - $weekday) * self::DAY);

        return $endday;
    }

    /**
     * Get the first day of a month the timestamp is in.
     *
     * @param  int $stamp
     * @return int
     */
    public static function get_first_day_of_month(int $stamp): int {
        // Split the date into the needed parts month and year.
        $month = date('n', $stamp);
        $year  = date('Y', $stamp);

        // The first day is just the 1. of the month.
        return strtotime($year . '-' . $month . '-1');
    }

    /**
     * Get the first day of the month before the given timestamp.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_first_day_of_prev_month(int $timestamp): int {
        $firtdayofmonth = self::get_first_day_of_month($timestamp); // Get the first day of the given month.
        $prevmonthdate  = $firtdayofmonth - (2 * self::DAY); // Decrease the date by two days so we are in the month before.

        return self::get_first_day_of_month($prevmonthdate);
    }

    /**
     * Get the first day of the next month after the given timestamp.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_first_day_of_next_month(int $timestamp): int {
        $lastdayofmonth = self::get_last_day_of_month($timestamp); // Get the first day of the given month.
        $nextmonthdate  = $lastdayofmonth + (2 * self::DAY); // Decrease the date by two days so we are in the month before.

        return self::get_first_day_of_month($nextmonthdate);
    }

    /**
     * Get the last day of a month the timestamp is in.
     *
     * @param  int $stamp
     * @return int
     */
    public static function get_last_day_of_month(int $stamp): int {
        // Split the date into the needed parts month and year.
        $month = (int) date('n', $stamp);
        $year  = (int) date('Y', $stamp);

        // On all months except the december we can just get the first day of the next month decreased by one day.
        // Example: If we want the last day in February we take the first march (01.03) and reduce it by one day.
        // This asures we have the right day.
        // If the month is 12 the last day is the 31.12.
        if ($month < 12) {
            return strtotime($year . '-' . ($month + 1) . '-1') - self::DAY;
        }   // In this case we have december (12).

        return strtotime($year . '-12-31');
    }

    /**
     * Get the last day of the month before the given timestamp.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_last_day_of_prev_month(int $timestamp): int {
        $firtdayofmonth = self::get_first_day_of_month($timestamp); // Get the first day of the given month.
        $prevmonthdate  = $firtdayofmonth - (2 * self::DAY); // Decrease the date by two days so we are in the month before.

        return self::get_last_day_of_month($prevmonthdate);
    }

    /**
     * Get the last day of the next month after the given timestamp.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_last_day_of_next_month(int $timestamp): int {
        $lastdayofmonth = self::get_last_day_of_month($timestamp); // Get the first day of the given month.
        $nextmonthdate  = $lastdayofmonth + (2 * self::DAY); // Decrease the date by two days so we are in the month before.

        return self::get_last_day_of_month($nextmonthdate);
    }

    /**
     * Get the next day of a given date at 0:00.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_next_day(int $timestamp): int {
        $nextday = $timestamp + self::DAY;

        return self::floor_to_day($nextday);
    }

    /**
     * Get the numeric interpretation of a weekday in the range of 1 to 7.
     * The value 1 means "Monday" and 7 means "Sunday".
     *
     * @param  int $timestamp
     * @return int
     */
    public static function get_weekday(int $timestamp): int {
        return (int) date('N', $timestamp); // Returns 1 to 7.
    }

    /**
     * Get the timestamp of a day starting at 0:00 a.m.
     *
     * @param  int $timestamp
     * @return int
     */
    public static function floor_to_day(int $timestamp): int {
        return strtotime(date('Y-m-d', $timestamp));
    }

    /**
     * Check if a given timestamp is at a weekend.
     *
     * @param  int  $timestamp
     * @return bool
     */
    public static function day_is_weekend(int $timestamp): bool {
        $weekday = date('N', $timestamp); // The function date('N'...) gets 1 to 7 for each day of week.
        if ($weekday == '6' || $weekday == '7') {
            return true;
        }

        return false;
    }
}
