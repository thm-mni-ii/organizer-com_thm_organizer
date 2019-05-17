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

/**
 * Class retrieves information for a filtered set of teachers.
 */
class Teachers extends ListModel
{
    protected $defaultOrdering = 't.surname, t.forename';

    /**
     * Method to get all teachers from the database
     *
     * @return \JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query  = $this->_db->getQuery(true);
        $select = 'DISTINCT t.id, t.surname, t.forename, t.username, t.gpuntisID, d.id AS departmentID, ';
        $parts  = ["'index.php?option=com_thm_organizer&view=teacher_edit&id='", 't.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);
        $query->from('#__thm_organizer_teachers AS t')
            ->leftJoin('#__thm_organizer_department_resources AS dr on dr.teacherID = t.id')
            ->leftJoin('#__thm_organizer_departments AS d on d.id = dr.id');

        $this->setSearchFilter($query, ['surname', 'forename', 'username', 't.gpuntisID']);
        $this->setIDFilter($query, 'departmentID', 'list.departmentID');
        $this->setValueFilters($query, ['forename', 'username', 't.gpuntisID']);

        $this->setOrdering($query);

        return $query;
    }
}
