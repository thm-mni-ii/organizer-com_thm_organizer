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

require_once JPATH_ROOT . '/components/com_thm_organizer/autoloader.php';

use Joomla\CMS\Factory;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

\JFormHelper::loadFieldClass('list');

/**
 * Class creates a select box for plan programs.
 */
class JFormFieldCategoryID extends \JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'categoryID';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT cat.id AS value, cat.name AS text');
        $query->from('#__thm_organizer_categories AS cat');
        $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = cat.id');
        $query->order('text ASC');

        // For use in the merge view
        $selectedIDs = OrganizerHelper::getSelectedIDs();
        if (!empty($selectedIDs)) {
            $query->innerJoin('#__thm_organizer_groups AS gr ON gr.categoryID = cat.id');
            $query->where("gr.id IN ( '" . implode("', '", $selectedIDs) . "' )");
        }

        // Ensures a boolean value and avoids double checking the variable because of false string positives.
        $accessRequired     = $this->getAttribute('access', 'false') == 'true';
        $departmentRestrict = $this->getAttribute('departmentRestrict', 'false');

        if ($departmentRestrict !== 'false') {
            $allowedDepartments = $accessRequired ?
                Access::getAccessibleDepartments('schedule')
                : Departments::getDepartmentsByResource('program');

            $defaultDept = $departmentRestrict === 'force' ? $allowedDepartments[0] : 0;

            // Direct input
            $input        = OrganizerHelper::getInput();
            $departmentID = $input->getInt('departmentID', $defaultDept);

            // Possible frontend form (jform)
            $feFormData      = OrganizerHelper::getForm();
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

        $values = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($values)) {
            return $defaultOptions;
        }

        $options = [];

        foreach ($values as $value) {
            if (!empty($value['value'])) {
                $options[] = HTML::_('select.option', $value['value'], $value['text']);
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
