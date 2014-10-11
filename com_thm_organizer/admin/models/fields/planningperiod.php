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
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
/**
 * Class loads a list of fields for selection
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldContentCategory extends JFormField
{
    /**
     * Type
     *
     * @var    String
     */
    public $type = 'planningperiod';

    /**
     * Method to get the field options for category
     * Use the extension attribute in a form to specify the.specific extension for
     * which categories should be displayed.
     * Use the show_root attribute to specify whether to show the global category root in the list.
     *
     * @return  array    The field option objects.
     */
    protected function getInput()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('DISTINCT semestername AS text, semestername AS value');
        $query->from('#__thm_organizer_schedules');
        $query->order('semestername ASC');
        $dbo->setQuery((string) $query);

        try
        {
            $planningPeriods = $dbo->loadAssocList();
            $options = array();
            $options[] = '<option value="">COM_THM_ORGANIZER_FILTER_PLANNING_PERIOD</option>';
            $options[] = '<option value="">COM_THM_ORGANIZER_FILTER_ALL</option>';
            foreach ($planningPeriods as $pPeriod)
            {
                $options[] = JHtml::_('select.options', $pPeriod['value'], $pPeriod['text']);
            }
            return array_merge(parent::getOptions(), $options);
        }
        catch (Exception $exc)
        {
            return parent::getOptions();
        }
    }

}
