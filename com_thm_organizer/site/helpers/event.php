<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Dominik Bassing, <dominik.bassing@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class provides helper methods for handling events
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerHelperEvent
{
    /**
     * Cleans and sets date and time related properties
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  void
     */
    public static function processTimes(&$event)
    {
        $event['startdate'] = THM_OrganizerHelperComponent::standardizeDate($event['startdate']);
        $event['enddate'] = empty($event['enddate'])?
            $event['startdate'] : THM_OrganizerHelperComponent::standardizeDate($event['enddate']);

        // Start publishing on event creation by default
        if (empty($event['publish_up']))
        {
            $event['publish_up'] = self::getDefaultPublishDate();
        }
        else
        {
            $rawPublishUp = THM_OrganizerHelperComponent::standardizeDate($event['publish_up']);
            $event['publish_up'] = date("Y-m-d H:i:s", strtotime($rawPublishUp));
        }

        // Stop publishing the day after the event is over by default
        if (empty($event['publish_down']))
        {
            $event['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($event['enddate'])));
        }
        else
        {
            $rawPublishDown = THM_OrganizerHelperComponent::standardizeDate($event['publish_down']);
            $event['publish_down'] = date("Y-m-d H:i:s", strtotime($rawPublishDown));
        }

        // Extends three digit times
        $event['starttime'] = self::standardizeTime($event['starttime']);
        $event['endtime'] = self::standardizeTime($event['endtime']);

        // Converts the dates/times to timestamps for easier comparison later
        $event['start'] = strtotime("{$event['startdate']} {$event['starttime']}");
        $event['end'] = strtotime("{$event['enddate']} {$event['endtime']}");
    }

    /**
     * uses the joomla configuration timezone to adjust the publish up date to
     * UTC time
     *
     * @return date the date normalized to UTC time for content
     */
    private static function getDefaultPublishDate()
    {
        date_default_timezone_set("UTC");
        $hereZone = new DateTimeZone(JFactory::getConfig()->get('offset'));
        $hereTime = new DateTime("now", $hereZone);
        $offset = $hereTime->getOffset();
        if ($offset > 0)
        {
            $offsetString = " -$offset";
        }
        else
        {
            $negativeOffset = abs($offset);
            $offsetString = " +$negativeOffset";
        }
        $offsetString .= " seconds";
        return date("Y-m-d H:i:s", strtotime($offsetString));
    }

    /**
     * Creates a time string with the format H:i
     *
     * @param   string  $time  the original time string
     *
     * @return  string  the formatted time string, empty if the input could not be resolved
     */
    private static function standardizeTime($time)
    {
        // Normal time check
        $matches = array();
        preg_match("/[0-2][0-9]:[0-5][0-9]/", $time, $matches);
        if (count($matches))
        {
            return $time;
        }
        preg_match("/[0-9]:[0-5][0-9]/", $time, $matches);
        if (count($matches))
        {
            return "0$time";
        }
        preg_match("/[0-2][0-9]/", $time, $matches);
        if (count($matches))
        {
            return "$time:00";
        }
        preg_match("/[0-9]/", $time, $matches);
        if (count($matches))
        {
            return "0$time:00";
        }
        return '';
    }

    /**
     * Calls Methods to build the event for save and preview
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  void
     */
    public static function buildText(&$event)
    {
        if (!empty($event['title']))
        {
            self::processTimes($event);
        }
        self::createIntroText($event);
    }

    /**
     * Creates a short text to describe the appointment
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  void
     */
    public static function createIntroText(&$event)
    {
        $introText = self::getDateText($event);

        $groups = self::getNames($event['groups'], 'title', '#__usergroups', 'GROUP');
        $teachers = self::getNames($event['teachers'], 'surname', '#__thm_organizer_teachers', 'TEACHER');
        $rooms = self::getNames($event['rooms'], 'name', '#__thm_organizer_rooms', 'ROOM');
        $resources = (!empty($groups) OR !empty($teachers) OR !empty($rooms));
        if ($resources)
        {
            $introText .= '<p>' . JText::_('COM_THM_ORGANIZER_RESOURCES_AFFECTED') . '</p>' . $groups . $teachers . $rooms;
        }

        if (!empty($event['fulltext']))
        {
            $introText .= '<div class="content-label">' . JText::_('COM_THM_ORGANIZER_FURTHER_INFORMATION') . '</div>';
        }

        $event['introtext'] = $introText;
    }

    /**
     * Creates an introductory text for events
     *
     * @param   array  &$event  an array of preprepared date and time entries
     *
     * @return  string $introText
     */
    public static function getDateText(&$event)
    {
        $startdate = THM_OrganizerHelperComponent::formatDate($event['startdate']);
        $enddate = THM_OrganizerHelperComponent::formatDate($event['enddate']);
        $useStartTime = (!empty($event['starttime']) AND $event['starttime'] != "00:00");
        $useEndTime = (!empty($event['endtime']) AND $event['endtime'] != "00:00");
        $useTimes = ($useStartTime OR $useEndTime);
        $singleDay = $event['startdate'] == $event['enddate'];

        if ($singleDay)
        {
            return self::getSingleDayText($event);
        }

        if ($event['recurrence_type'] == 0 AND $useTimes)
        {
            return self::getBlockText($event);
        }

        if ($event['recurrence_type'] == 1 AND $useTimes)
        {
            return self::getDailyText($event);
        }

        return JText::sprintf('COM_THM_ORGANIZER_MULTIPLE_DAYS_NO_TIMES', $startdate, $enddate);
    }

    /**
     * Creates the text for events which take place on only one day.
     *
     * @param   array  &$event  the event to be processed
     *
     * @return  string  the text output
     */
    private static function getSingleDayText(&$event)
    {
        $date = THM_OrganizerHelperComponent::formatDate($event['startdate']);
        $useStartTime = (!empty($event['starttime']) AND $event['starttime'] != "00:00");
        $useEndTime = (!empty($event['endtime']) AND $event['endtime'] != "00:00");

        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf( 'COM_THM_ORGANIZER_ONE_DAY_START_END', $date, $event['starttime'], $event['endtime']);
        }

        if ($useStartTime)
        {
            return JText::sprintf( 'COM_THM_ORGANIZER_ONE_DAY_START', $date, $event['starttime']);
        }

        if ($useEndTime)
        {
            return JText::sprintf( 'COM_THM_ORGANIZER_ONE_DAY_END', $date, $event['endtime']);
        }

        return JText::sprintf('COM_THM_ORGANIZER_ONE_DAY_NO_TIMES', $date);
    }

    /**
     * Gets a formatted text for block events that take place on a multiple days and use times
     *
     * @param   array  &$event  the event array
     *
     * @return  string  formatted text for date/time output
     */
    private static function getBlockText(&$event)
    {
        $startdate = THM_OrganizerHelperComponent::formatDate($event['startdate']);
        $enddate = THM_OrganizerHelperComponent::formatDate($event['enddate']);
        $useStartTime = (!empty($event['starttime']) AND $event['starttime'] != "00:00");
        $useEndTime = (!empty($event['endtime']) AND $event['endtime'] != "00:00");

        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_BLOCK_START_END', $startdate, $event['starttime'], $event['endtime'], $enddate);
        }

        if ($useStartTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_BLOCK_START', $startdate, $event['starttime'], $enddate);
        }

        if ($useEndTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_BLOCK_END', $startdate, $event['endtime'], $enddate);
        }

        return '';
    }

    /**
     * Gets a formatted text for daily events that take place on a multiple days and use times
     *
     * @param   array  &$event  the event array
     *
     * @return  string  formatted text for date/time output
     */
    private static function getDailyText(&$event)
    {
        $startdate = THM_OrganizerHelperComponent::formatDate($event['startdate']);
        $enddate = THM_OrganizerHelperComponent::formatDate($event['enddate']);
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;

        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_DAILY_START_END', $startdate, $enddate, $event['starttime'], $event['endtime']);
        }

        if ($useStartTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_DAILY_START', $startdate, $enddate, $event['starttime']);
        }

        if ($useEndTime)
        {
            return JText::sprintf('COM_THM_ORGANIZER_DAILY_END', $startdate, $enddate, $event['endtime']);
        }

        return '';
    }

    /**
     * Retrieves resource names from the database
     *
     * @param   array   &$resources   the event resources
     * @param   string  $columnName   the column name in which the names are stored
     * @param   string  $tableName    the table which manages the resource
     * @param   string  $textRoot     the root text
     *
     * @return  string   a text with a label and the names of the requested resources on success, otherwise empty
     */
    private static function getNames(&$resources, $columnName, $tableName, $textRoot)
    {
        if (empty($resources))
        {
            return '';
        }

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("$columnName");
        $query->from("$tableName");
        $requestedIDs = "( " . implode(", ", $resources) . " )";
        $query->where("id IN $requestedIDs");
        $dbo->setQuery((string) $query);

        try
        {
            $names = $dbo->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return '';
        }

        if (empty($names))
        {
            return '';
        }

        $html = '<div class="control-group ">';
        if (count($names) == 1)
        {
            $html .= '<div class="control-label">' . JText::_("COM_THM_ORGANIZER_EVENT_{$textRoot}") . '</div>';
            $html .= '<div class="controls">' . $names[0] . '</div>';
        }
        else
        {
            $html .= '<div class="control-label">' . JText::_("COM_THM_ORGANIZER_EVENT_{$textRoot}S") . '</div>';
            $html .= '<div class="controls">' . implode(', ', $names) . '</div>';
        }
        $html .= '</div>';

        return $html;
    }

    /**
     * Reformats events dates and times to the german standard
     *
     * @param   array  &$event  the event to be processed
     *
     * @return  void
     */
    public static function localizeEvent(&$event)
    {
        $event['startdate'] = THM_OrganizerHelperComponent::formatDate($event['startdate']);
        $event['enddate'] = THM_OrganizerHelperComponent::formatDate($event['enddate']);
        $event['starttime'] = THM_OrganizerHelperComponent::formatTime($event['starttime']);
        $event['endtime'] = THM_OrganizerHelperComponent::formatTime($event['endtime']);
    }
}
