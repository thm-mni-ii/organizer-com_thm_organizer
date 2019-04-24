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

require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/planning_periods.php';

/**
 * Class provides planning period options for a given department/program. Called from the room statistics view.
 */
class THM_OrganizerModelPlanning_Period_Ajax extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    /**
     * Gets the pool options as a string
     *
     * @return string the concatenated plan pool options
     */
    public function getOptions()
    {
        $planningPeriods = THM_OrganizerHelperPlanning_Periods::getPlanningPeriods();
        $options         = [];

        foreach ($planningPeriods as $planningPeriod) {
            $shortSD = THM_OrganizerHelperDate::formatDate($planningPeriod['startDate']);
            $shortED = THM_OrganizerHelperDate::formatDate($planningPeriod['endDate']);

            $option['value'] = $planningPeriod['id'];
            $option['text']  = "{$planningPeriod['name']} ($shortSD - $shortED)";
            $options[]       = $option;
        }

        return json_encode($options);
    }
}
