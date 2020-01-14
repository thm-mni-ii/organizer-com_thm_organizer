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
use Organizer\Helpers\Subjects;

/**
 * Class creates a select box for the association of persons with subject documentation.
 */
class SubjectPersonsField extends OptionsField
{
	protected $type = 'SubjectPersons';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$subjectIDs = Input::getSelectedIDs();
		$role       = $this->getAttribute('role');
		$invalid    = (empty($subjectIDs) or empty($subjectIDs[0]) or empty($role));

		if ($invalid)
		{
			return [];
		}

		$existingPersons = Subjects::getPersons($subjectIDs[0], $role);
		$this->value     = [];
		foreach ($existingPersons as $person)
		{
			$this->value[$person['id']] = $person['id'];
		}

		$dbo   = Factory::getDbo();
		$query = $dbo->getQuery(true);
		$query->select('t.id, t.surname, t.forename')
			->from('#__thm_organizer_persons AS t')
			->order('surname, forename');

		$departmentID = $this->form->getValue('departmentID');
		if (!empty($departmentID))
		{
			if (empty($this->value))
			{
				$query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.personID = t.id');
				$query->where("departmentID = $departmentID");
			}
			else
			{
				$query->leftJoin('#__thm_organizer_department_resources AS dr ON dr.personID = t.id');
				$personIDs  = implode(',', $this->value);
				$extPersons = "(departmentID != $departmentID AND personID IN ($personIDs))";
				$query->where("(departmentID = $departmentID OR $extPersons)");
			}
		}

		$dbo->setQuery($query);
		$persons = OrganizerHelper::executeQuery('loadAssocList', null, 'id');

		$options = parent::getOptions();
		if (empty($persons))
		{
			return $options;
		}

		foreach ($persons as $person)
		{
			$text      = empty($person['forename']) ?
				$person['surname'] : "{$person['surname']}, {$person['forename']}";
			$options[] = HTML::_('select.option', $person['id'], $text);
		}

		return $options;
	}
}
