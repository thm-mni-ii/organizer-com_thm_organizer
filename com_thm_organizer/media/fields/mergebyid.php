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

use \THM_OrganizerHelperHTML as HTML;

JFormHelper::loadFieldClass('list');

/**
 * Class creates a generalized select box for selection of a single id column value among those already selected.
 */
class JFormFieldMergeByID extends JFormFieldList
{
    /**
     * @var  string
     */
    protected $type = 'mergeByID';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $input       = THM_OrganizerHelperComponent::getInput();
        $selectedIDs = $input->get('cid', [], 'array');
        $valueColumn = $this->getAttribute('name');
        $tables      = explode(',', $this->getAttribute('tables'));
        $tableAlias  = '';

        $dbo        = JFactory::getDbo();
        $query      = $dbo->getQuery(true);
        $textColumn = $this->resolveText($query);
        $query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
        $query->from("#__{$tables[0]}");
        $count = count($tables);

        if ($count > 1) {
            $baseParts  = explode(' AS ', $tables[0]);
            $tableAlias .= $baseParts[1] . '.';
            for ($index = 1; $index < $count; $index++) {
                $query->leftJoin("#__{$tables[$index]}");
            }
        }

        $query->where("{$tableAlias}id IN ( '" . implode("', '", $selectedIDs) . "' )");
        $query->order('text ASC');
        $dbo->setQuery($query);

        $defaultOptions = parent::getOptions();
        $values         = THM_OrganizerHelperComponent::executeQuery('loadAssocList');
        if (empty($values)) {
            return $defaultOptions;
        }

        $options = [];
        foreach ($values as $value) {
            if (!empty($value['value'])) {
                $options[] = HTML::_('select.option', $value['value'], $value['text']);
            }
        }

        return empty($options) ? $defaultOptions : $options;
    }

    /**
     * Resolves the textColumns for concatenated values
     *
     * @param object &$query the query object
     *
     * @return string  the string to use for text selection
     */
    private function resolveText(&$query)
    {
        $textColumn  = $this->getAttribute('textcolumn');
        $textColumns = explode(',', $textColumn);
        $localized   = $this->getAttribute('localized', false);

        if ($localized) {
            require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
            $tag = THM_OrganizerHelperLanguage::getShortTag();
            foreach ($textColumns as $key => $value) {
                $textColumns[$key] = $value . '_' . $tag;
            }
        }

        $glue = $this->getAttribute('glue');

        if (count($textColumns) === 1 or empty($glue)) {
            return $textColumns[0];
        }

        return '( ' . $query->concatenate($textColumns, $glue) . ' )';
    }
}
