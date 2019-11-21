<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
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

	protected $filter_fields = ['programID'];

	/**
	 * Filters out form inputs which should not be displayed due to menu settings.
	 *
	 * @param   Form  $form  the form to be filtered
	 *
	 * @return void modifies $form
	 */
	public function filterFilterForm(&$form)
	{
		parent::filterFilterForm($form);
		if ($this->clientContext === self::BACKEND)
		{
			return;
		}

		$form->removeField('limit', 'list');
	}

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		$query->select('DISTINCT pa.id, pa.programID')
			->select($query->concatenate(['pa.surname', "', '", 'pa.forename'], '') . ' AS fullName')
			->from('#__thm_organizer_participants AS pa')
			->innerJoin('#__users AS u ON u.id = pa.id');

		$this->setSearchFilter($query, ['pa.forename', 'pa.surname']);
		$this->setValueFilters($query, ['programID']);

		$this->setOrdering($query);

		return $query;
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return void
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);

		if ($this->clientContext === self::FRONTEND)
		{
			$this->state->set('list.limit', 0);
		}

		return;
	}
}
