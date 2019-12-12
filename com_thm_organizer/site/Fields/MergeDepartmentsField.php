<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a generalized select box for selection of a single id column value among those already selected.
 */
class MergeDepartmentsField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'MergeDepartments';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $selectedIDs    = Input::getSelectedIDs();
        $resource       = str_replace('_merge', '', Input::getView());
        $validResources = ['category', 'person'];
        $invalid        = (empty($selectedIDs) or empty($resource) or !in_array($resource, $validResources));
        if ($invalid) {
            return [];
        }

        $textColumn = 'shortName_' . Languages::getTag();
        $table      = $resource === 'category' ? 'categories' : 'persons';

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select("DISTINCT depts.id AS value, depts.$textColumn AS text")
            ->from("#__thm_organizer_departments as depts")
            ->innerJoin("#__thm_organizer_department_resources AS dr ON dr.departmentID = depts.id")
            ->innerJoin("#__thm_organizer_$table AS res ON res.id = dr.{$resource}ID")
            ->where("res.id IN ( '" . implode("', '", $selectedIDs) . "' )")
            ->order('text ASC');
        $dbo->setQuery($query);

        $valuePairs = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($valuePairs)) {
            return [];
        }

        $options = [];
        $values  = [];
        foreach ($valuePairs as $valuePair) {
            $options[]                   = HTML::_('select.option', $valuePair['value'], $valuePair['text']);
            $values[$valuePair['value']] = $valuePair['value'];
        }

        $this->value = $values;

        return empty($options) ? [] : $options;
    }
}
