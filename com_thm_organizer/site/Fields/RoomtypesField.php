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

use Organizer\Helpers\Roomtypes;

/**
 * Class creates a form field for room type selection
 */
class RoomtypesField extends OptionsField
{
	/**
	 * @var  string
	 */
	protected $type = 'Roomtypes';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options   = parent::getOptions();
		$roomtypes = Roomtypes::getOptions();

		return array_merge($options, $roomtypes);
	}
}
