<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldMergeID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class fills the id and other id values for a merge form
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldMergeID extends JFormField
{
    protected $type = 'mergeID';

    /**
     * Returns a hidden in put field
     *
     * @return string
     */
    public function getInput()
    {
        $selectedIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        asort($selectedIDs);
        $first  = array_shift($selectedIDs);
        $others = implode(',', $selectedIDs);
        if ($this->getAttribute('other') == 'true') {
            return '<input name="jform[otherIDs]" type="hidden" value="' . $others . '">';
        }

        return '<input name="jform[id]" type="hidden" value="' . $first . '">';
    }

    /**
     * Returns an empty string to override the joomla handling
     *
     * @return string
     */
    public function getLabel()
    {
        return '';
    }
}
