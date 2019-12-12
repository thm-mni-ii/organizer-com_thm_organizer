<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers\Validators;

use Joomla\CMS\Factory;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\ResourceHelper;
use Organizer\Tables\Units as UnitsTable;
use stdClass;

/**
 * Provides functions for XML unit validation and persistence.
 */
class Units extends ResourceHelper implements UntisXMLValidator
{
	/**
	 * Determines how the missing room attribute will be handled
	 *
	 * @param   Schedules &$model  the validating schedule model
	 *
	 * @return void adds a message to the model warnings array
	 */
	private static function createInvalidRoomsMessages(&$model)
	{
		foreach ($model->warnings['IIR'] as $untisID => $invalidRooms)
		{
			asort($invalidRooms);
			$invalidRooms = implode(', ', $invalidRooms);
			$pos          = strrpos(', ', $invalidRooms);
			if ($pos !== false)
			{
				$and          = Languages::_('THM_ORGANIZER_AND');
				$invalidRooms = substr_replace($invalidRooms, " $and ", $pos, strlen($invalidRooms));
			}

			$model->warnings[] = sprintf(
				Languages::_('THM_ORGANIZER_UNIT_ROOM_INCOMPLETE'),
				$untisID,
				$invalidRooms
			);
		}
		unset($model->warnings['IIR']);
	}

	/**
	 * Determines how the missing room attribute will be handled
	 *
	 * @param   Schedules &$model  the validating schedule model
	 *
	 * @return void adds a message to the model warnings array
	 */
	private static function createMissingRoomsMessages(&$model)
	{
		foreach ($model->warnings['IMR'] as $untisID => $DOWs)
		{
			foreach ($DOWs as $dow => $periods)
			{
				foreach ($periods as $periodNo => $missingDates)
				{
					if (count($missingDates) > 2)
					{
						$model->warnings[] = sprintf(
							Languages::_('THM_ORGANIZER_UNIT_ROOMS_MISSING'),
							$untisID,
							$dow,
							$periodNo
						);
						continue;
					}

					$dates = implode(', ', $missingDates);
					$pos   = strrpos(', ', $dates);
					if ($pos !== false)
					{
						$and   = Languages::_('THM_ORGANIZER_AND');
						$dates = substr_replace($dates, " $and ", $pos, strlen($dates));
					}

					$model->warnings[] = sprintf(
						Languages::_('THM_ORGANIZER_UNIT_ROOMS_MISSING'),
						$untisID,
						$dates,
						$periodNo
					);
				}

			}
		}
		unset($model->warnings['IMR']);
	}

	/**
	 * Gets the id for a named role.
	 *
	 * @param   string  $role  the role as specified in the schedule
	 *
	 * @return int the id of the role, defaults to 1
	 */
	private static function getRoleID($role)
	{
		if (empty($role) or is_numeric($role))
		{
			return 1;
		}

		$role         = strtoupper($role);
		$conditions[] = "UPPER(name_de) = '$role'";
		$conditions[] = "UPPER(name_en) = '$role'";
		$conditions[] = "UPPER(abbreviation_de) = '$role'";
		$conditions[] = "UPPER(abbreviation_en) = '$role'";
		$dbo          = Factory::getDbo();
		$query        = $dbo->getQuery(true);
		$query->select('id')
			->from('#__thm_organizer_roles')
			->where($conditions, 'OR');
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadResult', 1);
	}

	/**
	 * Adjusts the template ('occurrence' attribute) to the unit's actual dates.
	 *
	 * @param   Schedules &$model   the validating schedule model
	 * @param   object    &$node    the unit node
	 * @param   int        $unitID  the id of the unit being iterated
	 *
	 * @return mixed   array if valid, otherwise false
	 */
	private static function getFilteredOccurrences(&$model, &$node, $unitID)
	{
		$rawOccurrences = trim((string) $node->occurence);
		$unit           = $model->units->$unitID;

		// Increases the end value one day (Untis uses inclusive dates)
		$end = strtotime('+1 day', $unit->endDT);

		// 86400 is the number of seconds in a day 24 * 60 * 60
		$offset = floor(($unit->startDT - strtotime($model->schoolYear->startDate)) / 86400);
		$length = floor(($end - $unit->startDT) / 86400);

		$filteredOccurrences = substr($rawOccurrences, $offset, $length);

		// Change occurrences from a string to an array of the appropriate length for iteration
		return empty($filteredOccurrences) ? [] : str_split($filteredOccurrences);
	}

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
		$unit  = $model->units->$untisID;
		$table = new UnitsTable;

		if ($table->load(['departmentID' => $unit->departmentID, 'termID' => $unit->termID, 'untisID' => $untisID]))
		{
			$altered = false;

			foreach ($unit as $key => $value)
			{

				// Context based changes need no write protection.
				if (property_exists($table, $key))
				{
					$table->set($key, $value);
					$altered = true;
				}
			}

			if ($altered)
			{
				$table->set('delta', 'changed');
				$table->store();
			}
			elseif (!empty($table->delta))
			{
				$table->set('delta', '');
				$table->store();
			}

		}
		else
		{
			$table->set('delta', 'new');
			$table->save($unit);
		}

		$unit->id = $table->id;

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
		if (!empty($model->warnings['MID']))
		{
			$warningCount = $model->warnings['MID'];
			unset($model->warnings['MID']);
			$model->warnings[] = sprintf(Languages::_('THM_ORGANIZER_METHOD_ID_WARNING'), $warningCount);
		}

		if (!empty($model->warnings['IMR']))
		{
			self::createMissingRoomsMessages($model);
		}

		if (!empty($model->warnings['IIR']))
		{
			self::createInvalidRoomsMessages($model);
		}
	}

	/**
	 * Validates the subjectID and builds dependant structural elements
	 *
	 * @param   Schedules &  $model   the validating schedule model
	 * @param   object &     $node    the unit node
	 * @param   int          $unitID  the id of the unit being iterated
	 *
	 * @return bool  true on success, otherwise boolean false
	 */
	private static function validateEvent(&$model, &$node, $unitID)
	{
		$eventID = str_replace('SU_', '', trim((string) $node->lesson_subject[0]['id']));

		if (empty($eventID))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_EVENT_MISSING'), $unitID);

			return false;
		}

		if (empty($model->events->$eventID))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_EVENT_INVALID'), $unitID, $eventID);

			return false;
		}

		$model->units->$unitID->eventID = $model->events->$eventID->id;

		return true;
	}

	/**
	 * Validates the description
	 *
	 * @param   Schedules &$model   the validating schedule model
	 * @param   object    &$node    the unit node
	 * @param   int        $unitID  the id of the unit being iterated
	 *
	 * @return void modifies object properties
	 */
	private static function validateMethod(&$model, &$node, $unitID)
	{
		$methodID = trim((string) $node->lesson_description);
		if (empty($methodID))
		{
			$model->warnings['MID'] = empty($model->warnings['MID']) ? 1 : $model->warnings['MID']++;

			return true;
		}

		if (empty($model->methods->$methodID))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_METHOD_INVALID'), $unitID, $methodID);

			return false;
		}

		$model->units->$unitID->methodID = $model->methods->$methodID;

		return true;
	}

	/**
	 * Checks whether XML node has the expected structure and required
	 * information
	 *
	 * @param   Schedules &$model  the validating schedule model
	 * @param   object    &$node   the node to be validated
	 *
	 * @return void
	 */
	public static function validate(&$model, &$node)
	{
		// Unit has no instances and should not have been exported
		if (empty($node->times->count()))
		{
			return;
		}

		$effBeginDT  = isset($node->begindate) ?
			strtotime(trim((string) $node->begindate)) : strtotime(trim((string) $node->effectivebegindate));
		$termBeginDT = strtotime($model->term->startDate);
		$effEndDT    = isset($node->enddate) ?
			strtotime(trim((string) $node->enddate)) : strtotime(trim((string) $node->effectiveenddate));
		$termEndDT   = strtotime($model->term->endDate);

		// Unit starts after term ends or ends before term begins
		if ($effBeginDT > $termEndDT or $effEndDT < $termBeginDT)
		{
			return;
		}

		// Unit overlaps beginning of term => use term start
		$effBeginDT = $effBeginDT < $termBeginDT ? $termBeginDT : $effBeginDT;

		// Unit overlaps end of term => use term end
		$effEndDT = $termEndDT < $effEndDT ? $termEndDT : $effEndDT;

		// Reset variables passed through the object
		$rawUntisID = str_replace("LS_", '', trim((string) $node[0]['id']));
		$untisID    = substr($rawUntisID, 0, strlen($rawUntisID) - 2);

		$gridName = (string) $node->timegrid;
		if (empty($gridName))
		{
			$gridName = 'Haupt-Zeitraster';
		}

		$comment = trim((string) $node->text);
		if (empty($comment) or $comment == '.')
		{
			$comment = '';
		}

		$role = trim((string) $node->text1);

		$unit               = new stdClass;
		$unit->departmentID = $model->departmentID;
		$unit->termID       = $model->termID;
		$unit->untisID      = $untisID;
		$unit->gridID       = Grids::getID($gridName);
		$unit->gridName     = $gridName;
		$unit->roleID       = self::getRoleID($role);
		$unit->startDate    = date('Y-m-d', $effBeginDT);
		$unit->startDT      = $effBeginDT;
		$unit->endDate      = date('Y-m-d', $effEndDT);
		$unit->endDT        = $effEndDT;
		$unit->comment      = (empty($comment) or $comment == '.') ? '' : $comment;

		$model->units->$untisID = $unit;

		$valid = count($model->errors) === 0;
		if ($valid)
		{
			self::setID($model, $untisID);
		}

		$valid = (self::validateDates($model, $untisID) and $valid);
		$valid = (self::validateEvent($model, $node, $untisID) and $valid);
		$valid = (self::validateGroups($model, $node, $untisID) and $valid);
		$valid = (self::validatePerson($model, $node, $untisID) and $valid);
		$valid = (self::validateMethod($model, $node, $untisID) and $valid);

		// Adjusted dates are used because effective dts are not always accurate for the time frame
		$filteredOccurrences = self::getFilteredOccurrences($model, $node, $untisID);

		// Cannot produce blocking errors
		Instances::validateCollection($model, $node->times, $untisID, $filteredOccurrences, $valid);
	}

	/**
	 * Validates the lesson_teacher attribute and sets corresponding schedule elements
	 *
	 * @param   Schedules &$model   the validating schedule model
	 * @param   object    &$node    the unit node
	 * @param   int        $unitID  the id of the unit being iterated
	 *
	 * @return boolean  true if valid, otherwise false
	 */
	private static function validatePerson(&$model, &$node, $unitID)
	{
		$personID = str_replace('TR_', '', trim((string) $node->lesson_teacher[0]['id']));

		if (empty($personID))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_PERSON_MISSING'), $unitID);

			return false;
		}

		if (empty($model->persons->$personID))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_PERSON_INVALID'), $unitID, $personID);

			return false;
		}

		$model->units->$unitID->personID = $model->persons->$personID->id;

		return true;
	}

	/**
	 * Validates the groups attribute and sets corresponding schedule elements
	 *
	 * @param   Schedules &$model   the validating schedule model
	 * @param   object    &$node    the unit node
	 * @param   int        $unitID  the id of the unit being iterated
	 *
	 * @return boolean  true if valid, otherwise false
	 */
	private static function validateGroups(&$model, &$node, $unitID)
	{
		$rawUntisIDs = str_replace('CL_', '', (string) $node->lesson_classes[0]['id']);

		if (empty($rawUntisIDs))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_GROUPS_MISSING'), $unitID);

			return false;
		}

		$unit         = $model->units->$unitID;
		$unit->groups = [];
		$groupIDs     = explode(" ", $rawUntisIDs);

		foreach ($groupIDs as $groupID)
		{
			if (empty($model->groups->$groupID))
			{
				$model->warnings[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_GROUP_INVALID'), $unitID, $groupID);

				continue;
			}

			$unit->groups[] = $model->groups->$groupID->id;
		}

		return count($unit->groups) ? true : false;
	}

	/**
	 * Checks for the validity and consistency of date values
	 *
	 * @param   Schedules &$model   the validating schedule model
	 * @param   int        $unitID  the id of the unit being iterated
	 *
	 * @return boolean  true if dates are valid, otherwise false
	 */
	private static function validateDates(&$model, $unitID)
	{
		$unit  = $model->units->$unitID;
		$valid = true;
		if (empty($unit->startDT))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_START_DATE_MISSING'), $unitID);

			$valid = false;
		}

		$syStartTime = strtotime($model->schoolYear->startDate);
		$syEndTime   = strtotime($model->schoolYear->endDate);

		if ($unit->startDT < $syStartTime or $unit->startDT > $syEndTime)
		{
			$model->errors[] = sprintf(
				Languages::_('THM_ORGANIZER_UNIT_START_DATE_INVALID'),
				$unitID,
				$unit->startDate
			);

			$valid = false;
		}

		if (empty($unit->endDT))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_UNIT_END_DATE_MISSING'), $unitID);

			$valid = false;
		}

		$validEndDate = ($unit->endDT >= $syStartTime and $unit->endDT <= $syEndTime);
		if (!$validEndDate)
		{
			$model->errors[] = sprintf(
				Languages::_('THM_ORGANIZER_UNIT_END_DATE_INVALID'),
				$unitID,
				$unit->endDate
			);

			$valid = false;
		}

		// Checks if start date is before end date
		if ($unit->endDT < $unit->startDT)
		{
			$model->errors[] = sprintf(
				Languages::_('THM_ORGANIZER_UNIT_DATES_INCONSISTENT'),
				$unitID,
				$unit->startDate,
				$unit->endDate
			);

			$valid = false;
		}

		return $valid;
	}
}
