<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Organizer\Tables\Fields as FieldsTable;

/**
 * Provides general functions for room type access checks, data retrieval and display.
 */
class Fields extends ResourceHelper
{
	/**
	 * Returns the color value associated with the field.
	 *
	 * @param   int  $fieldID  the id of the field
	 *
	 * @return string the hexadecimal color value associated with the field
	 */
	public static function getColor($fieldID)
	{
		$default = Input::getParams()->get('backgroundColor', '#f2f5f6');
		$table   = new FieldsTable;
		$exists  = $table->load($fieldID);
		if (!$exists or empty($table->colorID))
		{
			return $default;
		}

		return Colors::getColor($table->colorID);
	}

	/**
	 * Creates the display for a field item as used in a list view.
	 *
	 * @param   int  $fieldID  the field id
	 *
	 * @return string the HTML output of the field attribute display
	 */
	public static function getListDisplay($fieldID)
	{
		$table = new FieldsTable;

		$text    = '';
		$colorID = 0;
		if ($table->load($fieldID))
		{
			$textColumn = 'name_' . Languages::getTag();
			$text       = $table->$textColumn;
			$colorID    = $table->colorID;
		}

		return Colors::getListDisplay($text, $colorID);
	}
}
