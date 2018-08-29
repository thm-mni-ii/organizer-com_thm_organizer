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

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class THM_OrganizerHelperPlan_Pools
{
    /**
     * Checks whether the given plan pool is associated with an allowed department
     *
     * @param array $ppIDs the id of the plan pool being checked
     *
     * @return bool  true if the plan pool is associated with an allowed department, otherwise false
     * @throws Exception
     */
    public static function allowEdit($ppIDs)
    {
        $user = JFactory::getUser();

        if (empty($user->id)) {
            return false;
        }

        if ($user->authorise('core.admin', "com_thm_organizer")) {
            return true;
        }

        $allowedDepartments = THM_OrganizerHelperComponent::getAccessibleDepartments('schedule');

        $dbo   = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_department_resources');

        foreach ($ppIDs as $ppID) {
            $query->clear('where');
            $query->where("poolID = '$ppID'")
                ->where("departmentID IN ('" . implode("', '", $allowedDepartments) . "')");

            $dbo->setQuery($query);

            try {
                // Only one positive per resource is necessary
                $poolID = $dbo->loadResult();
            } catch (Exception $exc) {
                JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                    'error');

                return false;
            }

            if (empty($poolID))
            {
                return false;
            }
        }

        return true;
    }
}
