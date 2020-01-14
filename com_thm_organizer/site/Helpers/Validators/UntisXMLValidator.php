<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Models\ScheduleXML;

/**
 * Ensures that Helpers which validate Schedule XML Export files have standardized functions.
 */
interface UntisXMLValidator
{
	/**
	 * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
	 *
	 * @param   ScheduleXML &$model    the validating schedule model
	 * @param   string       $untisID  the id of the resource in Untis
	 *
	 * @return void modifies the model, setting the id property of the resource
	 */
	public static function setID(&$model, $untisID);

	/**
	 * Checks whether XML node has the expected structure and required
	 * information
	 *
	 * @param   ScheduleXML &$model  the validating schedule model
	 * @param   object &     $node   the node to be validated
	 *
	 * @return void
	 */
	public static function validate(&$model, &$node);
}
