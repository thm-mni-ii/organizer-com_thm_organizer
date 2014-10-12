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
class JFormFieldContentCategory extends JFormFieldList
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'contentcategory';

    /**
     * Method to get the field options for category
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return  array    The field option objects.
     */
    protected function getOptions()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT cc.id AS value, cc.title AS text');
        $query->from('#__categories AS cc');
        $query->innerJoin('#__thm_organizer_categories AS ec ON cc.id = ec.contentCatID');
        $query->order('cc.title ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $cCategories = $dbo->loadAssocList();
            $categoryOptions = array();
            foreach ($cCategories as $category)
            {
                $categoryOptions[] = JHtml::_('select.option', $category['value'], $category['text']);
            }
            return array_merge(parent::getOptions(), $categoryOptions);
        }
        catch (Exception $exc)
        {
            return parent::getOptions();
        }
    }

}
