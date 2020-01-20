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

use Organizer\Helpers\Buildings;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\ResourceHelper;
use Organizer\Tables\Rooms as RoomsTable;
use stdClass;

/**
 * Class provides general functions for retrieving room data.
 */
class Rooms extends ResourceHelper implements UntisXMLValidator
{
	/**
	 * Retrieves the resource id using the Untis ID. Creates the resource id if unavailable.
	 *
	 * @param   Schedules &$model    the validating schedule model
	 * @param   string     $untisID  the id of the resource in Untis
	 *
	 * @return void modifies the model, setting the id property of the resource
	 */
	public static function setID(&$model, $untisID)
	{
		$room  = $model->rooms->$untisID;
		$table = new RoomsTable;

		if ($table->load(['untisID' => $room->untisID]))
		{
			$altered = false;
			foreach ($room as $key => $value)
			{
				if (property_exists($table, $key) and empty($table->$key) and !empty($value))
				{
					$table->set($key, $value);
					$altered = true;
				}
			}

			if ($altered)
			{
				$table->store();
			}
		}
		else
		{
			$table->save($room);
		}
		$model->rooms->$untisID->id = $table->id;

		return;
	}

	/**
	 * Checks whether nodes have the expected structure and required information
	 *
	 * @param   Schedules &$model  the validating schedule model
	 *
	 * @return void modifies &$model
	 */
	public static function setWarnings(&$model)
	{
		if (!empty($model->warnings['REX']))
		{
			$warningCount = $model->warnings['REX'];
			unset($model->warnings['REX']);
			$model->warnings[] = sprintf(Languages::_('ORGANIZER_ROOM_EXTERNAL_IDS_MISSING'), $warningCount);
		}

		if (!empty($model->warnings['RT']))
		{
			$warningCount = $model->warnings['RT'];
			unset($model->warnings['RT']);
			$model->warnings[] = sprintf(Languages::_('ORGANIZER_ROOMTYPES_MISSING'), $warningCount);
		}
	}

	/**
	 * Checks whether room nodes have the expected structure and required
	 * information
	 *
	 * @param   Schedules &$model     the validating schedule model
	 * @param   object    &$roomNode  the room node to be validated
	 *
	 * @return void
	 */
	public static function validate(&$model, &$roomNode)
	{
		$internalID = strtoupper(str_replace('RM_', '', trim((string) $roomNode[0]['id'])));

		if ($externalID = strtoupper(trim((string) $roomNode->external_name)))
		{
			$untisID = $externalID;
		}
		else
		{
			$model->warnings['REX'] = empty($model->warnings['REX']) ? 1 : $model->warnings['REX']++;
			$untisID                = $internalID;
		}

		$roomTypeID  = str_replace('DS_', '', trim((string) $roomNode->room_description[0]['id']));
		$invalidType = (empty($roomTypeID) or empty($model->roomtypes->$roomTypeID));
		if ($invalidType)
		{
			$model->warnings['RT'] = empty($model->warnings['RT']) ? 1 : $model->warnings['RT']++;
			$roomTypeID            = null;
		}
		else
		{
			$roomTypeID = $model->roomtypes->$roomTypeID;
		}

		$capacity      = (int) $roomNode->capacity;
		$buildingID    = null;
		$buildingREGEX = Input::getParams()->get('buildingRegex');

		if (!empty($buildingREGEX) and preg_match("/$buildingREGEX/", $untisID, $matches))
		{
			$buildingID = Buildings::getID($matches[1]);
		}

		$room             = new stdClass;
		$room->buildingID = $buildingID;
		$room->capacity   = $capacity;
		$room->name       = $untisID;
		$room->roomtypeID = $roomTypeID;
		$room->untisID    = $untisID;

		$model->rooms->$internalID = $room;
		self::setID($model, $internalID);
	}
}
