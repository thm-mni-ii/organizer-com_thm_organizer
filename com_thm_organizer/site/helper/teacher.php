<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerHelperTeacher
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
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
        $picture = $dbo->loadResult();

        if (!empty($picture))
        {
            $path = JURI::base() . "components/com_thm_groups/img/portraits/$picture";
            return JHTML::image("$path", JText::_('COM_THM_ORGANIZER_RESPONSIBLE'), array());
        }
        else
        {
            return '';
        }
    }
    
    /**
     * Retrieves the teacher responsible for the subject's development
     *
     * @param   int  $subjectID  the subject's id
     *
     * @return  array  an array of teacher data
     */
    public static function getData($subjectID, $responsibility = null, $multiple = false)
    {
        $dbo = JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("t.id, t.surname, t.forename, t.title, t.username, u.id AS userID, teacherResp");
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
            return $dbo->loadAssocList();
        }
        else
        {
            return $dbo->loadAssoc();
        }
    }

    /**
     * Method to determine the name of a given module
     *
     * @param   int  $userID  the user id of the teacher
     *
     * @return  string title, first, and last names of the teacher as existent
     */
    public static function getName($userID)
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
		$abomination = $dbo->loadAssoc();

		if (!empty($abomination))
		{
            return "{$abomination['title']} {$abomination['forename']} {$abomination['surname']}";
		}
        else
        {
            return  '';
        }
    }

    
    /**
     * Method to build the link to a user profile of THM Groups
     *
     * @param   array  &$teacherData  an array containing information about a
     *                                teacher
     *
     * @return  string  the url of the teacher's thm groups details
     */
    public static function getLink(&$teacherData)
    {
        $link = 'index.php?option=com_thm_groups&view=profile&layout=default';
        $link .= "&gsuid={$teacherData['userID']}&name={$teacherData['surname']}&Itemid=" . JRequest::getVar('Itemid');
        return JRoute::_($link);
    }
}