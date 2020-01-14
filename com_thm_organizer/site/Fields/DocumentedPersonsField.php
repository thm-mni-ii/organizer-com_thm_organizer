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
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Subjects;

/**
 * Class creates a select box for the association of persons with subject documentation.
 */
class DocumentedPersonsField extends OptionsField
{
	protected $type = 'DocumentedPersons';

	/**
	 * Method to get the field input markup for a generic list.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		if (empty(Input::getInt('programID')))
		{
			return '';
		}

		return parent::getInput();
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		$options      = parent::getOptions();
		$calledPoolID = Input::getInput()->get->getInt('poolID', 0);
		$poolID       = Input::getFilterID('pool', $calledPoolID);
		$programID    = Input::getInt('programID');
		$subjectIDs   = $poolID ? Mappings::getPoolSubjects($poolID) : Mappings::getProgramSubjects($programID);

		if (empty($subjectIDs))
		{
			return $options;
		}

		$aggregatedPersons = [];
		foreach ($subjectIDs as $subjectID)
		{
			$subjectPersons = Subjects::getPersons($subjectID);
			if (empty($subjectPersons))
			{
				continue;
			}

			$aggregatedPersons = array_merge($aggregatedPersons, $subjectPersons);
		}

		ksort($aggregatedPersons);

		foreach ($aggregatedPersons as $name => $person)
		{
			$options[] = HTML::_('select.option', $person['id'], $name);
		}

		return $options;
	}
}
