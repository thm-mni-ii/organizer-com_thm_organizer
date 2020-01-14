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

use Joomla\CMS\Factory;

/**
 * Class provides general functions for retrieving room data.
 */
class Rooms extends ResourceHelper implements Selectable
{
	use Filtered;

	/**
	 * Retrieves a list of resources in the form of name => id.
	 *
	 * @return array the resources, or empty
	 */
	public static function getOptions()
	{
		$options = [];
		foreach (self::getResources() as $room)
		{
			$options[] = HTML::_('select.option', $room['id'], $room['name']);
		}

		return $options;
	}

	/**
	 * Retrieves the ids for filtered rooms used in events.
	 *
	 * @return array the rooms used in actual events which meet the filter criteria
	 */
	public static function getPlannedRooms()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('r.id, r.name, rt.id AS roomtypeID')
			->from('#__thm_organizer_rooms AS r')
			->innerJoin('#__thm_organizer_instance_rooms AS ir ON ir.roomID = r.id')
			->leftJoin('#__thm_organizer_roomtypes AS rt ON ir.roomID = r.id')
			->order('r.name');

		if ($selectedDepartment = Input::getFilterID('department'))
		{
			$query->innerJoin('#__thm_organizer_instance_groups AS ig ON ig.assocID = ir.assocID')
				->innerJoin('#__thm_organizer_groups AS g ON g.id = ig.groupID')
				->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = g.categoryID')
				->where("dr.departmentID = $selectedDepartment");

			if ($selectedCategory = Input::getFilterID('category'))
			{
				$query->where("g.categoryID  = $selectedCategory");
			}
		}

		$dbo->setQuery($query);

		if (!$results = OrganizerHelper::executeQuery('loadAssocList', []))
		{
			return [];
		}

		$plannedRooms = [];
		foreach ($results as $result)
		{
			$plannedRooms[$result['name']] = ['id' => $result['id'], 'roomtypeID' => $result['roomtypeID']];
		}

		return $plannedRooms;
	}

	/**
	 * Retrieves all room entries which match the given filter criteria. Ordered by their display names.
	 *
	 * @return array the rooms matching the filter criteria or empty if none were found
	 */
	public static function getResources()
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("DISTINCT r.id, r.*")
			->from('#__thm_organizer_rooms AS r');

		self::addResourceFilter($query, 'roomtype', 'rt', 'r');
		self::addResourceFilter($query, 'building', 'b1', 'r');

		// This join is used specifically to filter campuses independent of buildings.
		$query->leftJoin('#__thm_organizer_buildings AS b2 ON b2.id = r.buildingID');
		self::addCampusFilter($query, 'b2');

		$query->order('name');
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
	}
}
