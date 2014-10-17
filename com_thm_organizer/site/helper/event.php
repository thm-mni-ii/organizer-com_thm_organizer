<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        Helper for appointment/event view
 * @author      Dominik Bassing, <dominik.bassing@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class THM_OrganizerHelperEvent for component com_thm_organizer
 *
 * Class provides methods to create Eventtext for save/preview
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THM_OrganizerHelperEvent
{
    /**
     * Calls Methods to build the event for save and preview
     *
     * @param   array  &$data  holds data from the request
     *
     * @return  void
     */
    public static function buildText(&$data)
    {
        self::setContentCategoryData($data);
        $requestTitle = JFactory::getApplication()->input->getString('title', '');
        if (!empty($requestTitle))
        {
            self::cleanRequestTimeData($data);
        }
        self::createIntroText($data);
    }

    /**
     * Retrieves the content category id and title and places them in the
     * data array
     *
     * @param   array  &$data  holds data from the request
     *
     * @return  void
     */
    private static function setContentCategoryData(&$data)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('title, contentCatID');
        $query->from('#__thm_organizer_categories');
        $query->where("id = '{$data['categoryID']}'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $category = $dbo->loadAssoc();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return;
        }
        
        $data['contentCatName'] = $category['title'];
        $data['contentCatID'] = $category['contentCatID'];
    }

    /**
     * Cleans and sets date and time related properties
     *
     * @param   array  &$data  holds data from the request
     *
     * @return  void
     */
    public static function cleanRequestTimeData(&$data)
    {
        $data['rec_type'] = JFactory::getApplication()->input->getInt('rec_type', 0);
        $startdate = trim($data['startdate']);
        $sdParts = explode(".", $startdate);
        $data['startdate'] = "{$sdParts[2]}-{$sdParts[1]}-{$sdParts[0]}";

        if (!empty($data['enddate']))
        {
            $enddate = trim($data['enddate']);
            $edParts = explode(".", $enddate);
            $data['enddate'] = "{$edParts[2]}-{$edParts[1]}-{$edParts[0]}";
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        else
        {
            $data['enddate'] = $data['startdate'];
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }

        // Extends three digit times
        $data['starttime'] = (strlen($data['starttime']) == 4)? "0{$data['starttime']}" : $data['starttime'];
        $data['endtime'] = (strlen($data['endtime']) == 4) ? "0{$data['endtime']}" : $data['endtime'];

        // Converts the times to integer for easier comparison later
        $data['start'] = strtotime("{$data['startdate']} {$data['starttime']}");
        $data['end'] = strtotime("{$data['enddate']} {$data['endtime']}");

        $data['publish_up'] = self::getPublishDate();
    }

    /**
     * getPublishDate
     *
     * uses the joomla configuration timezone to adjust the publish up date to
     * UTC time
     *
     * @return date the date normalized to UTC time for content
     */
    private static function getPublishDate()
    {
        date_default_timezone_set("UTC");
        $hereZone = new DateTimeZone(JFactory::getApplication()->getCfg('offset'));
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
     * Creates a short text to describe the appointment
     *
     * @param   array  &$event  holds data from the request
     *
     * @return  void
     */
    private static function createIntroText(&$event)
    {
        $introText = '<p>';
        $introText .= self::getDateText($event);
        $introText .= self::getNames('groups', 'title', '#__usergroups', 'GROUP', $event['groups']);
        $introText .= self::getNames('teachers', 'surname', '#__thm_organizer_teachers', 'TEACHER', $event['teachers']);
        $introText .= self::getNames('rooms', 'name', '#__thm_organizer_rooms', 'ROOM', $event['rooms']);

        if (!empty($event['description']))
        {
            $introText .= '<p><strong>' . JText::_('COM_THM_ORGANIZER_E_INTROTEXT_FURTHER_INFORMATION') . '</strong></p>';
        }

        $introText .= "</p>";
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
        $useStartTime = (empty($event['starttime']) OR $event['starttime'] == "00:00")? false : true;
        $useEndTime = (empty($event['endtime']) OR $event['endtime'] == "00:00")? false : true;
        $useTimes = ($useStartTime OR $useEndTime);
        $singleDay = ($event['enddate'] == "00.00.0000" OR $event['startdate'] == $event['enddate']);

        if ($singleDay)
        {
            return self::getSingleDayText($event);
        }

        if ($event['rec_type'] == 0 AND $useTimes)
        {
            return self::getBlockText($event);
        }

        if ($event['rec_type'] == 1 AND $useTimes)
        {
            return self::getDailyText($event);
        }

        return JText::sprintf(
            'COM_THM_ORGANIZER_E_MULTIPLENOTIME',
            self::localizeDate($event['startdate']),
            self::localizeDate($event['enddate'])
        );
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
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;
        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_SINGLESTARTEND',
                self::localizeDate($event['startdate']),
                $event['starttime'],
                $event['endtime']
            );
        }

        if ($useStartTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_SINGLESTART',
                self::localizeDate($event['startdate']),
                $event['starttime']
            );
        }

        if ($useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_SINGLEEND',
                self::localizeDate($event['startdate']),
                $event['endtime']
            );
        }

        return JText::sprintf('COM_THM_ORGANIZER_E_SINGLE', self::localizeDate($event['startdate']));
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
        $useStartTime = (empty($event['starttime']) OR $event['starttime'] == "00:00")? false : true;
        $useEndTime = (empty($event['endtime']) OR $event['endtime'] == "00:00")? false : true;
        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_BLOCKSTARTEND',
                self::localizeDate($event['startdate']),
                $event['starttime'],
                $event['endtime'],
                self::localizeDate($event['enddate'])
            );
        }

        if ($useStartTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_BLOCKSTART',
                self::localizeDate($event['startdate']),
                $event['starttime'],
                self::localizeDate($event['enddate'])
            );
        }

        if ($useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_BLOCKEND',
                self::localizeDate($event['startdate']),
                $event['endtime'],
                self::localizeDate($event['enddate'])
            );
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
        $useStartTime = $event['starttime'] == "00:00"? false : true;
        $useEndTime = $event['endtime'] == "00:00"? false : true;
        if ($useStartTime AND $useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_DAILYSTARTEND',
                self::localizeDate($event['startdate']),
                self::localizeDate($event['enddate']),
                $event['starttime'],
                $event['endtime']
            );
        }

        if ($useStartTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_DAILYSTART',
                self::localizeDate($event['startdate']),
                self::localizeDate($event['enddate']),
                $event['starttime']
            );
        }

        if ($useEndTime)
        {
            return JText::sprintf(
                'COM_THM_ORGANIZER_E_DAILYEND',
                self::localizeDate($event['startdate']),
                self::localizeDate($event['enddate']),
                $event['endtime']
            );
        }

        return '';
    }

    /**
     * Retrieves resource names from the database
     *
     * @param   string  $requestName  the name with which the REQUESTed resources can
     *                                be called upon
     * @param   string  $columnName   the column name in which the names are stored
     * @param   string  $tableName    the table which manages the resource
     * @param   string  $textRoot     the root text
     * @param   object  &$resources   the event resources (when not called from a form)
     *
     * @return  array   $names the names of the requested resources
     */
    private static function getNames($requestName, $columnName, $tableName, $textRoot, &$resources = null)
    {
        if (empty($resources))
        {
            $resources = JFactory::getApplication()->input->get($requestName, array(), 'array');
        }

        // Remove the dummy index if selected
        $dummyIndex = array_search('-1', $resources);
        if ($dummyIndex)
        {
            unset($resources[$dummyIndex]);
        }

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

        $text = '<p>';
        if (count($names) == 1)
        {
            $text .= '<strong>' . JText::_("COM_THM_ORGANIZER_E_{$textRoot}") . '</strong>';
            $text .= $names[0];
        }
        else
        {
            $text .= '<strong>' . JText::_("COM_THM_ORGANIZER_E_{$textRoot}S") . '</strong>';
            $text .= implode(', ', $names);
        }
        $text .= '</p>';

        return $text;
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
        $event['startdate'] = self::localizeDate($event['startdate']);
        $event['enddate'] = self::localizeDate($event['enddate']);
        $event['starttime'] = date_format(date_create($event['starttime']), 'H:i');
        $event['endtime'] = date_format(date_create($event['endtime']), 'H:i');
    }

    /**
     * Converts a date string into the locally accepted format
     *
     * @param   string  $date  the date string in format Y-m-d
     *
     * @return  string  date sting in local format
     */
    public static function localizeDate($date)
    {
        if (empty($date))
        {
            return '';
        }
        return date_format(date_create($date), 'd.m.Y');
    }

    /**
     * Converts a date string into the standardized format
     *
     * @param   string  $date  the date string
     *
     * @return  string  date sting in format Y-m-d
     */
    public static function standardizeDate($date)
    {
        if (empty($date))
        {
            return '';
        }
        return date_format(date_create($date), 'Y-m-d');
    }
}
