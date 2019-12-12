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

	protected $filter_fields = ['programID'];

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$query = $this->_db->getQuery(true);

		$query->select('DISTINCT pa.id, pa.programID, u.email')
			->select($query->concatenate(['pa.surname', "', '", 'pa.forename'], '') . ' AS fullName')
			->from('#__thm_organizer_participants AS pa')
			->innerJoin('#__users AS u ON u.id = pa.id')
			->innerJoin('#__thm_organizer_programs AS pr ON pr.id = pa.programID');

		$this->setSearchFilter($query, ['pa.forename', 'pa.surname', 'pr.name_de', 'pr.name_en']);
		$this->setValueFilters($query, ['programID']);

		if ($courseID = Input::getFilterID('course'))
		{
			$query->select('cp.attended, cp.paid, cp.status')
				->innerJoin('#__thm_organizer_course_participants AS cp on cp.participantID = pa.id')
				->where("cp.courseID = $courseID");
		}

		$this->setOrdering($query);

		return $query;
	}
}
