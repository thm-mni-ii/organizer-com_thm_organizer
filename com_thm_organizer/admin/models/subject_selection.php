<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerSubject_Selection
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class THM_OrganizerModelSubject_Selection for component com_thm_organizer
 * Class provides methods to deal adding subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
*/
class THM_OrganizerModelSubject_Selection extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    public $programs = null;

    public $pools = null;

    protected function _getListQuery()
    {
        $dbo = JFactory::getDBO();
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        // Create the sql query
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, externalID, name_$shortTag AS name ";
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');

        $searchFields = array('name_de', 'short_name_de', 'abbreviation_de', 'name_en', 'short_name_en',
                              'abbreviation_en', 'externalID', 'description_de', 'objective_de', 'content_de',
                              'description_en', 'objective_en', 'content_en'
        );
        $this->setSearchFilter($query, $searchFields);
        $this->setValueFilters($query, array('externalID', 'fieldID'));
        $this->setLocalizedFilters($query, array('name'));

        $programID = $this->state->get('list.programID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('list.poolID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $poolID, 'pool', 'subject');

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @param   string  $idColumn  the main id column of the list query
     *
     * @return  integer  The total number of items available in the data set.
     */
    public function getTotal()
    {
        return parent::getTotal('s.id');
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
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
            $return[$index]['name'] = $item->name;
            $return[$index]['externalID'] = $item->externalID;
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
        if ($this->actions->{'core.edit'} OR $this->actions->{'core.delete'})
        {
            $headers['checkbox'] = '';
        }

        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['externalID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_EXTERNAL_ID', 'externalID', $direction, $ordering);

        return $headers;
    }

    /**
     * Overwrites the JModelList populateState function
     *
     * @param   string  $ordering   the column by which the table is should be ordered
     * @param   string  $direction  the direction in which this column should be ordered
     *
     * @return  void  sets object state variables
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $session = JFactory::getSession();
        $session->clear('programID');
        $formProgramID = $this->state->get('list.programID', '');
        if (!empty($formProgramID))
        {
            $session->set('programID', $formProgramID);
        }
    }
}
