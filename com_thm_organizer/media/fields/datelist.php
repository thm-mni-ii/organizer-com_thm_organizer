<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldDateList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class loads a list of date fields for selection
 *
 * @category    Joomla.Component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldDateList extends JFormFieldList
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	public $type = 'datelist';

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$dbo   = JFactory::getDbo();
		$query = $dbo->getQuery(true);

		$valueColumn = $this->getAttribute('valueColumn');
		$textColumn  = $this->getAttribute('textColumn');

		$query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
		$this->setFrom($query);
		$query->order("text ASC");
		$dbo->setQuery((string) $query);

		try
		{
			$resources = $dbo->loadAssocList();
			$options   = array();
			$params    = JComponentHelper::getParams('com_thm_organizer');
			$type      = $this->getAttribute('format');
			$format    = $type == 'time' ? $params->get('timeFormat', 'H:i') : $params->get('dateFormat', 'd.m.Y');
			foreach ($resources as $resource)
			{
				$text      = date($format, strtotime($resource['text']));
				$options[] = JHtml::_('select.option', $resource['value'], $text);
			}

			return array_merge(parent::getOptions(), $options);
		}
		catch (Exception $exc)
		{
			return parent::getOptions();
		}
	}

	/**
	 * Resolves the textColumns for concatenated values
	 *
	 * @param object &$query the query object
	 *
	 * @return  string  the string to use for text selection
	 */
	private function setFrom(&$query)
	{
		$tableParameters = $this->getAttribute('table');
		$tables          = explode(',', $tableParameters);

		$query->from("#__{$tables[0]}");
		$count = count($tables);
		if ($count === 1)
		{
			return;
		}

		for ($index = 1; $index < $count; $index++)
		{
			$query->innerjoin("#__{$tables[$index]}");
		}
	}
}
