<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of room types into the display context.
 */
class Roomtypes extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_ROOMTYPES_TITLE'), 'cog');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'roomtype.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'roomtype.edit', true);
        $toolbar->appendButton(
            'Confirm',
            Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
            'delete',
            Languages::_('THM_ORGANIZER_DELETE'),
            'roomtype.delete',
            true
        );

        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard',
                'attachment',
                Languages::_('THM_ORGANIZER_MERGE'),
                'roomtype.mergeView',
                true
            );
            HTML::setPreferencesButton();
        }
    }

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    protected function allowAccess()
    {
        return Access::allowFMAccess();
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering  = $this->state->get('list.ordering');
        $direction = $this->state->get('list.direction');
        $headers   = [];

        $headers['checkbox']     = '';
        $headers['untisID']      = HTML::sort('UNTIS_ID', 'untisID', $direction, $ordering);
        $headers['name']         = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['minCapacity'] = HTML::sort('MIN_CAPACITY', 'minCapacity', $direction, $ordering);
        $headers['maxCapacity'] = HTML::sort('MAX_CAPACITY', 'maxCapacity', $direction, $ordering);
        $headers['roomCount']    = HTML::sort('ROOM_COUNT', 'roomCount', $direction, $ordering);

        return $headers;
    }

    /**
     * Processes the items in a manner specific to the view, so that a generalized  output in the layout can occur.
     *
     * @return void processes the class items property
     */
    protected function preProcessItems()
    {
        if (empty($this->items)) {
            return;
        }

        $index          = 0;
        $link           = 'index.php?option=com_thm_organizer&view=roomtype_edit&id=';
        $processedItems = [];

        foreach ($this->items as $item) {
            $thisLink                               = $link . $item->id;
            $processedItems[$index]                 = [];
            $processedItems[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['untisID']      = HTML::_('link', $thisLink, $item->untisID);
            $processedItems[$index]['name']         = HTML::_('link', $thisLink, $item->name);
            $processedItems[$index]['minCapacity'] = $item->minCapacity;
            $processedItems[$index]['maxCapacity'] = $item->maxCapacity;
            $processedItems[$index]['roomCount']    = $item->roomCount;
            $index++;
        }

        $this->items = $processedItems;
    }
}