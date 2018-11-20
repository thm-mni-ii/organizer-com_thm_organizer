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
JFormHelper::loadFieldClass('list');

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/component.php';

/**
 * Class creates a select box for plan programs.
 */
class JFormFieldPlanProgramID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'planProgramID';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT ppr.id AS value, ppr.name AS text');
        $query->from('#__thm_organizer_plan_programs AS ppr');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id');
        $query->order('text ASC');

        // For use in the merge view
        $selectedIDs = THM_OrganizerHelperComponent::getInput()->get('cid', [], 'array');
        if (!empty($selectedIDs)) {
            $selectedIDs = Joomla\Utilities\ArrayHelper::toInteger($selectedIDs);
            $query->innerJoin('#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id');
            $query->where("ppl.id IN ( '" . implode("', '", $selectedIDs) . "' )");
        }

        // Ensures a boolean value and avoids double checking the variable because of false string positives.
        $accessRequired     = $this->getAttribute('access', 'false') == 'true';
        $departmentRestrict = $this->getAttribute('departmentRestrict', 'false');

        if ($departmentRestrict !== 'false') {
            $allowedDepartments = $accessRequired ?
                THM_OrganizerHelperAccess::getAccessibleDepartments('schedule')
                : THM_OrganizerHelperDepartments::getDepartmentsByResource('program');

            $defaultDept = $departmentRestrict === 'force' ? $allowedDepartments[0] : 0;

            // Direct input
            $input        = THM_OrganizerHelperComponent::getInput();
            $departmentID = $input->getInt('departmentID', $defaultDept);

            // Possible frontend form (jform)
            $feFormData      = $input->get('jform', [], 'array');
            $plausibleFormID = (!empty($feFormData) and !empty($feFormData['departmentID']) and is_numeric($feFormData['departmentID']));
            $departmentID    = $plausibleFormID ? $feFormData['departmentID'] : $departmentID;

            // Possible backend form (list)
            $beFormData      = $input->get('list', [], 'array');
            $plausibleFormID = (!empty($beFormData) and !empty($beFormData['departmentID']) and is_numeric($beFormData['departmentID']));
            $departmentID    = $plausibleFormID ? $beFormData['departmentID'] : $departmentID;

            $restrict = (!empty($departmentID)
                and (empty($allowedDepartments) or in_array($departmentID, $allowedDepartments)));

            if ($restrict) {
                $query->where("dr.departmentID = '$departmentID'");
            }
        }

        $dbo->setQuery($query);
        $defaultOptions = parent::getOptions();

        $values = THM_OrganizerHelperComponent::query('loadAssocList');
        if (empty($values)) {
            return $defaultOptions;
        }

        $options = [];

        foreach ($values as $value) {
            if (!empty($value['value'])) {
                $options[] = JHtml::_('select.option', $value['value'], $value['text']);
            }
        }

        // An empty/default value should not be allowed in a merge view.
        if (empty($selectedIDs)) {
            $options = array_merge($defaultOptions, $options);

            return $options;
        }

        return count($options) ? $options : $defaultOptions;
    }
}
