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
use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads a filtered set of buildings into the display context.
 */
class Buildings extends ListView
{
    const OWNED = 1;
    const RENTED = 2;
    const USED = 3;

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_BUILDINGS_TITLE'), 'home-2');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'building.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'building.edit', true);
        $toolbar->appendButton(
            'Confirm', Languages::_('THM_ORGANIZER_DELETE_CONFIRM'), 'delete',
            Languages::_('THM_ORGANIZER_DELETE'), 'building.delete', true
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
        $direction               = $this->state->get('list.direction');
        $headers                 = [];
        $headers['checkbox']     = '';
        $headers['name']         = HTML::sort('NAME', 'name', $direction, 'name');
        $headers['campusID']     = Languages::_('THM_ORGANIZER_CAMPUS');
        $headers['propertyType'] = Languages::_('THM_ORGANIZER_PROPERTY_TYPE');
        $headers['address']      = Languages::_('THM_ORGANIZER_ADDRESS');

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
            $processedItems[$index]             = [];
            $processedItems[$index]['checkbox'] = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']     = HTML::_('link', $item->link, $item->name);
            $campusName                         = Campuses::getName($item->campusID);
            $processedItems[$index]['campusID'] = HTML::_('link', $item->link, $campusName);

            switch ($item->propertyType) {
                case self::OWNED:
                    $propertyType = Languages::_('THM_ORGANIZER_OWNED');
                    break;

                case self::RENTED:
                    $propertyType = Languages::_('THM_ORGANIZER_RENTED');
                    break;

                case self::USED:
                    $propertyType = Languages::_('THM_ORGANIZER_USED');
                    break;

                default:
                    $propertyType = Languages::_('THM_ORGANIZER_UNKNOWN');
                    break;
            }

            $processedItems[$index]['propertyType'] = HTML::_('link', $item->link, $propertyType);
            $processedItems[$index]['address']      = HTML::_('link', $item->link, $item->address);
            $index++;
        }

        $this->items = $processedItems;
    }
}
