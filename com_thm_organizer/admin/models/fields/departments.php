<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';

/**
 * Class creates a select box for departments.
 */
class JFormFieldDepartments extends JFormField
{
    protected $type = 'departments';

    /**
     * Returns a selectionbox where stored coursepool can be chosen as a parent node
     *
     * @return string the HTML which forms the select box
     */
    public function getInput()
    {
        $langTag = THM_OrganizerHelperLanguage::getShortTag();

        $dbo              = JFactory::getDbo();
        $departmentsQuery = $dbo->getQuery(true);
        $departmentsQuery->select("id AS value, name_$langTag AS text");
        $departmentsQuery->from('#__thm_organizer_departments');
        $dbo->setQuery($departmentsQuery);

        $allDepartments = THM_OrganizerHelperComponent::query('loadAssocList');
        if (empty($allDepartments)) {
            return $this->getDefault();
        }

        $resourceID   = THM_OrganizerHelperComponent::getInput()->getInt('id', 0);
        $resourceType = $this->getAttribute('resource');

        $selectedQuery = $dbo->getQuery(true);
        $selectedQuery->select('DISTINCT departmentID');
        $selectedQuery->from('#__thm_organizer_department_resources');
        $selectedQuery->where("{$resourceType}ID = '$resourceID'");
        $dbo->setQuery($selectedQuery);
        $selected = THM_OrganizerHelperComponent::query('loadColumn', []);

        $attributes          = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '12'];
        $selectedDepartments = empty($selected) ? [] : $selected;

        return JHtml::_(
            'select.genericlist',
            $allDepartments,
            'jform[departments][]',
            $attributes,
            'value',
            'text',
            $selectedDepartments
        );
    }

    /**
     * Creates a default input in the event of an exception
     *
     * @return string  a default teacher selection field without any teachers
     */
    private function getDefault()
    {
        $allDepartments   = [];
        $allDepartments[] = ['value' => '-1', 'name' => JText::_('JNONE')];
        $attributes       = ['multiple' => 'multiple', 'class' => 'inputbox', 'size' => '1'];

        return JHtml::_('select.genericlist', $allDepartments, 'jform[departments][]', $attributes, 'value', 'text');
    }
}
