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
use Joomla\CMS\Form\FormHelper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

FormHelper::loadFieldClass('list');

/**
 * Class creates a generalized select box for selection of a single id column value among those already selected.
 */
class MergeAssociationsField extends ListField
{
    /**
     * @var  string
     */
    protected $type = 'MergeAssociations';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $selectedIDs = OrganizerHelper::getSelectedIDs();
        $valueColumn = $this->getAttribute('name');
        if (empty($selectedIDs) or empty($valueColumn)) {
            return [];
        }

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $textColumn  = $this->resolveTextColumn($query);
        if (empty($textColumn)) {
            return [];
        }

        $query->select("DISTINCT $valueColumn AS value, $textColumn AS text")
            ->order('text ASC');

        // 1 => table, 2 => alias, 4 => conditions
        $pattern = '/([a-z_]+) AS ([a-z]+)( ON ([a-z]+\.[A-Za-z]+ = [a-z]+\.[A-Za-z]+))?/';
        $from    = $this->getAttribute('from', '');

        $validFrom = preg_match($pattern, $from, $parts);
        if (!$validFrom) {
            return [];
        }

        $alias = $parts[2];
        $query->from("#__thm_organizer_$from")
            ->where("$alias.id IN ( '" . implode("', '", $selectedIDs) . "' )");

        $innerJoins = explode(',', $this->getAttribute('innerJoins', ''));

        foreach ($innerJoins as $innerJoin) {
            $validJoin = preg_match($pattern, $innerJoin, $parts);
            if (!$validJoin) {
                return [];
            }
            $query->innerJoin("#__thm_organizer_$innerJoin");
        }

        $dbo->setQuery($query);

        $valuePairs = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($valuePairs)) {
            return [];
        }

        $options = [];
        foreach ($valuePairs as $valuePair) {
            $options[] = HTML::_('select.option', $valuePair['value'], $valuePair['text']);
        }

        return empty($options) ? [] : $options;
    }

    /**
     * Resolves the textColumns for localization and concatenation of column names
     *
     * @param object &$query the query object by reference is an optimization, not a necessity
     *
     * @return string  the string to use for text selection
     */
    private function resolveTextColumn(&$query)
    {
        $textColumn  = $this->getAttribute('textcolumn', '');
        $textColumns = explode(',', $textColumn);
        $localized   = $this->getAttribute('localized', false);

        if ($localized) {
            $textColumns[0] = $textColumns[0] . '_' . Languages::getShortTag();
        }

        $glue = $this->getAttribute('glue');

        if (count($textColumns) === 1 or empty($glue)) {
            return $textColumns[0];
        }

        return '( ' . $query->concatenate($textColumns, $glue) . ' )';
    }
}
