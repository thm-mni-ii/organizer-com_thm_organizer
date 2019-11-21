<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Categories implements DepartmentAssociated, Selectable
{
	use Filtered;

	/**
	 * Retrieves the ids of departments associated with the resource
	 *
	 * @param   int  $resourceID  the id of the resource for which the associated departments are requested
	 *
	 * @return array the ids of departments associated with the resource
	 */
	public static function getDepartmentIDs($resourceID)
	{
		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('departmentID')
			->from('#__thm_organizer_department_resources')
			->where("categoryID = $resourceID");
		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadColumn', []);
	}

	/**
	 * Retrieves the category name
	 *
	 * @param   int  $categoryID  the table id for the program
	 *
	 * @return string the name of the (plan) program, otherwise empty
	 */
	public static function getName($categoryID)
	{
		$dbo = Factory::getDbo();
		$tag = Languages::getTag();

		$query     = $dbo->getQuery(true);
		$nameParts = ["p.name_$tag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
		$query->select('cat.name AS catName, ' . $query->concatenate($nameParts, "") . ' AS name');

		$query->from('#__thm_organizer_categories AS cat');
		$query->leftJoin('#__thm_organizer_programs AS p ON p.categoryID = cat.id');
		$query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
		$query->where("cat.id = '$categoryID'");

		$dbo->setQuery($query);
		$names = OrganizerHelper::executeQuery('loadAssoc', []);

		return empty($names) ? '' : empty($names['name']) ? $names['catName'] : $names['name'];
	}

	/**
	 * Retrieves the selectable options for the resource.
	 *
	 * @param   string  $access  any access restriction which should be performed
	 *
	 * @return array the available options
	 */
	public static function getOptions($access = '')
	{
		$options = [];
		foreach (self::getResources($access) as $category)
		{
			$name = empty($category['programName']) ? $category['name'] : $category['programName'];

			$options[] = HTML::_('select.option', $category['id'], $name);
		}

		uasort($options, function ($optionOne, $optionTwo) {
			return $optionOne->text > $optionTwo->text;
		});

		// Any out of sequence indexes cause JSON to treat this as an object
		return array_values($options);
	}

	/**
	 * Retrieves the name of the program associated with the category.
	 *
	 * @param   int  $categoryID  the table id for the program
	 *
	 * @return string the name of the (plan) program, otherwise empty
	 */
	public static function getProgram($categoryID)
	{
		$noName = Languages::_('THM_ORGANIZER_NO_PROGRAM');
		if (empty($categoryID))
		{
			return $noName;
		}

		$dbo = Factory::getDbo();
		$tag = Languages::getTag();

		$query     = $dbo->getQuery(true);
		$nameParts = ["p.name_$tag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
		$query->select($query->concatenate($nameParts, "") . ' AS name')
			->from('#__thm_organizer_programs AS p')
			->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id')
			->innerJoin('#__thm_organizer_categories AS cat ON cat.id = p.categoryID')
			->where("p.categoryID = '$categoryID'")
			->order('p.version DESC');


		$dbo->setQuery($query);
		$names = OrganizerHelper::executeQuery('loadColumn', []);

		return empty($names) ? $noName : $names[0];
	}

	/**
	 * Retrieves the resource items.
	 *
	 * @param   string  $access  any access restriction which should be performed
	 *
	 * @return array the available resources
	 */
	public static function getResources($access = '')
	{
		$dbo = Factory::getDbo();
		$tag = Languages::getTag();

		$query     = $dbo->getQuery(true);
		$nameParts = ["p.name_$tag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
		$query->select('DISTINCT c.*, ' . $query->concatenate($nameParts, "") . ' AS programName')
			->from('#__thm_organizer_categories AS c')
			->leftJoin('#__thm_organizer_programs AS p ON p.categoryID = c.id')
			->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id')
			->order('c.name');

		if (!empty($access))
		{
			$query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = c.id');
			self::addAccessFilter($query, 'dr', $access);
		}

		self::addDeptSelectionFilter($query, 'category', 'c');

		$dbo->setQuery($query);

		return OrganizerHelper::executeQuery('loadAssocList', []);
	}

	/**
	 * Retrieves subject entries from the database
	 *
	 * @return string  the subjects which fit the selected resource
	 */
	public function byPerson()
	{
		$dbo         = Factory::getDbo();
		$tag         = Languages::getTag();
		$query       = $dbo->getQuery(true);
		$concatQuery = ["dp.name_$tag", "', ('", 'd.abbreviation', "' '", ' dp.version', "')'"];
		$query->select('dp.id, ' . $query->concatenate($concatQuery, '') . ' AS name');
		$query->from('#__thm_organizer_programs AS dp');
		$query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
		$query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');

		$personClauses = Mappings::getPersonMappingClauses();
		if (!empty($personClauses))
		{
			$query->where('( ( ' . implode(') OR (', $personClauses) . ') )');
		}

		$query->order('name');
		$dbo->setQuery($query);

		$programs = OrganizerHelper::executeQuery('loadObjectList');

		return empty($programs) ? '[]' : json_encode($programs, JSON_UNESCAPED_UNICODE);
	}
}
