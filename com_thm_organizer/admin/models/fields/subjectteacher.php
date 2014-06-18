<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectTeachers
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
include_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/subjectteachers.php';

/**
 * Class loads a list of teachers for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectTeacher extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    protected $type = 'subjectTeacher';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $subjectID = JRequest::getInt('id');
        $input = THM_OrganizerHelperSubjectTeachers::getInput($subjectID, 'teacherID', '2');
        return $input;
    }
}
