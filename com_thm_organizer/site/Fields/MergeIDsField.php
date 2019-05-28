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

use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a two hidden fields for merging. One has the lowest selected id as its value, the other has all
 * other selected ids (comma separated) as its value.
 */
class MergeIDsField extends BaseField
{
    protected $type = 'MergeIDs';

    /**
     * Method to get the field input markup for a generic list.
     *
     * @return  string  The field input markup.
     */
    protected function getInput()
    {
        $selectedIDs = OrganizerHelper::getSelectedIDs();
        asort($selectedIDs);
        $values = implode(',', $selectedIDs);
        return '<input type="hidden" name="' . $this->name . '" value="' . $values . '"/>';
    }
}