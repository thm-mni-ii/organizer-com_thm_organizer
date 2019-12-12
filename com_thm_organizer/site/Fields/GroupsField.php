<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Organizer\Helpers\Groups;

/**
 * Class creates a select box for plan programs.
 */
class GroupsField extends OptionsField
{

    /**
     * @var  string
     */
    protected $type = 'Groups';

    /**
     * Returns a select box where resource attributes can be selected
     *
     * @return array the options for the select box
     */
    protected function getOptions()
    {
        $options = parent::getOptions();
        $groups  = Groups::getOptions();

        return array_merge($options, $groups);
    }
}
