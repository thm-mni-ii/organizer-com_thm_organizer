<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldLocalizedList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');
jimport('thm_core.helpers.corehelper');

/**
 * Class loads a list of of localized entries for selection
 *
 * @category    Joomla.Component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldLocalizedList extends JFormFieldList
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'localizedlist';

    /**
     * Method to get the field options for category
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return  array  The field option objects.
     */
    protected function getOptions()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $tag = THM_CoreHelper::getLanguageShortTag();
        $valueColumn = $this->getAttribute('valueColumn') . "_$tag";
        $textColumn = $this->getAttribute('textColumn') . "_$tag";

        $query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
        $this->setFrom($query);
        $query->order("text ASC");
        $dbo->setQuery((string) $query);

        try
        {
            $resources = $dbo->loadAssocList();
            $options = array();
            foreach ($resources as $resource)
            {
                $options[] = JHtml::_('select.option', $resource['value'], $resource['text']);
            }
            return array_merge(parent::getOptions(), $options);
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return parent::getOptions();
        }
    }

    /**
     * Resolves the textColumns for concatenated values
     *
     * @param   object  &$query  the query object
     *
     * @return  string  the string to use for text selection
     */
    private function setFrom(&$query)
    {
        $tableParameter = $this->getAttribute('table');
        $tables = explode(',', $tableParameter);
        $count = count($tables);
        if ($count === 1)
        {
            $query->from("#__$tableParameter");
            return;
        }

        $query->from("#__{$tables[0]}");
        for ($index = 1; $index < $count; $index++)
        {
            $query->innerjoin("#__{$tables[$index]}");
        }
    }
}
