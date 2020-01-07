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

use JDatabaseQuery;
use Joomla\CMS\Form\Form;
use Organizer\Helpers\Input;

/**
 * Class retrieves information for a filtered set of participants.
 */
class Participants extends ListModel
{
	protected $defaultOrdering = 'fullName';

	protected $filter_fields = ['attended', 'duplicates', 'paid', 'programID'];

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

		if (!$courseID = Input::getFilterID('course'))
		{
			$form->removeField('attended', 'filter');
			$form->removeField('paid', 'filter');
		}
	}

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		$query->select('pa.id, pa.programID, u.email')
			->select($query->concatenate(['pa.surname', "', '", 'pa.forename'], '') . ' AS fullName')
			->from('#__thm_organizer_participants AS pa')
			->innerJoin('#__users AS u ON u.id = pa.id')
			->leftJoin('#__thm_organizer_programs AS pr ON pr.id = pa.programID');

		$this->setSearchFilter($query, ['pa.forename', 'pa.surname', 'pr.name_de', 'pr.name_en']);
		$this->setValueFilters($query, ['attended', 'paid', 'programID']);

		if ($courseID = $this->state->get('filter.courseID'))
		{
			$query->select('cp.attended, cp.paid, cp.status')
				->innerJoin('#__thm_organizer_course_participants AS cp on cp.participantID = pa.id')
				->where("cp.courseID = $courseID");
		}

		if (Input::getBool('duplicates'))
		{
			$likePAFN   = $query->concatenate(["'%'", 'TRIM(pa.forename)', "'%'"], '');
			$likePA2FN  = $query->concatenate(["'%'", 'TRIM(pa2.forename)', "'%'"], '');
			$conditions = "((pa.forename LIKE $likePA2FN OR pa2.forename LIKE $likePAFN)";

			$conditions .= " AND ";

			$likePASN   = $query->concatenate(["'%'", 'TRIM(pa.surname)', "'%'"], '');
			$likePA2SN  = $query->concatenate(["'%'", 'TRIM(pa2.surname)', "'%'"], '');
			$conditions .= "(pa.surname LIKE $likePA2SN OR pa2.surname LIKE $likePASN))";
			$query->leftJoin("#__thm_organizer_participants AS pa2 on $conditions")
				->where('pa.id != pa2.id')
				->group('pa.id');

			if ($courseID)
			{
				$query->innerJoin('#__thm_organizer_course_participants AS cp2 on cp2.participantID = pa2.id')
					->where("cp2.courseID = $courseID");
			}
		}

		$this->setOrdering($query);

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
		parent::populateState($ordering, $direction);

		if ($courseID = Input::getFilterID('course'))
		{
			$this->setState("filter.courseID", $courseID);
		}
	}
}
