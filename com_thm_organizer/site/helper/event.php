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
    public static function buildtext(&$data)
    {
        self::setContentCategoryData($data);
        self::handleDatesandTimes($data);
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
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_CATEGORIES"), 500);
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
    private static function handleDatesandTimes(&$data)
    {
        $data['rec_type'] = JRequest::getInt('rec_type');
        $data['startdate'] = trim($data['startdate']);
        $data['nativestartdate'] = $data['startdate'];
        $data['startdate'] = explode(".", $data['startdate']);
        $data['startdate'] = "{$data['startdate'][2]}-{$data['startdate'][1]}-{$data['startdate'][0]}";

        if (!empty($data['enddate']))
        {
            $data['enddate'] = trim($data['enddate']);
            $data['nativeenddate'] = $data['enddate'];
            $data['enddate'] = explode(".", $data['enddate']);
            $data['enddate'] = "{$data['enddate'][2]}-{$data['enddate'][1]}-{$data['enddate'][0]}";
            if ($data['enddate'] < $data['startdate'])
            {
                $data['enddate'] = $data['startdate'];
            }
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        else
        {
            $data['enddate'] = $data['startdate'];
            $data['publish_down'] = date("Y-m-d H:i:s", strtotime('+1 day', strtotime($data['enddate'])));
        }
        $data['starttime'] = (strlen($data['starttime']) == 4) ?
            "0{$data['starttime']}" : $data['starttime'];
        $data['endtime'] = (strlen($data['endtime']) == 4) ?
            "0{$data['endtime']}" : $data['endtime'];

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
     * Creates a short text to describe the appointment as such
     *
     * @param   array  &$data  holds data from the request
     *
     * @return  void
     */
    private static function createIntroText(&$data)
    {
        $introText = "<p>";
        $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_DATES_START');
        $introText .= self::getDateText($data);
        $introText .= "</p>";

        $groupNames = self::getNames('groups', 'title', '#__usergroups');
        if (count($groupNames))
        {
            $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_AFFECTED') . implode(", ", $groupNames) . "</p>";
        }

        $teacherNames = self::getNames('teachers', 'surname', '#__thm_organizer_teachers');
        if (count($teacherNames))
        {
            if (count($teacherNames) == 1)
            {
                $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_TEACHER') . $teacherNames[0] . "</p>";
            }
            else
            {
                $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_TEACHERS') . implode(', ', $teacherNames) . "</p>";
            }
        }

        $roomNames = self::getNames('rooms', 'name', '#__thm_organizer_rooms');
        if (count($roomNames))
        {
            if (count($roomNames) == 1)
            {
                $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_ROOM') . $roomNames[0] . "</p>";
            }
            else
            {
                $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_ROOMS') . implode(', ', $roomNames) . "</p>";
            }
        }
        $introText .= "<p>" . JText::_('COM_THM_ORGANIZER_E_INTROTEXT_FURTHER_INFORMATIONS') . "</p>";

        $introText .= "</p>";
        $data['introtext'] = $introText;
    }

    /**
     * Creates an introductory text for events
     *
     * @param   array  &$data     an array of preprepared date and time entries
     * @param   bool   $withText  whether leading and ending text should be added
     *
     * @return  string $introText
     */
    public static function getDateText(&$data, $withText = true)
    {
        $dateText = $timeText = "";
        if (empty($data['nativestartdate']))
        {
            $data['nativestartdate'] = $data['startdate'];
        }
        if (empty($data['nativeenddate']) AND !empty($data['enddate']))
        {
            $data['nativeenddate'] = $data['enddate'];
        }

        // One day events and reoccuring events use the 'same' time text
        if ($data['startdate'] == $data['enddate'] or $data['rec_type'] == 1)
        {
            if ($data['starttime'] != "00:00")
            {
                $timeText .= $withText == true? JText::_('COM_THM_ORGANIZER_E_FROM') : ' ';
                $timeText .= $data['starttime'];
            }
            if ($data['endtime'] != "00:00")
            {
                $timeText .= JText::_('COM_THM_ORGANIZER_E_TO') . $data['endtime'];
            }
            if ($data['starttime'] == "00:00" AND $data['endtime'] == "00:00")
            {
                $timeText .= JText::_("COM_THM_ORGANIZER_E_ALLDAY");
            }
        }

        // Single day events use the same date text irregardless of repetition
        if ($data['startdate'] == $data['enddate'])
        {
            $dateText .= $withText == true? JText::_('COM_THM_ORGANIZER_E_ON') : '';
            $dateText .= $data['nativestartdate'] . $timeText;
        }
        // Repeating events which span multiple days
        elseif ($data['rec_type'])
        {
            $dateText .= $withText == true? JText::_('COM_THM_ORGANIZER_E_BETWEEN') : '';
            $dateText .= $data['nativestartdate'];
            $dateText .= JText::_('COM_THM_ORGANIZER_E_TO') . $data['nativeenddate'];
            $dateText .= $timeText;
        }
        // Block events which span multiple days
        else
        {
            $dateText .= $withText == true? JText::_('COM_THM_ORGANIZER_E_FROM') : '';
            if ($data['starttime'] != "")
            {
                $dateText .= $data['starttime'] . JText::_('COM_THM_ORGANIZER_E_ON');
            }
            $dateText .= $data['nativestartdate'] . JText::_('COM_THM_ORGANIZER_E_TO');
            if ($data['endtime'] != "")
            {
                $dateText .= $data['endtime'] . JText::_('COM_THM_ORGANIZER_E_ON');
            }
            $dateText .= $data['nativeenddate'];
            if ($data['starttime'] == "" and $data['endtime'] == "")
            {
                $dateText .= JText::_("COM_THM_ORGANIZER_E_ALLDAY");
            }
        }
        $dateText .= $withText == true? JText::_("COM_THM_ORGANIZER_E_DATES_END") : '';
        return $dateText;
    }

    /**
     * Retrieves resource names from the database
     *
     * @param   string  $requestName  the name with which the REQUESTed resources can
     *                                be called upon
     * @param   string  $columnName   the column name in which the names are stored
     * @param   string  $tableName    the table which manages the resource
     *
     * @return  array   $names the names of the requested resources
     */
    private static function getNames($requestName, $columnName, $tableName)
    {
        $names = array();
        $requestName = JRequest::getVar($requestName, array());
        $dummyIndex = array_search('-1', $requestName);
        if ($dummyIndex)
        {
            unset($requestName[$dummyIndex]);
        }
        if (count($requestName))
        {
            $dbo = JFactory::getDbo();
            $query = $dbo->getQuery(true);
            $query->select("$columnName");
            $query->from("$tableName");
            $requestedIDs = "( " . implode(", ", $requestName) . " )";
            $query->where("id IN $requestedIDs");
            $dbo->setQuery((string) $query);
            
            try
            {
                $names = $dbo->loadResultArray();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_NAMES"), 500);
            }
            
            $names = (count($names)) ? $names : array();
        }
        return $names;
    }

}
