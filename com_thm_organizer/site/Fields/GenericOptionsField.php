<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class replaces form field type sql by using Joomla's database objects to avoid database language dependency. While
 * the display text can be localized, the value cannot be.
 */
class GenericOptionsField extends OptionsField
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	public $type = 'GenericList';

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return string  The field input markup.
	 */
	protected function getInput()
	{
		$html = [];
		$attr = '';

		// Initialize some field attributes.
		$attr        .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr        .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr        .= $this->multiple ? ' multiple' : '';
		$attr        .= $this->required ? ' required aria-required="true"' : '';
		$attr        .= $this->autofocus ? ' autofocus' : '';
		$placeHolder = $this->getAttribute('placeholder', '');
		$attr        .= empty($placeHolder) ? '' : ' placeholder="' . Languages::_($placeHolder) . '"';

		$isReadOnly     = ($this->readonly == '1' or $this->readonly == 'true');
		$this->readonly = (string) $isReadOnly;
		$isDisabled     = ($this->disabled == '1' or $this->disabled == 'true');
		$this->disabled = (string) $isDisabled;
		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ($isReadOnly or $isDisabled)
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with hidden input(s) to store the value(s).
		if ($isReadOnly)
		{
			$html[] = HTML::_(
				'select.genericlist',
				$options,
				'',
				trim($attr),
				'value',
				'text',
				$this->value,
				$this->id
			);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$value  = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $value . '"/>';
				}
			}
			else
			{
				$value  = htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8');
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $value . '"/>';
			}
		}
		else // Create a regular list.
		{
			$html[] = HTML::_(
				'select.genericlist',
				$options,
				$this->name,
				trim($attr),
				'value',
				'text',
				$this->value,
				$this->id
			);
		}

		return implode($html);
	}

	/**
	 * Method to get the field options for category
	 * Use the extension attribute in a form to specify the.specific extension for
	 * which categories should be displayed.
	 * Use the show_root attribute to specify whether to show the global category root in the list.
	 *
	 * @return array  The field option objects.
	 */
	protected function getOptions()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$valueColumn = $this->getAttribute('valuecolumn');
		$textColumn  = $this->resolveText($query);

		$query->select("DISTINCT $valueColumn AS value, $textColumn AS text");
		$this->setFrom($query);
		$this->setWhere($query);
		$order = $this->getAttribute('order', 'text ASC');
		$query->order($order);
		$dbo->setQuery($query);

		$defaultOptions = parent::getOptions();
		$resources      = OrganizerHelper::executeQuery('loadAssocList');
		if (empty($resources))
		{
			return $defaultOptions;
		}

		$options = [];
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

			$options[$resource['text']] = HTML::_('select.option', $resource['value'], $resource['text']);
		}
		$this->setValueParameters($options);

		return array_merge($defaultOptions, $options);
	}

	/**
	 * Resolves the textColumns for concatenated values
	 *
	 * @param   object &$query  the query object
	 *
	 * @return string  the string to use for text selection
	 */
	private function resolveText(&$query)
	{
		$textColumn  = $this->getAttribute('textcolumn');
		$textColumns = explode(',', $textColumn);

		$localized = $this->getAttribute('localized', false);
		if ($localized)
		{
			$tag = Languages::getTag();
			foreach ($textColumns as $key => $value)
			{
				$textColumns[$key] = $value . '_' . $tag;
			}
		}
		$glue = $this->getAttribute('glue');

		if (count($textColumns) === 1 or empty($glue))
		{
			return $textColumns[0];
		}

		return '( ' . $query->concatenate($textColumns, $glue) . ' )';
	}

	/**
	 * Resolves the textColumns for concatenated values
	 *
	 * @param   object &$query  the query object
	 *
	 * @return void modifies the query as necessary
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
	 * @param   array &$options  the input options
	 *
	 * @return void  sets option values
	 */
	private function setValueParameters(&$options)
	{
		$valueParameter = $this->getAttribute('valueParameter', '');
		if ($valueParameter === '')
		{
			return;
		}
		$valueParameters     = explode(',', $valueParameter);
		$componentParameters = Input::getParams();
		foreach ($valueParameters as $parameter)
		{
			$componentParameter = $componentParameters->get($parameter);
			if (empty($componentParameter))
			{
				continue;
			}
			$options[$componentParameter] = HTML::_('select.option', $componentParameter, $componentParameter);
		}
		ksort($options);
	}

	/**
	 * Adds filter conditions
	 *
	 * @param   object &$query  the query object
	 *
	 * @return void modifies the query as necessary
	 */
	private function setWhere(&$query)
	{
		$rawConditions = $this->getAttribute('conditions');

		if (empty($rawConditions))
		{
			return;
		}

		$conditions = explode(',', $rawConditions);

		foreach ($conditions as $condition)
		{
			$query->where($condition);
		}
	}
}
