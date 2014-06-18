<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerHelperSubjectTeachers
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class loads a list of teachers for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerHelperSubjectTeachers
{
    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public static function getInput($subjectID, $name, $responsibilityKey = 1)
    {
        $dbo = JFactory::getDBO();
 
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('teacherID');
        $selectedQuery->from('#__thm_organizer_subject_teachers');
        $selectedQuery->where("subjectID = '$subjectID' AND teacherResp = '$responsibilityKey'");
        $dbo->setQuery((string) $selectedQuery);

        try 
        {
            $selected = $dbo->loadResultArray();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_TEACHER"), 500);
        }

        $teachersQuery = $dbo->getQuery(true);
        $teachersQuery->select("id AS value, surname, forename, username");
        $teachersQuery->from('#__thm_organizer_teachers');
        $teachersQuery->order('surname, forename');
        $dbo->setQuery((string) $teachersQuery);
        
        try 
        {
            $teachers = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_TEACHERS"), 500);
        }
        
        foreach ($teachers as $key => $teacher)
        {
            $teachers[$key]['name'] = empty($teacher['forename'])? $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
        }

        $attributes = array('multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10');
        $selectedTeachers = empty($selected)? array() : $selected;
        return JHTML::_("select.genericlist", $teachers, "jform[$name][]", $attributes, "value", "name", $selectedTeachers);
    }
}
