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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class replaces form field type sql by using Joomla's database objects to avoid database language dependency. Both the
 * value and the text are localized.
 */
class LocalizedListField extends ListField
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'LocalizedList';

    /**
     * Method to get the field options for category.
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return array  The field option objects.
     */
    protected function getOptions()
    {
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);

        $tag         = Languages::getShortTag();
        $valueColumn = $this->getAttribute('valuecolumn') . "_$tag";
        $textColumn  = $this->getAttribute('textcolumn') . "_$tag";

        $query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
        $this->setFrom($query);
        $query->order('text ASC');
        $dbo->setQuery($query);

        $defaultOptions = parent::getOptions();
        $resources      = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($resources)) {
            return $defaultOptions;
        }

        $options = [];
        foreach ($resources as $resource) {
            $options[] = HTML::_('select.option', $resource['value'], $resource['text']);
        }

        return array_merge($defaultOptions, $options);
    }

    /**
     * Resolves the textColumns for concatenated values
     *
     * @param object &$query the query object
     *
     * @return void modifies the query object
     */
    private function setFrom(&$query)
    {
        $tableParameter = $this->getAttribute('table');
        $tables         = explode(',', $tableParameter);
        $count          = count($tables);
        if ($count === 1) {
            $query->from("#__$tableParameter");

            return;
        }

        $query->from("#__{$tables[0]}");
        for ($index = 1; $index < $count; $index++) {
            $query->innerjoin("#__{$tables[$index]}");
        }
    }
}
