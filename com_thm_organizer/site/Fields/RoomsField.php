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

use Organizer\Helpers\Rooms;

/**
 * Class creates a form field for room selection.
 */
class RoomsField extends OptionsField
{
	/**
	 * @var  string
	 */
	protected $type = 'Rooms';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();
		$rooms   = Rooms::getOptions();

		return array_merge($options, $rooms);
	}
}
