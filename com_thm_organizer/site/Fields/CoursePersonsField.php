<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Fields;

use Joomla\CMS\Factory;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a form field for course person selection
 */
class CoursePersonsField extends OptionsField
{
	/**
	 * @var  string
	 */
	protected $type = 'CoursePersons';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		$persons = [];
		foreach (self::getPersons() as $person)
		{
			$persons[] = HTML::_('select.option', $person['id'], $person['name']);
		}

		return array_merge($options, $persons);
	}

	/**
	 * Gets the persons associated with courses with additional optional filters.
	 *
	 * @return array  the persons associated with courses
	 */
	public static function getPersons()
	{
		if (!$departmentID = Input::getFilterID('department', 0))
		{
			return [];
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);

		$query->select('DISTINCT p.*')->from('#__thm_organizer_persons AS p')->order('p.surname, p.forename');

		// Ensure relation to a course
		$query->innerJoin('#__thm_organizer_instance_persons AS ip ON ip.personID = p.id')
			->innerJoin('#__thm_organizer_instances AS i ON i.id = ip.instanceID')
			->innerJoin('#__thm_organizer_units AS u ON u.id = i.unitID')
			->where('u.courseID IS NOT NULL');

		// Ensure relation to a department
		$query->innerJoin('#__thm_organizer_instance_groups AS ig ON ig.assocID = ip.id')
			->innerJoin('#__thm_organizer_groups AS g ON g.id = ig.groupID')
			->innerJoin('#__thm_organizer_department_resources AS dr ON dr.categoryID = g.categoryID')
			->where("dr.departmentID = $departmentID");

		// Ensure existence
		$query->where("i.delta != 'removed'")
			->where("ip.delta != 'removed'")
			->where("ig.delta != 'removed'")
			->where("u.delta != 'removed'");

		// Extra filters
		if ($categoryID = Input::getFilterID('category', 0))
		{
			$query->where("g.categoryID = $categoryID");

			if ($groupID = Input::getFilterID('group', 0))
			{
				$query->where("g.id = $categoryID");
			}
		}

		$dbo->setQuery($query);

		if (!$persons = OrganizerHelper::executeQuery('loadAssocList', []))
		{
			return [];
		}

		foreach ($persons as $index => $person)
		{
			$name     = $person['surname'];
			$forename = trim($person['forename']);
			$name     .= $forename ? ", $forename" : '';

			$persons[$index]['name'] = $name;
		}

		return $persons;
	}
}
