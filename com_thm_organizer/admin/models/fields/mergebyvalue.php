<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        JFormFieldProgramID
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class creates a form field for subject-degree program association
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class JFormFieldMergeByValue extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'mergeByValue';

	/**
	 * Returns a select box where resource attributes can be selected
	 *
	 * @return  array the options for the select box
	 */
	public function getOptions()
	{
		$input       = JFactory::getApplication()->input;
		$selectedIDs = $input->get('cid', [], 'array');
		$column      = $this->getAttribute('name');
		$resource    = str_replace('_merge', '', $input->get('view')) . 's';

		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("DISTINCT $column AS value, $column AS text")->from("#__thm_organizer_$resource");
		$query->where("id IN ( '" . implode("', '", $selectedIDs) . "' )");
		$query->order('text ASC');
		$dbo->setQuery((string) $query);

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
