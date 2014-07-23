<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperTeacher
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class provides methods used by organizer models for retrieving teacher data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerHelperTeacher
{
    /**
     * Method to get the groups picture
     *
     * @param   int  $userID  ID of a module
     *
     * @return  mixed  JHTML image or an empty string
     */
    public static function getPicture($userID)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("value")->from('#__thm_groups_picture')->where("userid = '$userID'")->order('structid DESC');
        $dbo->setQuery((string) $query, 0, 1);
        
        try
        {
            $picture = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (!empty($picture))
        {
            return JURI::root() . "components/com_thm_groups/img/portraits/$picture";
        }
        else
        {
            return '';
        }
    }

    /**
     * Retrieves the teacher responsible for the subject's development
     *
     * @param   int  $teacherID  the teacher's id
     *
     * @return  array  an array of teacher data
     */
    public static function getDataByID($teacherID)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp, gpuntisID");
        $query->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON t.id = st.teacherID ');
        $query->leftJoin('#__users AS u ON t.username = u.username');
        $query->where("t.id= '$teacherID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $teacherData = $dbo->loadAssoc();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $teacherData;
    }

    /**
     * Retrieves the teacher responsible for the subject's development
     *
     * @param   int   $subjectID       the subject's id
     * @param   int   $responsibility  represents the teacher's level of
     *                                 responsibility for the subject
     * @param   bool  $multiple        whether or not multiple results are desired
     *
     * @return  array  an array of teacher data
     */
    public static function getDataBySubject($subjectID, $responsibility = null, $multiple = false)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp, gpuntisID");
        $query->from('#__thm_organizer_teachers AS t');
        $query->innerJoin('#__thm_organizer_subject_teachers AS st ON t.id = st.teacherID ');
        $query->leftJoin('#__users AS u ON t.username = u.username');
        $query->where("st.subjectID = '$subjectID' ");
        if (!empty($responsibility))
        {
            $query->where("st.teacherResp = '$responsibility'");
        }
        $dbo->setQuery((string) $query);
        if ($multiple)
        {
            try 
            {
                $teacherList = $dbo->loadAssocList();
            }
            catch (Exception $exc)
            {
                JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                return array();
            }
            self::ensureUnique($teacherList);
            return $teacherList;
        }
        try
        {
            return $dbo->loadAssoc();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return array();
        }
    }

    /**
     * Checks for multiple teacher entries (responsibilities) for a subject and removes the lesser
     *
     * @param   array  &$list  the list of teachers responsilbe for a subject
     *
     * @return  void  removes duplicate list entries dependent on responsibility
     */
    private static function ensureUnique(&$list)
    {
        $keysToIds = array();
        foreach ($list as $key => $item)
        {
            $keysToIds[$key] = $item['id'];
        }
        $valueCount = array_count_values($keysToIds);
        foreach($list as $key => $item)
        {
            $unset = ($valueCount[$item['id']] > 1 AND $item['teacherResp'] > 1);
            if ($unset)
            {
                unset($list[$key]);
            }
        }
    }

    /**
     * Generates a default teacher text based upon organizer's internal data
     * 
     * @param   mixed  $teacherData  array or object with teacher data
     *                               (objects are converted internally to arrays)
     * 
     * @return  string  the default name of the teacher
     */
    public static function getDefaultName($teacherData)
    {
        $teacherData = is_object($teacherData)? (array) $teacherData : $teacherData;
        $title = empty($teacherData['title'])? '' : "{$teacherData['title']} ";
        $forename = empty($teacherData['forename'])? '' : "{$teacherData['forename']} ";
        $surname = $teacherData['surname'];
        return $title . $forename . $surname;
    }

    /**
     * Method to determine the name of a given module
     *
     * @param   int  $userID  the user id of the teacher
     *
     * @return  string title, first, and last names of the teacher as existent
     */
    public static function getNameFromTHMGroups($userID)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT text1.value AS forename, text2.value AS surname, text3.value AS title');
        $query->from('#__thm_groups_text AS text1');
        $query->innerJoin('#__thm_groups_text AS text2 ON text1.userid = text2.userid');
        $query->innerJoin('#__thm_groups_text as text3 ON text2.userid = text3.userid');
        $query->where('text1.structid = 1');
        $query->where('text2.structid = 2');
        $query->where('text3.structid = 5');
        $query->where("text1.userid = '$userID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $abomination = $dbo->loadAssoc();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }

        if (!empty($abomination))
        {
            return "{$abomination['title']} {$abomination['forename']} {$abomination['surname']}";
        }
        return  '';
    }

    
    /**
     * Method to build the link to a user profile of THM Groups
     *
     * @param   int     $userID   the teacher's user ID
     * @param   string  $surname  the teacher's surname
     * @param   int     $itemID   the menu item id from which the link will be
     *                            called
     *
     * @return  string  the url of the teacher's thm groups details
     */
    public static function getLink($userID, $surname = null, $itemID = null)
    {
        $link = "index.php?option=com_thm_groups&view=profile&layout=default&gsuid=$userID&name=$surname&Itemid=$itemID";
        return JRoute::_($link);
    }

    /**
     * Method to resolve the Untis ID to a user ID
     * 
     * @param   string  $gpuntisID  the teacher's gpuntis ID
     * 
     * @return  mixed  the teacher's user ID or null if the query failed
     */
    public static function getUserIDfromUntisID($gpuntisID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('u.id')->from('#__users AS u')->innerJoin('#__thm_organizer_teachers AS t ON t.username = u.username');
        $query->where("t.gpuntisID = '$gpuntisID'");
        $dbo->setQuery((string) $query);
        
        try
        {
            $userID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $userID;
    }
}