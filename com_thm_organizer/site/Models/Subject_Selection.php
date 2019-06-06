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

use Joomla\CMS\Factory;
use Organizer\Helpers\Mappings;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class retrieves information for a filtered set of subjects. Modal view.
 */
class Subject_Selection extends ListModel
{
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

        $programID = $this->state->get('list.programID', '');
        Mappings::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('list.poolID', '');
        Mappings::setResourceIDFilter($query, $poolID, 'pool', 'subject');

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
}
