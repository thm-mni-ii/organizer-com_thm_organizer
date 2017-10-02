<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldPlanProgramID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for merging plan pool programs
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldPlanProgramID extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'planProgramID';

	/**
	 * Returns a select box where resource attributes can be selected
	 *
	 * @return  array the options for the select box
	 */
	public function getOptions()
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("DISTINCT ppr.id AS value, ppr.name AS text");
		$query->from("#__thm_organizer_plan_programs AS ppr");
		$query->innerJoin("#__thm_organizer_plan_pools AS ppl ON ppl.programID = ppr.id");

		$selectedIDs = JFactory::getApplication()->input->get('cid', [], 'array');
		$query->where("ppl.id IN ( '" . implode("', '", $selectedIDs) . "' )");
		$query->order('text ASC');
		$dbo->setQuery($query);

		try
		{
			$values  = $dbo->loadAssocList();
			$options = [];
			foreach ($values as $value)
			{
				if (!empty($value['value']))
				{
					$options[] = JHtml::_('select.option', $value['value'], $value['text']);
				}
			}

			return count($options) ? $options : parent::getOptions();
		}
		catch (Exception $exc)
		{
			return parent::getOptions();
		}
	}
}
