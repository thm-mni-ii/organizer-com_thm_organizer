<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use DateTime;
use Joomla\CMS\Factory;

/**
 * Class provides generalized functions regarding dates and times.
 */
class Dates
{
	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param   string  $date      the date to be formatted
	 * @param   bool    $withText  if the day name should be part of the output
	 * @param   bool    $short     if the day name output should be abbreviated
	 *
	 * @return string|bool  a formatted date string otherwise false
	 */
	public static function formatDate($date = '', $withText = false, $short = false)
	{
		$date          = empty($date) ? date('Y-m-d') : $date;
		$formattedDate = date(self::getFormat(), strtotime($date));

		if ($withText)
		{
			$textFormat    = $short ? 'D' : 'l';
			$shortDOW      = date($textFormat, strtotime($date));
			$text          = Languages::_(strtoupper($shortDOW));
			$formattedDate = "$text $formattedDate";
		}

		return $formattedDate;
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param   string  $time  the date to be formatted
	 *
	 * @return string|bool  a formatted date string otherwise false
	 */
	public static function formatTime($time)
	{
		$timeFormat = Input::getParams()->get('timeFormat', 'H:i');

		return date($timeFormat, strtotime($time));
	}

	/**
	 * Formats the date stored in the database according to the format in the component parameters
	 *
	 * @param   string  $startDate  the start date of the resource
	 * @param   string  $endDate    the end date of the resource
	 * @param   bool    $withText   if the day name should be part of the output
	 * @param   bool    $short      if the day name output should be abbreviated
	 *
	 * @return string|bool  a formatted date string otherwise false
	 */
	public static function getDisplay($startDate, $endDate, $withText = false, $short = false)
	{
		$startDate = self::formatDate($startDate, $withText, $short);
		$endDate   = self::formatDate($endDate, $withText, $short);

		return $startDate === $endDate ? $startDate : "$startDate - $endDate";
	}

	/**
	 * Gets the format from the component settings
	 *
	 * @return string the date format
	 */
	public static function getFormat()
	{
		return Input::getParams()->get('dateFormat', 'd.m.Y');
	}

	/**
	 * Returns the end date and start date of the ICS for the given date
	 *
	 * @param   string  $date      the date
	 * @param   int     $startDay  0-6 number of the starting day of the week
	 * @param   int     $endDay    0-6 number of the ending day of the week
	 *
	 * @return array containing startDate and endDate
	 */
	public static function getICSDates($date, $startDay = 1, $endDay = 6)
	{
		$dateTime     = strtotime($date);
		$startDayName = date('l', strtotime("Sunday + $startDay days"));
		$endDayName   = date('l', strtotime("Sunday + $endDay days"));
		$startDate    = date('Y-m-d', strtotime("$startDayName this week", $dateTime));
		$previewEnd   = date('Y-m-d', strtotime('+6 month', strtotime($date)));
		$endDate      = date('Y-m-d', strtotime("$endDayName this week", strtotime($previewEnd)));

		return ['startDate' => $startDate, 'endDate' => $endDate];
	}

	/**
	 * Returns the end date and start date of the month for the given date
	 *
	 * @param   string  $date      the date
	 * @param   int     $startDay  0-6 number of the starting day of the week
	 * @param   int     $endDay    0-6 number of the ending day of the week
	 *
	 * @return array containing startDate and endDate
	 */
	public static function getMonth($date, $startDay = 1, $endDay = 6)
	{
		$dateTime     = strtotime($date);
		$startDayName = date('l', strtotime("Sunday + $startDay days"));
		$endDayName   = date('l', strtotime("Sunday + $endDay days"));
		$monthStart   = date('Y-m-d', strtotime('first day of this month', $dateTime));
		$startDate    = date('Y-m-d', strtotime("$startDayName this week", strtotime($monthStart)));
		$monthEnd     = date('Y-m-d', strtotime('last day of this month', $dateTime));
		$endDate      = date('Y-m-d', strtotime("$endDayName this week", strtotime($monthEnd)));

		return ['startDate' => $startDate, 'endDate' => $endDate];
	}

	/**
	 * Returns the end date and start date of the semester for the given date
	 *
	 * @param   string  $date  the date in format Y-m-d
	 *
	 * @return array containing startDate and endDate
	 */
	public static function getSemester($date)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('startDate, endDate')
			->from('#__thm_organizer_terms')
			->where("'$date' BETWEEN startDate AND endDate");
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssoc', []);
	}

	/**
	 * Returns the end date and start date of the week for the given date
	 *
	 * @param   string  $date      the date
	 * @param   int     $startDay  0-6 number of the starting day of the week
	 * @param   int     $endDay    0-6 number of the ending day of the week
	 *
	 * @return array containing startDate and endDate
	 */
	public static function getWeek($date, $startDay = 1, $endDay = 6)
	{
		$dateTime     = strtotime($date);
		$startDayName = date('l', strtotime("Sunday + $startDay days"));
		$endDayName   = date('l', strtotime("Sunday + $endDay days"));
		$startDate    = date('Y-m-d', strtotime("$startDayName this week", $dateTime));
		$endDate      = date('Y-m-d', strtotime("$endDayName this week", $dateTime));

		return ['startDate' => $startDate, 'endDate' => $endDate];
	}

	/**
	 * Checks whether a date is a valid date in the standard Y-m-d format.
	 *
	 * @param   string  $date  the date to be checked
	 *
	 * @return bool
	 */
	public static function isStandardized($date)
	{
		$dt = DateTime::createFromFormat('Y-m-d', $date);

		return ($dt !== false and !array_sum($dt->getLastErrors()));
	}

	/**
	 * Converts a date string from the format in the component settings into the format used by the database
	 *
	 * @param   string  $date  the date string
	 *
	 * @return string  date sting in format Y-m-d
	 */
	public static function standardizeDate($date = '')
	{
		$default = date('Y-m-d');

		if (empty($date))
		{
			return $default;
		}

		if (self::isStandardized($date))
		{
			return $date;
		}

		$dt = DateTime::createFromFormat(self::getFormat(), $date);

		return ($dt !== false and !array_sum($dt->getLastErrors())) ? $dt->format('Y-m-d') : $default;
	}
}
