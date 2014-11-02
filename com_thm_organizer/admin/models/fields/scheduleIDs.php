<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldScheduleIDs
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class fills the id and otherid values for a merge form
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldScheduleIDs extends JFormField
{
    protected $type = 'scheduleIDs';

    /**
     * Returns a hidden in put field
     *
     * @return  string
     */
    public function getInput()
    {
        $selectedIDs = implode(',', JFactory::getApplication()->input->get('cid', array(), 'array'));
        return '<input name="jform[scheduleIDs]" type="hidden" value="' . $selectedIDs . '">';

    }

    /**
     * Returns an empty string to override the joomla handling
     *
     * @return  string
     */
    public function getLabel()
    {

        return '';
    }
}
