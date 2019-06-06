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

use Organizer\Helpers\Languages;

/**
 * Class retrieves information for a filtered set of participants.
 */
class Participants extends ListModel
{
    protected $defaultOrdering = 'fullName';

    /**
     * Method to get all groups from the database
     *
     * @return \JDatabaseQuery
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
}
