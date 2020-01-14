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
use Joomla\CMS\Form\FormField;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class creates a select box for explicitly mapping subject documentation to plan subjects. This is also done
 * implicitly during the schedule import process according to degree programs and the subject's module number.
 */
class SubjectEventsField extends FormField
{
	use Translated;

	protected $type = 'SubjectEvents';

	/**
	 * Returns a selectionbox where stored coursepool can be chosen as a parent node
	 *
	 * @return string  the HTML output
	 */
	public function getInput()
	{
		$fieldName = $this->getAttribute('name');
		$subjectID = Input::getID();

		$dbo          = Factory::getDbo();
		$subjectQuery = $dbo->getQuery(true);
		$subjectQuery->select('eventID');
		$subjectQuery->from('#__thm_organizer_subject_events');
		$subjectQuery->where("subjectID = '$subjectID'");
		$dbo->setQuery($subjectQuery);
		$selected = OrganizerHelper::executeQuery('loadColumn', []);

		$tag        = Languages::getTag();
		$eventQuery = $dbo->getQuery(true);
		$eventQuery->select("id AS value, name_$tag AS name");
		$eventQuery->from('#__thm_organizer_events');
		$eventQuery->order('name');
		$dbo->setQuery($eventQuery);

		$events = OrganizerHelper::executeQuery('loadAssocList');
		if (empty($events))
		{
			$events = [];
		}

		$options = [];
		foreach ($events as $course)
		{
			$options[] = HTML::_('select.option', $course['value'], $course['name']);
		}

		$attributes       = ['multiple' => 'multiple', 'size' => '10'];
		$selectedMappings = empty($selected) ? [] : $selected;

		return HTML::selectBox($options, $fieldName, $attributes, $selectedMappings, true);
	}
}
