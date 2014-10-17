<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelTeachers for component com_thm_organizer
 * Class provides methods to deal with teachers
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 't.surname';

    protected $defaultDirection = 'ASC';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                    'id', 'id'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to get all teachers from the database
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        // Create the query
        $query = $this->_db->getQuery(true);
        $select = "t.id, t.surname, t.forename, t.username, t.gpuntisID, f.field, c.color, ";
        $parts = array("'index.php?option=com_thm_organizer&view=teacher_edit&id='","t.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);
        $query->from('#__thm_organizer_teachers AS t');
        $query->leftJoin('#__thm_organizer_fields AS f ON t.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $searchFilter = $this->state->get('filter.search');
        if (!empty($searchFilter))
        {
            $search = '%' . $this->_db->escape($this->state->get('filter.search'), true) . '%';
            $query->where("(surname LIKE '$search' OR forename LIKE '$search')");
        }

        $orderBy = $this->state->get('list.ordering', $this->defaultOrdering);
        $orderDir = $this->state->get('list.direction', $this->defaultDirection);
        $query->order("$orderBy $orderDir");

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to create iterate table data
     *
     * @return  array  an array of arrays with preformatted teacher data
     */
    public function getItems()
    {
        $items = parent::getItems();
        $return = array();
        if (empty($items))
        {
            return $return;
        }

        $index = 0;
        foreach ($items as $item)
        {
            $return[$index] = array();
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['surname'] = JHtml::_('link', $item->link, $item->surname);
            $forename = empty($item->forename)? '' : $item->forename;
            $return[$index]['forename'] = JHtml::_('link', $item->link, $forename);
            $username = empty($item->username)? '' : $item->username;
            $return[$index]['username'] = JHtml::_('link', $item->link, $username);
            $gpuntisID = empty($item->gpuntisID)? '' : $item->gpuntisID;
            $return[$index]['untisID'] = JHtml::_('link', $item->link, $gpuntisID);
            if (!empty($item->field))
            {
                if (!empty($item->color))
                {
                    $return[$index]['fieldID'] = THM_ComponentHelper::getColorField($item->field, $item->color);
                }
                else
                {
                    $return[$index]['fieldID'] = $item->field;
                }
            }
            else
            {
                $return[$index]['fieldID'] = '';
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
        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers = array();
        $headers['checkbox'] = '';
        $headers['surname'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_SURNAME', 't.surname', $direction, $ordering);
        $headers['forename'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FORENAME', 't.forename', $direction, $ordering);
        $headers['username'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_USERNAME', 't.username', $direction, $ordering);
        $headers['untisID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_GPUNTISID', 't.gpuntisID', $direction, $ordering);
        $headers['fieldID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'f.field', $direction, $ordering);

        return $headers;
    }
}
