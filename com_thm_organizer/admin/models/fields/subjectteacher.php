<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectTeachers
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
class JFormFieldSubjectTeacher extends JFormField
{
    protected $type = 'subjectTeacher';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $fieldName = $this->getAttribute('name');
        $subjectID = JFactory::getApplication()->input->getInt('id', 0);
        $responsibility = $this->getAttribute('responsibility');

        $dbo = JFactory::getDBO();
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('teacherID');
        $selectedQuery->from('#__thm_organizer_subject_teachers');
        $selectedQuery->where("subjectID = '$subjectID' AND teacherResp = '$responsibility'");
        $dbo->setQuery((string) $selectedQuery);

        try
        {
            $selected = $dbo->loadColumn();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return $this->getDefault();
        }

        $teachersQuery = $dbo->getQuery(true);
        $teachersQuery->select("id AS value, surname, forename");
        $teachersQuery->from('#__thm_organizer_teachers');
        $teachersQuery->order('surname, forename');
        $dbo->setQuery((string) $teachersQuery);

        try
        {
            $teachers = $dbo->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return $this->getDefault();
        }

        foreach ($teachers as $key => $teacher)
        {
            $teachers[$key]['text'] = empty($teacher['forename'])? $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
        }

        $attributes = array('multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10');
        $selectedTeachers = empty($selected)? array() : $selected;
        return JHTML::_("select.genericlist", $teachers, "jform[$fieldName][]", $attributes, "value", "text", $selectedTeachers);
    }

    /**
     * Creates a default input in the event of an exception
     *
     * @return  string  a default teacher selection field without any teachers
     */
    private function getDefault()
    {
        $teachers = array();
        $teachers[] = array('value' => '-1', 'name' => JText::_('JNONE'));
        $fieldName = $this->getAttribute('name');
        $attributes = array('multiple' => 'multiple', 'class' => 'inputbox', 'size' => '1');
        return JHTML::_("select.genericlist", $teachers, "jform[$fieldName][]", $attributes, "value", "text");
    }
}
