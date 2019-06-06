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
use Organizer\Helpers\Campuses as CampusesHelper;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of campuses into the display context.
 */
class Campuses extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_CAMPUSES_TITLE'), 'location');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'campus.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'campus.edit', true);
        $toolbar->appendButton(
            'Confirm', Languages::_('THM_ORGANIZER_DELETE_CONFIRM'), 'delete',
            Languages::_('THM_ORGANIZER_DELETE'), 'campus.delete', true
        );
        if (Access::isAdmin()) {
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
        $headers             = [];
        $headers['checkbox'] = '';
        $headers['name']     = Languages::_('THM_ORGANIZER_NAME');
        $headers['address']  = Languages::_('THM_ORGANIZER_ADDRESS');
        $headers['location'] = Languages::_('THM_ORGANIZER_LOCATION');
        $headers['gridID']   = Languages::_('THM_ORGANIZER_GRID');

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

        $processedItems = [];

        foreach ($this->items as $item) {
            if (empty($item->parentID)) {
                $index = $item->name;
                $name  = $item->name;
            } else {
                $index = "{$item->parentName}-{$item->name}";
                $name  = "|&nbsp;&nbsp;-&nbsp;{$item->name}";
            }

            $processedItems[$index]             = [];
            $processedItems[$index]['checkbox'] = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']     = HTML::_('link', $item->link, $name);

            $address    = '';
            $ownAddress = (!empty($item->address) or !empty($item->city) or !empty($item->zipCode));

            if ($ownAddress) {
                $addressParts   = [];
                $addressParts[] = empty($item->address) ? empty($item->parentAddress) ? '' : $item->parentAddress : $item->address;
                $addressParts[] = empty($item->city) ? empty($item->parentCity) ? '' : $item->parentCity : $item->city;
                $addressParts[] = empty($item->zipCode) ? empty($item->parentZIPCode) ? '' : $item->parentZIPCode : $item->zipCode;
                $address        = implode(' ', $addressParts);
            }

            $processedItems[$index]['address']  = $address;
            $processedItems[$index]['location'] = CampusesHelper::getPin($item->location);

            if (!empty($item->gridName)) {
                $gridName = $item->gridName;
            } elseif (!empty($item->parentGridName)) {
                $gridName = $item->parentGridName;
            } else {
                $gridName = Languages::_('JNONE');
            }
            $processedItems[$index]['gridID'] = $gridName;
        }

        asort($processedItems);

        $this->items = $processedItems;
    }
}
