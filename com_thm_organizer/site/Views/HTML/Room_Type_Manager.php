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

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of room types into the display context.
 */
class Room_Type_Manager extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_ROOM_TYPE_MANAGER'), 'cog');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'room_type.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'room_type.edit', true);
        $toolbar->appendButton(
            'Confirm', Languages::_('THM_ORGANIZER_DELETE_CONFIRM'), 'delete',
            Languages::_('THM_ORGANIZER_DELETE'), 'room_type.delete', true
        );

        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard', 'attachment', Languages::_('THM_ORGANIZER_MERGE'), 'room_type.mergeView', true
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
        $headers['gpuntisID']    = HTML::sort('GPUNTISID', 'gpuntisID', $direction, $ordering);
        $headers['name']         = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['min_capacity'] = HTML::sort('MIN_CAPACITY', 'min_capacity', $direction, $ordering);
        $headers['max_capacity'] = HTML::sort('MAX_CAPACITY', 'max_capacity', $direction, $ordering);
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
        $processedItems = [];

        foreach ($this->items as $item) {
            $processedItems[$index]                 = [];
            $processedItems[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['gpuntisID']    = HTML::_('link', $item->link, $item->gpuntisID);
            $processedItems[$index]['name']         = HTML::_('link', $item->link, $item->name);
            $processedItems[$index]['min_capacity'] = $item->min_capacity;
            $processedItems[$index]['max_capacity'] = $item->max_capacity;
            $processedItems[$index]['roomCount']    = $item->roomCount;
            $index++;
        }

        $this->items = $processedItems;
    }
}
