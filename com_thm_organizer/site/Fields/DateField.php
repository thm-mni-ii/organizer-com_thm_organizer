<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Form\FormField;
use Organizer\Helpers\Dates;
use Organizer\Helpers\Languages;

/**
 * Class creates text input.
 */
class DateField extends FormField
{
	use Translated;

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Date';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		if ($this->value)
		{
			$value = Dates::standardizeDate($this->value);
		}
		else
		{
			$value = (isset($this->empty) and $this->empty == false) ? Dates::standardizeDate() : '';
		}

		$attributes = [
			$this->autofocus ? 'autofocus' : '',
			$this->class ? "class=\"$this->class\"" : '',
			$this->disabled ? 'disabled' : '',
			"id=\"$this->id\"",
			"name=\"$this->name\"",
			!empty($this->onChange) ? "onChange=\"$this->onChange\"" : '',
			$this->readonly ? 'readonly' : '',
			$this->required ? 'required aria-required="true"' : '',
			'type="date"',
			'value="' . $value . '"'
		];

		return '<input ' . implode(' ', $attributes) . '/>';
	}
}
