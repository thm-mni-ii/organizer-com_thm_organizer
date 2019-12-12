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
use Organizer\Helpers\Can;
use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of (degree) programs.
 */
class Programs extends ListModel
{
	protected $filter_fields = ['degreeID', 'departmentID', 'fieldID', 'frequencyID', 'version'];

	/**
	 * Method to get a list of resources from the database.
	 *
	 * @return JDatabaseQuery
	 */
	protected function getListQuery()
	{
		$authorizedDepts = Can::documentTheseDepartments();
		$tag             = Languages::getTag();

		$query     = $this->_db->getQuery(true);
		$linkParts = ["'index.php?option=com_thm_organizer&view=program_edit&id='", 'dp.id'];
		$query->select("DISTINCT dp.id AS id, dp.name_$tag AS programName, version")
			->select($query->concatenate($linkParts, '') . ' AS link')
			->from('#__thm_organizer_programs AS dp')
			->select('d.abbreviation AS degree')
			->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID')
			->leftJoin('#__thm_organizer_fields AS f ON f.id = dp.fieldID')
			->select("dpt.shortName_$tag AS department")
			->leftJoin('#__thm_organizer_departments AS dpt ON dp.departmentID = dpt.id')
			->where('(dp.departmentID IN (' . implode(',', $authorizedDepts) . ') OR dp.departmentID IS NULL)');

		$searchColumns = ['dp.name_de', 'dp.name_en', 'version', 'd.name', 'description_de', 'description_en'];
		$this->setSearchFilter($query, $searchColumns);
		$this->setValueFilters($query, ['degreeID', 'departmentID', 'fieldID', 'frequencyID', 'version']);

		$this->setOrdering($query);

		return $query;
	}
}
