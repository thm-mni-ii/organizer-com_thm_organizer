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
     * Type
     *
     * @var    String
     */
    protected $type = 'subjectPrograms';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return Select box
     */
    public function getInput()
    {
        $dbo = JFactory::getDBO();
        $subjectID = JRequest::getInt('id');
 
        $rangesQuery = $dbo->getQuery(true);
        $rangesQuery->select('lft, rgt')->from('#__thm_organizer_mappings')->where("subjectID = '$subjectID'");
        $dbo->setQuery((string) $rangesQuery);
        $ranges = $dbo->loadAssocList();

        if (!empty($ranges))
        {
            $rangeConditions = array();
            foreach ($ranges as $range)
            {
                $rangeConditions[] = "( lft < '{$range['lft']}' AND rgt > '{$range['rgt']}' )";
            }
            $rangesClause = implode(' OR ', $rangeConditions);

            $selectedProgramsQuery = $dbo->getQuery(true);
            $selectedProgramsQuery->select("DISTINCT dp.id");
            $selectedProgramsQuery->from('#__thm_organizer_mappings AS m');
            $selectedProgramsQuery->innerJoin('#__thm_organizer_programs AS dp ON m.programID = dp.id');
            $selectedProgramsQuery->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
            $selectedProgramsQuery->where($rangesClause);
            $dbo->setQuery((string) $selectedProgramsQuery);
            $associatedPrograms = $dbo->loadResultArray();
        }

        $allProgramsQuery = $dbo->getQuery(true);
        $allProgramsQuery->select("dp.id AS value, CONCAT(dp.subject, ' (', d.abbreviation, ' ', dp.version, ')') AS program");
        $allProgramsQuery->from('#__thm_organizer_programs AS dp');
        $allProgramsQuery->innerJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $allProgramsQuery->innerJoin('#__thm_organizer_mappings AS m ON dp.id = m.programID');
        $allProgramsQuery->order('program ASC');
        $dbo->setQuery((string) $allProgramsQuery);
        $allPrograms = $dbo->loadAssocList();
 
        $programDefaultOptions = array();
        $programDefaultOptions[] = array('value' => '-1', 'program' => JText::_('COM_THM_ORGANIZER_SEARCH_PROGRAM'));
        $programDefaultOptions[] = array('value' => '-1', 'program' => JText::_('COM_THM_ORGANIZER_POM_NO_PROGRAM'));
        $programs = array_merge($programDefaultOptions, empty($allPrograms)? array() : $allPrograms);
 
        $attributes = array('multiple' => 'multiple');
        $selectedPrograms = empty($associatedPrograms)? array() : $associatedPrograms;
        return JHTML::_("select.genericlist", $programs, "jform[programID][]", $attributes, "value", "program", $selectedPrograms);
    }
}
