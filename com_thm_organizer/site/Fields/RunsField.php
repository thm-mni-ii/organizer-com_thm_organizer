<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('subform');

/**
 * Class loads multiple/repeatable period blocks from database and make it possible to advance them.
 * This needs an own form field to load the values, maybe because the dates are saved as json string.
 */
class RunsField extends \JFormFieldSubform
{
	/**
	 * Type
	 *
	 * @var    String
	 */
	protected $type = 'Runs';

	/**
	 * Method to get the multiple field input of the loaded Runs Section
	 *
	 * @return string  The field input markup.
	 */
	protected function getInput()
	{
		$this->value = isset($this->value['runs']) ? $this->value['runs'] : [];

		return parent::getInput();
	}
}
