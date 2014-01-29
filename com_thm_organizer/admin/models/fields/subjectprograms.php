<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldSubjectPrograms
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.form.formfield');
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldSubjectPrograms extends JFormField
{
    /**
     * @var  string
     */
    protected $type = 'subjectPrograms';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $subjectID = JRequest::getInt('id');
        $ranges = THM_OrganizerHelperMapping::getRanges('subjectID', $subjectID);
        $selectedPrograms = !empty($ranges)?
            THM_OrganizerHelperMapping::getSelectedPrograms($ranges) : array();
        $allPrograms = THM_OrganizerHelperMapping::getAllPrograms();

        $defaultOptions = array(array('value' => '-1', 'program' => JText::_('COM_THM_ORGANIZER_POM_NO_PROGRAM')));
        $programs = array_merge($defaultOptions, $allPrograms);

        $attributes = array('multiple' => 'multiple', 'size' => '10');
        return JHTML::_("select.genericlist", $programs, "jform[programID][]", $attributes, "value", "program", $selectedPrograms);
    }
}
