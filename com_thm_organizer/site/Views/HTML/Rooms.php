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
 * Class loads persistent information a filtered set of rooms into the display context.
 */
class Rooms extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_ROOMS_TITLE'), 'enter');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'room.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'room.edit', true);

        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard',
                'attachment',
                Languages::_('THM_ORGANIZER_MERGE'),
                'room.mergeView',
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

        $headers['checkbox']   = '';
        $headers['name']       = HTML::sort('NAME', 'r.name', $direction, $ordering);
        $headers['buildingID'] = HTML::sort('BUILDING', 'buildingName', $direction, $ordering);
        $headers['roomtypeID']     = HTML::sort('TYPE', 'roomtype', $direction, $ordering);

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
        $processedItems = [];

        foreach ($this->items as $item) {
            $processedItems[$index]               = [];
            $processedItems[$index]['checkbox']   = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']       = HTML::_('link', $item->link, $item->name);
            $processedItems[$index]['buildingID'] = HTML::_('link', $item->link, $item->buildingName);
            $processedItems[$index]['roomtypeID'] = HTML::_('link', $item->link, $item->roomtype);
            $index++;
        }

        $this->items = $processedItems;
    }
}
