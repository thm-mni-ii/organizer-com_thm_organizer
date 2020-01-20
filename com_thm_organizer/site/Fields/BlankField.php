<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Form\FormField;
use Organizer\Helpers\Languages;

/**
 * Class creates text input.
 */
class BlankField extends FormField
{
	use Translated;

	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $type = 'Blank';

	/**
	 * The allowable maxlength of the field.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $maxLength;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.0
	 */
	protected function getInput()
	{
		if ($this->hint and $hint = trim($this->hint))
		{
			$hint = preg_match('/^[A-Z_]+$/', $hint) ?
				Languages::_("ORGANIZER_$hint") : htmlspecialchars($hint, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$hint = '';
		}

		$attributes = [
			(!$this->autocomplete or $this->autocomplete !== 'off') ?
				'' : "autocomplete=\"$this->autocomplete\"",
			$this->autofocus ? 'autofocus' : '',
			$this->class ? "class=\"$this->class\"" : '',
			$this->disabled ? 'disabled' : '',
			$hint ? "placeholder=\"$hint\"" : '',
			"id=\"$this->id\"",
			$this->maxLength ? 'maxlength="' . (int) $this->maxLength . '"' : '',
			"name=\"$this->name\"",
			!empty($this->onChange) ? "onChange=\"$this->onChange\"" : '',
			$this->pattern ? 'pattern="' . $this->pattern . '"' : '',
			$this->readonly ? 'readonly' : '',
			$this->required ? 'required aria-required="true"' : '',
			$this->spellcheck ? '' : 'spellcheck="false"',
			'type="text"',
			'value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"'
		];

		return '<input ' . implode(' ', $attributes) . '/>';
	}
}
