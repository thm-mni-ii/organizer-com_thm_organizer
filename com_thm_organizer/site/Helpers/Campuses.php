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
use Organizer\Tables\Campuses as CampusesTable;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Campuses extends ResourceHelper implements Selectable
{
	/**
	 * Retrieves the default grid id for the given campus
	 *
	 * @param   int  $campusID  the id of the campus
	 *
	 * @return int the id of the associated grid
	 */
	public static function getGridID($campusID)
	{
		$table = new CampusesTable;
		if (!$table->load($campusID))
		{
			return 0;
		}

		if ($gridID = $table->gridID)
		{
			return $gridID;
		}

		if ($parentID = $table->parentID)
		{
			return self::getGridID($parentID);
		}

		return 0;
	}

	/**
	 * Creates a link to the campus' location
	 *
	 * @param   int  $campusID  the id of the campus
	 *
	 * @return string the HTML for the location link
	 */
	public static function getLocation($campusID)
	{
		$table = new CampusesTable;
		$table->load($campusID);

		return empty($table->location) ? '' : str_replace(' ', '', $table->location);
	}

	/**
	 * Gets the qualified campus name
	 *
	 * @param   int  $campusID  the campus' id
	 *
	 * @return string the name if the campus could be resolved, otherwise empty
	 */
	public static function getName($campusID = null)
	{
		if (empty($campusID))
		{
			return '';
		}

		$tag   = Languages::getTag();
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("c1.name_$tag as name, c2.name_$tag as parentName")
			->from('#__thm_organizer_campuses AS c1')
			->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id')
			->where("c1.id = '$campusID'");
		$dbo->setQuery($query);
		$names = OrganizerHelper::executeQuery('loadAssoc', []);

		if (empty($names))
		{
			return '';
		}

		return empty($names['parentName']) ? $names['name'] : "{$names['parentName']} / {$names['name']}";
	}

	/**
	 * Retrieves the selectable options for the resource.
	 *
	 * @return array the available options
	 */
	public static function getOptions()
	{
		$options = [];
		foreach (self::getResources() as $campus)
		{
			$name = empty($campus['parentName']) ? $campus['name'] : "{$campus['parentName']} / {$campus['name']}";

			$options[$name] = HTML::_('select.option', $campus['id'], $name);
		}

		ksort($options);

		return $options;
	}

	/**
	 * Retrieves the resource items.
	 *
	 * @return array the available resources
	 */
	public static function getResources()
	{
		$tag = Languages::getTag();

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select("c1.*, c1.name_$tag AS name")
			->from('#__thm_organizer_campuses AS c1')
			->select("c2.name_$tag as parentName")
			->leftJoin('#__thm_organizer_campuses as c2 on c2.id = c1.parentID')
			->order('parentName, name');

		$selectedIDs = Input::getSelectedIDs();
		$view        = Input::getView();

		// Only parents
		if (strtolower($view) === 'campus_edit')
		{
			$query->where("c1.parentID IS NULL");

			// Not self
			if (count($selectedIDs))
			{
				$query->where("c1.id != {$selectedIDs[0]}");
			}
		}

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
	}

	/**
	 * Returns a pin icon with a link for the location
	 *
	 * @param   mixed  $input  int the id of the campus, string the location coordinates
	 *
	 * @return string the html output of the pin
	 */
	public static function getPin($input)
	{
		$isID     = is_numeric($input);
		$location = $isID ? self::getLocation($input) : $input;

		if (!preg_match('/\d{1,2}\.\d{6},[ ]*\d{1,2}\.\d{6}/', $location))
		{
			return '';
		}

		$pin = '<a target="_blank" href="https://www.google.de/maps/place/' . $location . '">';
		$pin .= '<span class="icon-location"></span></a>';

		return $pin;
	}
}
