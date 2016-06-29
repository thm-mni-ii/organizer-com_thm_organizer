<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        JFormFieldGenericList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
JFormHelper::loadFieldClass('list');

/**
 * Class loads a list of of entries for selection
 *
 * @category    Joomla.Component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
class JFormFieldGenericList extends JFormFieldList
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	public $type = 'genericlist';

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
		$textColumn  = $this->resolveText($query);

		$query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
		$this->setFrom($query);
		$query->order("text ASC");
		$dbo->setQuery((string) $query);

		try
		{
			$resources = $dbo->loadAssocList();
			$options   = array();
			foreach ($resources as $resource)
			{
				// Removes glue from the end of entries
				$glue = $this->getAttribute('glue', '');
				if (!empty($glue))
				{
					$glueSize = strlen($glue);
					$textSize = strlen($resource['text']);
					if (strpos($resource['text'], $glue) == $textSize - $glueSize)
					{
						$resource['text'] = str_replace($glue, '', $resource['text']);
					}
				}

				$options[$resource['text']] = JHtml::_('select.option', $resource['value'], $resource['text']);
			}
			$this->setValueParameters($options);

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
	 * @param   object &$query the query object
	 *
	 * @return  string  the string to use for text selection
	 */
	private function resolveText(&$query)
	{
		$textColumn  = $this->getAttribute('textColumn');
		$textColumns = explode(',', $textColumn);

		$localized = $this->getAttribute('localized', false);
		if ($localized)
		{
			/** @noinspection PhpIncludeInspection */
			require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
			$tag = THM_OrganizerHelperLanguage::getShortTag();
			foreach ($textColumns as $key => $value)
			{
				$textColumns[$key] = $value . '_' . $tag;
			}
		}
		$glue = $this->getAttribute('glue');

		if (count($textColumns) === 1 OR empty($glue))
		{
			return $textColumns[0];
		}

		return '( ' . $query->concatenate($textColumns, $glue) . ' )';
	}

	/**
	 * Resolves the textColumns for concatenated values
	 *
	 * @param   object &$query the query object
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

	/**
	 * Sets value oriented parameters from component settings
	 *
	 * @param   array &$options the input options
	 *
	 * @return  void  sets option values
	 */
	private function setValueParameters(&$options)
	{
		$valueParameter = $this->getAttribute('valueParameter', '');
		if ($valueParameter === '')
		{
			return;
		}
		$valueParameters     = explode(',', $valueParameter);
		$componentParameters = JComponentHelper::getParams(JFactory::getApplication()->input->get('option'));
		foreach ($valueParameters AS $parameter)
		{
			$componentParameter = $componentParameters->get($parameter);
			if (empty($componentParameter))
			{
				continue;
			}
			$options[$componentParameter] = JHtml::_('select.option', $componentParameter, $componentParameter);
		}
		ksort($options);
	}
}
