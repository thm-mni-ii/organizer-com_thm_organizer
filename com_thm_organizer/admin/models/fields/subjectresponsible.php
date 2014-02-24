<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectResponsible
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');

/**
 * Class loads a list of teachers for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectResponsible extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'subjectResponsible';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $dbo = JFactory::getDBO();
        $subjectID = JRequest::getInt('id');
 
        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('teacherID')->from('#__thm_organizer_subject_teachers')->where("subjectID = '$subjectID' AND teacherResp = '1'");
        $dbo->setQuery((string) $selectedQuery);
        $selected = $dbo->loadResultArray();

        $teachersQuery = $dbo->getQuery(true);
        $teachersQuery->select("id AS value, surname, forename, username");
        $teachersQuery->from('#__thm_organizer_teachers');
        $teachersQuery->order('surname, forename');
        $dbo->setQuery((string) $teachersQuery);
        $teachers = $dbo->loadAssocList();
        foreach ($teachers as $key => $teacher)
        {
            $teachers[$key]['name'] = empty($teacher['forename'])? $teacher['surname'] : "{$teacher['surname']}, {$teacher['forename']}";
        }

        $attributes = array('multiple' => 'multiple', 'class' => 'inputbox', 'size' => '10');
        $selectedTeachers = empty($selected)? array() : $selected;
        return JHTML::_("select.genericlist", $teachers, "jform[responsibleID][]", $attributes, "value", "name", $selectedTeachers);
    }
}
