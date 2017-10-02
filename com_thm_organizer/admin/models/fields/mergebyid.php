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
class JFormFieldMergeByID extends JFormFieldList
{
	/**
	 * @var  string
	 */
	protected $type = 'mergeByID';

	/**
	 * Returns a select box where resource attributes can be selected
	 *
	 * @return  array the options for the select box
	 */
	public function getOptions()
	{
		$input       = JFactory::getApplication()->input;
		$selectedIDs = $input->get('cid', [], 'array');
		$valueColumn = $this->getAttribute('name');
		$tables      = explode(',', $this->getAttribute('tables'));
		$tableAlias  = '';

		$dbo        = JFactory::getDbo();
		$query      = $dbo->getQuery(true);
		$textColumn = $this->resolveText($query);
		$query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
		$query->from("#__{$tables[0]}");
		$count = count($tables);

		if ($count > 1)
		{
			$baseParts  = explode(' AS ', $tables[0]);
			$tableAlias .= $baseParts[1] . '.';
			for ($index = 1; $index < $count; $index++)
			{
				$query->leftJoin("#__{$tables[$index]}");
			}
		}

		$query->where("{$tableAlias}id IN ( '" . implode("', '", $selectedIDs) . "' )");
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

	/**
	 * Resolves the textColumns for concatenated values
	 *
	 * @param object &$query the query object
	 *
	 * @return  string  the string to use for text selection
	 */
	private function resolveText(&$query)
	{
		$textColumn  = $this->getAttribute('textcolumn');
		$textColumns = explode(',', $textColumn);
		$localized   = $this->getAttribute('localized', false);

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
}
