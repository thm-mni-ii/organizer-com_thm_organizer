<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Provides functions for XML description validation and modeling.
 */
class Descriptions implements UntisXMLValidator
{
	/**
	 * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
	 *
	 * @param   Schedules &$model     the validating schedule model
	 * @param   string     $untisID   the id of the resource in Untis
	 * @param   string     $typeFlag  the flag identifying the categorization resource
	 *
	 * @return void modifies the model, setting the id property of the resource
	 */
	public static function setID(&$model, $untisID, $typeFlag = '')
	{
		$error    = 'THM_ORGANIZER_';
		$resource = '';
		switch ($typeFlag)
		{
			case 'f':
				$error    .= 'FIELD_INVALID';
				$resource = 'Fields';

				break;
			case 'r':
				$error    .= 'ROOMTYPE_INVALID';
				$resource = 'Roomtypes';

				break;
			case 'u':
				$error    .= 'METHOD_INVALID';
				$resource = 'Methods';

				break;
		}

		$table = OrganizerHelper::getTable($resource);

		// These are set by the administrator, so there is no case for saving a new resource on upload.
		if ($table->load(['untisID' => $untisID]))
		{
			$property                   = strtolower($resource);
			$model->$property->$untisID = $table->id;
		}
		else
		{
			$model->errors[] = sprintf(Languages::_($error), $untisID);
		}

		return;
	}

	/**
	 * Checks whether XML node has the expected structure and required
	 * information
	 *
	 * @param   Schedules &  $model  the validating schedule model
	 * @param   object &     $node   the node to be validated
	 *
	 * @return void
	 */
	public static function validate(&$model, &$node)
	{
		$untisID = str_replace('DS_', '', trim((string) $node[0]['id']));
		$name    = trim((string) $node->longname);

		if (empty($name))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_DESCRIPTION_NAME_MISSING'), $untisID);

			return;
		}

		$typeFlag   = strtolower(trim((string) $node->flags));
		$validFlags = ['f', 'r', 'u'];

		if (empty($typeFlag))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_DESCRIPTION_TYPE_MISSING'), $name, $untisID);

			return;
		}

		if (!in_array($typeFlag, $validFlags))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_DESCRIPTION_TYPE_INVALID'), $name, $untisID);

			return;
		}

		self::setID($model, $untisID, $typeFlag);
	}
}
