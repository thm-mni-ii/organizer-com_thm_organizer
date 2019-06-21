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
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a select box for plan programs.
 */
class CategoriesField extends OptionsField
{
    use DepartmentFilters;

    /**
     * @var  string
     */
    protected $type = 'Categories';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT cat.id AS value, cat.name AS text')
            ->from('#__thm_organizer_categories AS cat')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = cat.id')
            ->order('text ASC');

        $access = $this->getAttribute('access');
        if (!empty($access)) {
            $this->addDeptAccessFilter($query, 'dr', $access);
        }

        $this->addDeptSelectionFilter($query, 'dr');

        if ($this->getAttribute('participant') === '1') {
            $query->innerJoin('#__thm_organizer_participants AS part ON part.categoryID = cat.id');
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
