<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use JDatabaseQuery;
use Joomla\CMS\Factory;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of subjects. Modal view.
 */
class SubjectSelection extends ListModel
{
	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$dbo   = Factory::getDbo();
		$tag   = Languages::getTag();
		$query = $dbo->getQuery(true);

		$query->select("DISTINCT s.id, code, name_$tag AS name")->from('#__thm_organizer_subjects AS s');

		$searchFields = [
			'name_de',
			'shortName_de',
			'abbreviation_de',
			'name_en',
			'shortName_en',
			'abbreviation_en',
			'code',
			'description_de',
			'objective_de',
			'content_de',
			'description_en',
			'objective_en',
			'content_en'
		];
		$this->setSearchFilter($query, $searchFields);
		$this->setValueFilters($query, ['code', 'fieldID']);

		$programID = $this->state->get('filter.programID', '');
		Mappings::setResourceIDFilter($query, $programID, 'program', 'subject');
		$poolID = $this->state->get('filter.poolID', '');
		Mappings::setResourceIDFilter($query, $poolID, 'pool', 'subject');

		$this->setOrdering($query);

		return $query;
	}
}
