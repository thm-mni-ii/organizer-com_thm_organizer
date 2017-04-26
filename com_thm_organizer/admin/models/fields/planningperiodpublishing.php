<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldPublishing
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class creates a form field for merging plan pool programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldPlanningPeriodPublishing extends JFormField
{
	/**
	 * @var  string
	 */
	protected $type = 'planningPeriodPublishing';

	/**
	 * Returns a select box where resource attributes can be selected
	 *
	 * @return  string  the HTML select box
	 */
	protected function getInput()
	{
		$dbo         = JFactory::getDbo();
		$periodQuery = $dbo->getQuery(true);
		$periodQuery->select('id, name')->from('#__thm_organizer_planning_periods')->order("startDate ASC");
		$dbo->setQuery($periodQuery);

		try
		{
			$periods = $dbo->loadAssocList('id');
		}
		catch (Exception $exc)
		{
			return '';
		}

		if (empty($periods))
		{
			return '';
		}

		$poolID    = JFactory::getApplication()->input->getInt('id');
		$poolQuery = $dbo->getQuery(true);
		$poolQuery->select('planningPeriodID, published')
			->from('#__thm_organizer_plan_pool_publishing')
			->where("planPoolID = '$poolID'");
		$dbo->setQuery($poolQuery);

		try
		{
			$publishingEntries = $dbo->loadAssocList('planningPeriodID');
		}
		catch (Exception $exc)
		{
			return '';
		}

		$return = '<div class="publishing-container">';
		foreach ($periods as $period)
		{
			$pID   = $period['id'];
			$pName = $period['name'];

			$return .= '<div class="period-container">';
			$return .= '<div class="period-label">' . $pName . "</div>";
			$return .= '<div class="period-input">';
			$return .= '<select id="jform_publishing_' . $pID . '" name="jform[publishing][' . $pID . ']" class="chzn-color-state">';

			// Implicitly (new) and explicitly published entries
			if (!isset($publishingEntries[$period['id']]) OR $publishingEntries[$period['id']]['published'])
			{
				$return .= '<option value="1" selected="selected">' . JText::_('JYES') . '</option>';
				$return .= '<option value="0">' . JText::_('JNO') . '</option>';
			}
			else
			{
				$return .= '<option value="1">' . JText::_('JYES') . '</option>';
				$return .= '<option value="0" selected="selected">' . JText::_('JNO') . '</option>';
			}

			$return .= '</select>';
			$return .= '</div>';
			$return .= '</div>';
		}
		$return .= '</div>';

		return $return;
	}
}
