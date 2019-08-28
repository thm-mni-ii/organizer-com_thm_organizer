<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a generalized select box for selection of a single column value among those already selected.
 */
class MergeValuesField extends OptionsField
{
    /**
     * @var  string
     */
    protected $type = 'MergeValues';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $selectedIDs    = Input::getSelectedIDs();
        $resource       = str_replace('_merge', '', Input::getView());
        $validResources = ['category', 'field', 'group', 'method', 'room', 'roomtype', 'person'];
        $invalid        = (empty($selectedIDs) or empty($resource) or !in_array($resource, $validResources));
        if ($invalid) {
            return [];
        }

        $column = $this->getAttribute('name');
        $table  = $resource === 'category' ? 'categories' : "{$resource}s";

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select("DISTINCT $column AS value")
            ->from("#__thm_organizer_$table");
        $query->where("id IN ( '" . implode("', '", $selectedIDs) . "' )");
        $query->order('value ASC');
        $dbo->setQuery($query);

        $values = OrganizerHelper::executeQuery('loadColumn');
        if (empty($values)) {
            return [];
        }

        $options = [];
        foreach ($values as $value) {
            $options[] = HTML::_('select.option', $value, $value);
        }

        return $options;
    }
}
