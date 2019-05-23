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

defined('_JEXEC') or die;

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
        $shortTag = Languages::getShortTag();
        $query = $this->_db->getQuery(true);

        $select    = 'DISTINCT pa.id, ';
        $linkParts = ["'index.php?option=com_thm_organizer&view=participant_edit&id='", 'pa.id'];
        $select    .= $query->concatenate($linkParts, '') . ' AS link, ';
        $paNameParts = ['pa.surname', "', '", 'pa.forename'];
        $select    .= $query->concatenate($paNameParts, '') . ' AS fullName, ';
        $prNameParts = ["pr.name_$shortTag", "' ('", 'dg.abbreviation', "')'"];
        $select    .= $query->concatenate($prNameParts, '') . ' AS programName ';

        $query->from('#__thm_organizer_participants AS pa')
            ->innerJoin('#__users AS u ON u.id = pa.id')
            ->innerJoin('#__thm_organizer_programs AS pr on pr.id = pa.programID')
            ->innerJoin('#__thm_organizer_degrees AS dg ON dg.id = pr.degreeID');


        $query->select($select);

        $searchColumns = ['pa.forename', 'pa.surname'];
        $this->setSearchFilter($query, $searchColumns);
        $this->setIDFilter($query, 'pa.programID', 'list.programID');

        $this->setOrdering($query);

        return $query;
    }
}
