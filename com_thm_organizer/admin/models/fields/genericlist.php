<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldFields
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class loads a list of fields for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldGenericList extends JFormFieldList
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'genericlist';

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
        $attributes = $this->element;
        $valueColumn = $this->element->xpath("[@value='Large']")['@value'];
        $textColumn = $this->element['@text'];
        $table = $this->element['@table'];

        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
        $query->from("#__$table");
        $query->order("$textColumn ASC");
        $dbo->setQuery((string) $query);
        echo "<pre>" . print_r((string) $query, true) . "</pre>";

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
            return parent::getOptions();
        }
    }

}
