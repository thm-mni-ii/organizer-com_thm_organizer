<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use \THM_OrganizerHelperHTML as HTML;

require_once 'list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/language.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/teachers.php';

/**
 * Class retrieves information for a filtered set of teachers.
 */
class THM_OrganizerModelTeacher_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 't.surname, t.forename';

    protected $defaultDirection = 'asc';

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
        $this->setIDFilter($query, 'departmentID', ['list.departmentID']);
        $this->setValueFilters($query, ['forename', 'username', 't.gpuntisID']);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to create iterate table data
     *
     * @return array  an array of arrays with preformatted teacher data
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $itemForename  = empty($item->forename) ? '' : $item->forename;
            $itemUsername  = empty($item->username) ? '' : $item->username;
            $itemGPUntisID = empty($item->gpuntisID) ? '' : $item->gpuntisID;

            $return[$index]                = [];
            $return[$index]['checkbox']    = HTML::_('grid.id', $index, $item->id);
            $return[$index]['surname']     = HTML::_('link', $item->link, $item->surname);
            $return[$index]['forename']    = HTML::_('link', $item->link, $itemForename);
            $return[$index]['username']    = HTML::_('link', $item->link, $itemUsername);
            $return[$index]['t.gpuntisID'] = HTML::_('link', $item->link, $itemGPUntisID);

            $departments = THM_OrganizerHelperTeachers::getDepartmentNames($item->id);

            if (empty($departments)) {
                $return[$index]['departmentID'] = \JText::_('JNONE');
            } elseif (count($departments) === 1) {
                $return[$index]['departmentID'] = $departments[0];
            } else {
                $return[$index]['departmentID'] = \JText::_('COM_THM_ORGANIZER_MULTIPLE_DEPARTMENTS');
            }

            $index++;
        }

        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $headers                 = [];
        $headers['checkbox']     = '';
        $headers['surname']      = \JText::_('COM_THM_ORGANIZER_SURNAME');
        $headers['forename']     = \JText::_('COM_THM_ORGANIZER_FORENAME');
        $headers['username']     = \JText::_('COM_THM_ORGANIZER_USERNAME');
        $headers['t.gpuntisID']  = \JText::_('COM_THM_ORGANIZER_GPUNTISID');
        $headers['departmentID'] = \JText::_('COM_THM_ORGANIZER_DEPARTMENT');

        return $headers;
    }
}
