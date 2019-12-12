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

use Organizer\Helpers\Programs;

/**
 * Class creates a select box for (degree) programs.
 */
class ProgramsField extends OptionsField
{
	/**
	 * @var  string
	 */
	protected $type = 'Programs';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options  = parent::getOptions();
		$programs = Programs::getOptions($this->getAttribute('access', ''));

		return array_merge($options, $programs);
	}
}
