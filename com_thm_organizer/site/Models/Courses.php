<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Joomla\CMS\Form\Form;
use Organizer\Helpers\Filtered;
use Organizer\Helpers\Input;
use Organizer\Helpers\Terms as TermsHelper;

/**
 * Class retrieves the data regarding a filtered set of courses.
 */
class Courses extends ListModel
{
	use Filtered;

	protected $defaultOrdering = 'name';

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

		$form->removeField('limit', 'list');
		$params = Input::getParams();
		if ($params->get('onlyPrepCourses'))
		{
			$form->removeField('search', 'filter');
			$form->removeField('termID', 'filter');
		}

		if ($params->get('campusID', 0))
		{
			$form->removeField('campusID', 'filter');
		}
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  array  item objects on success, otherwise empty
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// set the names

		if (empty($items))
		{
			return [];
		}

		return $items;
	}

	/**
	 * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * Adds filter settings for status, campus, term
	 *
	 * @return \JDatabaseQuery  A \JDatabaseQuery object to retrieve the data set.
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

		$this->setSearchFilter($query, ['c.name_de', 'c.name_en', 'e.name_de', 'e.name_en']);

		if ($this->clientContext === self::FRONTEND and Input::getParams()->get('onlyPrepCourses'))
		{
			$query->where('c.termID = ' . TermsHelper::getPreviousID($this->state->get('filter.termID')));
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
			$this->state->set('list.limit', 0);
			$params = Input::getParams();
			if ($campusID = $params->get('campusID', 0))
			{
				$this->setState('filter.campusID', $campusID);
			}

			if ($params->get('onlyPrepCourses'))
			{
				$this->setState('filter.termID', TermsHelper::getNextID());
			}
		}
	}
}
