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

use Exception;
use Organizer\Helpers;
use Organizer\Helpers\Languages;
use Organizer\Tables\Categories as CategoriesTable;
use Organizer\Tables\Degrees as DegreesTable;
use stdClass;

/**
 * Provides general functions for campus access checks, data retrieval and display.
 */
class Categories extends Helpers\ResourceHelper implements UntisXMLValidator
{
	/**
	 * Determines whether the data conveyed in the untisID is plausible for finding a real program.
	 *
	 * @param   string  $untisID  the id used in untis for this program
	 *
	 * @return array empty if the id is implausible
	 */
	private static function parseProgramData($untisID)
	{
		$pieces = explode('.', $untisID);
		if (count($pieces) !== 3)
		{
			return [];
		}

		// Two uppercase letter code for the degree. First letter is B (Bachelor) or M (Master)
		$implausibleDegree = (!ctype_upper($pieces[1]) or !preg_match('/^[B|M]{1}[A-Z]{1,2}$/', $pieces[1]));
		if ($implausibleDegree)
		{
			return [];
		}

		// Some degree program 'subject' identifiers have a number
		$plausibleCode = preg_match('/^[A-Z]+[0-9]*$/', $pieces[0]);

		// Degrees are their own managed resource
		$degrees  = new DegreesTable;
		$degreeID = $degrees->load(['code' => $pieces[1]]) ? $degrees->id : null;

		// Should be year of accreditation, but ITS likes to pick random years
		$plausibleVersion = (ctype_digit($pieces[2]) and preg_match('/^[2]{1}[0-9]{3}$/', $pieces[2]));

		return ($plausibleCode and $degreeID and $plausibleVersion) ?
			['code' => $pieces[0], 'degreeID' => $degreeID, 'version' => $pieces[2]] : [];
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
		$category     = $model->categories->$untisID;
		$exists       = false;
		$loadCriteria = [['untisID' => $untisID], ['name' => $category->name]];
		$table        = new CategoriesTable;

		foreach ($loadCriteria as $criterion)
		{
			if ($exists = $table->load($criterion))
			{
				$altered = false;

				foreach ($category as $key => $value)
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

				break;
			}
		}

		if (!$exists)
		{
			$table->save($category);
		}

		$category->id = $table->id;

		return;
	}

	/**
	 * Checks whether XML node has the expected structure and required information.
	 *
	 * @param   Schedules &  $model  the validating schedule model
	 * @param   object &     $node   the node to be validated
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function validate(&$model, &$node)
	{
		$untisID = str_replace('DP_', '', trim((string) $node[0]['id']));

		$name = (string) $node->longname;
		if (!isset($name))
		{
			$model->errors[] = sprintf(Languages::_('THM_ORGANIZER_CATEGORY_NAME_MISSING'), $untisID);

			return;
		}

		$category          = new stdClass;
		$category->name    = $name;
		$category->untisID = $untisID;

		$programData         = self::parseProgramData($untisID);
		$filteredName        = trim(substr($name, 0, strpos($name, '(')));
		$category->programID = empty($programData) ? null : Helpers\Programs::getID($programData, $filteredName);

		$model->categories->$untisID = $category;

		self::setID($model, $untisID);
		Helpers\Departments::setDepartmentResource($category->id, 'categoryID');
	}
}
