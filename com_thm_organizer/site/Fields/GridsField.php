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

use Organizer\Helpers\Campuses;
use Organizer\Helpers\Grids;
use Organizer\Helpers\Input;

/**
 * Class creates a select box for (subject) pools.
 */
class GridsField extends OptionsField
{
	/**
	 * @var  string
	 */
	protected $type = 'Grids';

	/**
	 * Method to get the field input markup for a generic list.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if (empty($this->value) and $campusID = Input::getParams()->get('campusID'))
		{
			$this->value = Campuses::getGridID($campusID);
		}

		return parent::getInput();
	}

	/**
	 * Returns an array of pool options
	 *
	 * @return array  the pool options
	 */
	protected function getOptions()
	{
		$options  = parent::getOptions();
		$campuses = Grids::getOptions();

		return array_merge($options, $campuses);
	}
}
