<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelTeacher_Ajax
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Class provides methods for building a model of the curriculum in JSON format
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelPlanning_Period_Ajax extends JModelLegacy
{
	/**
	 * Gets the pool options as a string
	 *
	 * @param bool $short whether or not the options should use abbreviated names
	 *
	 * @return string the concatenated plan pool options
	 */
	public function getOptions()
	{
		$planningPeriods = THM_OrganizerHelperPlanning_Periods::getPlanningPeriods();
		$options         = [];

		foreach ($planningPeriods as $planningPeriodID => $planningPeriod)
		{
			$shortSD = THM_OrganizerHelperComponent::formatDate($planningPeriod['startDate']);
			$shortED = THM_OrganizerHelperComponent::formatDate($planningPeriod['endDate']);

			$option['value'] = $planningPeriod['id'];
			$option['text']  = "{$planningPeriod['name']} ($shortSD - $shortED)";
			$options[]       = $option;
		}

		return json_encode($options);
	}
}
