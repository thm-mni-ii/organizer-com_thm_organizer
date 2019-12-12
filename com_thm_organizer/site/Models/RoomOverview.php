<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Joomla\CMS\Form\Form;
use Organizer\Helpers as Helpers;
use Organizer\Helpers\Input;

/**
 * Retrieves lesson and event data for a filtered set of rooms.
 */
class RoomOverview extends ListModel
{
	use Helpers\Filtered;

	const DAY = 1, WEEK = 2;

	protected $defaultLimit = 10;

	protected $defaultOrdering = 'r.name';

	protected $filter_fields = ['buildingID', 'capacity', 'roomtypeID'];

	/**
	 * Filters out form inputs which should not be displayed due to menu settings.
	 *
	 * @param   Form  $form  the form to be filtered
	 *
	 * @return void modifies $form
	 */
	protected function filterFilterForm(&$form)
	{
		parent::filterFilterForm($form);

		$params = Input::getParams();

		if ($params->get('campusID', 0))
		{
			$form->removeField('campusID', 'filter');
		}
	}

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$tag   = Helpers\Languages::getTag();
		$query = $this->_db->getQuery(true);

		$query->select('r.id, r.name AS name, r.capacity')
			->select("t.id AS roomtypeID, t.name_$tag AS typeName, t.description_$tag AS typeDesc")
			->from('#__thm_organizer_rooms AS r')
			->leftJoin('#__thm_organizer_roomtypes AS t ON r.roomtypeID = t.id')
			->leftJoin('#__thm_organizer_buildings AS b ON b.id = r.buildingID')
			->leftJoin('#__thm_organizer_campuses AS c ON (c.id = b.campusID OR c.parentID = b.campusID)');

		// Only display public room types, i.e. no offices or toilets...
		$query->where('t.public = 1');

		$this->setSearchFilter($query, ['r.name']);
		$this->setValueFilters($query, ['campusID', 'buildingID', 'roomtypeID']);

		if ($roomIDs = Helpers\Input::getFilterIDs('room'))
		{
			$query->where('r.id IN (' . implode(',', $roomIDs) . ')');
		}

		if ($capacity = Helpers\Input::getInt('capacity'))
		{
			$query->where("r.capacity >= $capacity");
		}

		self::addCampusFilter($query, 'b');

		$query->order($this->defaultOrdering);

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return void populates state properties
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering = null, $direction = null);

		$app = Helpers\OrganizerHelper::getApplication();

		$template = $app->getUserStateFromRequest($this->context . '.list.template', 'list.template', self::DAY, 'INT');
		$this->setState('list.template', $template);

		if ($campusID = Helpers\Input::getParams()->get('campusID'))
		{
			$gridID = Helpers\Campuses::getGridID($campusID);
		}
		else
		{
			$gridID = Helpers\Grids::getDefault();
		}

		$gridID = $app->getUserStateFromRequest($this->context . '.list.gridID', 'list.gridID', $gridID, 'INT');
		$this->setState('list.gridID', $gridID);

		$defaultDate = date('Y-m-d');
		$date        = $app->getUserStateFromRequest($this->context . '.list.gridID', 'list.date', $defaultDate);
		if (strtotime($date))
		{
			$this->setState('list.date', $date);
		}
		else
		{
			$this->setState('list.date', $defaultDate);
		}
	}
}
