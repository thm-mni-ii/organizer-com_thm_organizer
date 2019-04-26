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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/OrganizerHelper.php';

/**
 * Class creates a two hidden fields for merging. One has the lowest selected id as its value, the other has all
 * other selected ids (comma separated) as its value.
 */
class JFormFieldMergeID extends \Joomla\CMS\Form\FormField
{
    protected $type = 'mergeID';

    /**
     * Returns a hidden in put field
     *
     * @return string
     */
    public function getInput()
    {
        $selectedIDs = OrganizerHelper::getInput()->get('cid', [], 'array');
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
