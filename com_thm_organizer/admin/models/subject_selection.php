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

require_once 'list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/mapping.php';

use Joomla\CMS\Factory;

/**
 * Class retrieves information for a filtered set of subjects. Modal view.
 */
class THM_OrganizerModelSubject_Selection extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

    public $programs = null;

    public $pools = null;

    protected function getListQuery()
    {
        $dbo      = Factory::getDbo();
        $shortTag = Languages::getShortTag();

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
            $return[$index]['checkbox']   = HTML::_('grid.id', $index, $item->id);
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
        $headers   = [];

        $headers['checkbox']   = '';
        $headers['name']       = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['externalID'] = HTML::sort('EXTERNAL_ID', 'externalID', $direction, $ordering);

        return $headers;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @param string $idColumn not used
     *
     * @return integer  The total number of items available in the data set.
     */
    public function getTotal($idColumn = null)
    {
        $query = $this->getListQuery();
        $query->clear('select');
        $query->clear('order');
        $query->select('COUNT(DISTINCT s.id)');
        $dbo = Factory::getDbo();
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $session = Factory::getSession();
        $session->clear('programID');
        $formProgramID = $this->state->get('list.programID', '');
        if (!empty($formProgramID)) {
            $session->set('programID', $formProgramID);
        }
    }
}
