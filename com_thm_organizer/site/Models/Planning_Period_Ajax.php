<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Planning_Periods;

/**
 * Class provides planning period options for a given department/program. Called from the room statistics view.
 */
class Planning_Period_Ajax extends BaseModel
{
    /**
     * Gets the pool options as a string
     *
     * @return string the concatenated plan pool options
     */
    public function getOptions()
    {
        $planningPeriods = Planning_Periods::getPlanningPeriods();
        $options         = [];

        foreach ($planningPeriods as $planningPeriod) {
            $shortSD = Dates::formatDate($planningPeriod['startDate']);
            $shortED = Dates::formatDate($planningPeriod['endDate']);

            $option['value'] = $planningPeriod['id'];
            $option['text']  = "{$planningPeriod['name']} ($shortSD - $shortED)";
            $options[]       = $option;
        }

        return json_encode($options);
    }
}
