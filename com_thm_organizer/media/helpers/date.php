<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides generalized functions regarding dates and times.
 */
class THM_OrganizerHelperDate
{
    /**
     * Formats the date stored in the database according to the format in the component parameters
     *
     * @param string $date     the date to be formatted
     * @param bool   $withText if the day name should be part of the output
     * @param bool   $short    if the day name output should be abbreviated
     *
     * @return string|bool  a formatted date string otherwise false
     */
    public static function formatDate($date, $withText = false, $short = false)
    {
        $formattedDate = date(self::getFormat(), strtotime($date));

        if ($withText) {
            $textFormat    = $short ? 'D' : 'l';
            $shortDOW      = date($textFormat, strtotime($date));
            $text          = JText::_(strtoupper($shortDOW));
            $formattedDate = "$text $formattedDate";
        }

        return $formattedDate;
    }

    /**
     * Formats the date stored in the database according to the format in the component parameters
     *
     * @param string $time the date to be formatted
     *
     * @return string|bool  a formatted date string otherwise false
     */
    public static function formatTime($time)
    {
        $params     = JComponentHelper::getParams('com_thm_organizer');
        $timeFormat = $params->get('timeFormat', 'H:i');

        return date($timeFormat, strtotime($time));
    }

    /**
     * Gets the format from the component settings
     *
     * @return string the date format
     */
    public static function getFormat()
    {
        return JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');
    }

    /**
     * Checks whether a date is a valid date in the standard Y-m-d format.
     *
     * @param string $date the date to be checked
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
     * @param string $date the date string
     *
     * @return string  date sting in format Y-m-d
     */
    public static function standardizeDate($date)
    {
        $default = date('Y-m-d');

        if (empty($date)) {
            return $default;
        }

        if (self::isStandardized($date)) {
            return $date;
        }

        $dt = DateTime::createFromFormat(self::getFormat(), $date);

        return ($dt !== false and !array_sum($dt->getLastErrors())) ? $dt->format('Y-m-d') : $default;
    }
}
