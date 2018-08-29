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
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class retrieves information for a filtered set of subjects. Modal view.
 *
 * @todo does so much of this need to be explicitly implemented?
 */
class THM_OrganizerModelSubject_Selection extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    public $programs = null;

    public $pools = null;

    protected function getListQuery()
    {
        $dbo      = JFactory::getDbo();
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        // Create the sql query
        $query  = $dbo->getQuery(true);
        $select = "DISTINCT s.id, externalID, name_$shortTag AS name ";
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');

        $searchFields = [
            'name_de',
            'short_name_de',
            'abbreviation_de',
            'name_en',
            'short_name_en',
            'abbreviation_en',
            'externalID',
            'description_de',
            'objective_de',
            'content_de',
            'description_en',
            'objective_en',
            'content_en'
        ];
        $this->setSearchFilter($query, $searchFields);
        $this->setValueFilters($query, ['externalID', 'fieldID']);
        $this->setLocalizedFilters($query, ['name']);

        $programID = $this->state->get('list.programID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('list.poolID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $poolID, 'pool', 'subject');

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Function to feed the data in the table body correctly to the list view
     *
     * @return array consisting of items in the body
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
            $return[$index]               = [];
            $return[$index]['checkbox']   = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name']       = $item->name;
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
        $ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers               = [];
        $headers['checkbox']   = '';
        $headers['name']       = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['externalID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_EXTERNAL_ID', 'externalID', $direction,
            $ordering);

        return $headers;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @param string $idColumn not used
     *
     * @return integer  The total number of items available in the data set.
     * @throws Exception
     */
    public function getTotal($idColumn = null)
    {
        $query = $this->getListQuery();
        $query->clear('select');
        $query->clear('order');
        $query->select('COUNT(DISTINCT s.id)');
        $dbo = JFactory::getDbo();
        $dbo->setQuery($query);

        try {
            $result = $dbo->loadResult();

            return $result;
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage($exc->getMessage());

            return null;
        }
    }

    /**
     * Overwrites the JModelList populateState function
     *
     * @param string $ordering  the column by which the table is should be ordered
     * @param string $direction the direction in which this column should be ordered
     *
     * @return void  sets object state variables
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $session = JFactory::getSession();
        $session->clear('programID');
        $formProgramID = $this->state->get('list.programID', '');
        if (!empty($formProgramID)) {
            $session->set('programID', $formProgramID);
        }
    }
}
