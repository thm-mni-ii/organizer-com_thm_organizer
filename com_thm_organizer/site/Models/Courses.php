<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;
use Joomla\CMS\Form\Form;
use Organizer\Helpers\Filtered;
use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;
use Organizer\Helpers\Terms as TermsHelper;

/**
 * Class retrieves the data regarding a filtered set of courses.
 */
class Courses extends ListModel
{
	use Filtered;

	protected $defaultOrdering = 'name';

	protected $filter_fields = ['campusID', 'termID'];

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
		if ($this->clientContext === self::BACKEND)
		{
			return;
		}

		$params = Input::getParams();
		if ($params->get('onlyPrepCourses'))
		{
			$form->removeField('search', 'filter');
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
		$query->select('c.*')
			->from('#__thm_organizer_courses AS c')
			->innerJoin('#__thm_organizer_units AS u ON u.courseID = c.id')
			->innerJoin('#__thm_organizer_instances AS i ON i.unitID = u.id')
			->innerJoin('#__thm_organizer_events AS e ON e.id = i.eventID')
			->where("u.delta != 'removed'")
			->where("i.delta != 'removed'")
			->group('c.id');

		if ($search = $this->state->get('filter.search', '') and preg_match('/^[\d]+$/', $search))
		{
			$query->where("c.id = $search");
		}
		else
		{
			$this->setSearchFilter($query, ['c.name_de', 'c.name_en', 'e.name_de', 'e.name_en']);
		}

		if ($this->clientContext === self::FRONTEND and Input::getParams()->get('onlyPrepCourses'))
		{
			$query->where('c.termID = ' . TermsHelper::getPreviousID($this->state->get('filter.termID')))
				->where('e.preparatory = 1');
		}
		else
		{
			$this->setValueFilters($query, ['c.termID']);
		}

		self::addCampusFilter($query, 'c');

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

		if ($this->clientContext === self::FRONTEND)
		{
			$app             = OrganizerHelper::getApplication();
			$params          = Input::getParams();
			$requestedCampus = $app->getUserStateFromRequest($this->context . '.filter.campusID', 'filter.campusID');
			if (!$requestedCampus and $campusID = $params->get('campusID', 0))
			{
				$this->state->set('filter.campusID', $campusID);
			}

			$requestedTerm = $app->getUserStateFromRequest($this->context . '.filter.termID', 'filter.termID');
			if (!$requestedTerm and $params->get('onlyPrepCourses'))
			{
				$this->state->set('filter.termID', TermsHelper::getNextID());
			}
		}
	}
}
