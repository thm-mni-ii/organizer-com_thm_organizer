<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

/**
 * Ensures that helpers that reference selectable items offer the getOptions function.
 */
interface Selectable
{
	/**
	 * Retrieves the selectable options for the resource.
	 *
	 * @return array the available options
	 */
	public static function getOptions();

	/**
	 * Retrieves the resource items.
	 *
	 * @return array the available resources
	 */
	public static function getResources();
}
