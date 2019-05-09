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
 * Class loads persistent information a filtered set of degrees into the display context.
 */
class Degree_Manager extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_DEGREE_MANAGER'), 'info-cap');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'degree.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'degree.edit', true);
        $toolbar->appendButton(
            'Confirm', Languages::_('THM_ORGANIZER_DELETE_CONFIRM'), 'delete',
            Languages::_('THM_ORGANIZER_DELETE'), 'degree.delete', true
        );
        HTML::setPreferencesButton();
    }

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    protected function allowAccess()
    {
        return Access::isAdmin();
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering            = $this->state->get('list.ordering');
        $direction           = $this->state->get('list.direction');
        $headers             = [];
        $headers['checkbox'] = '';

        $headers['name'] = HTML::sort('NAME', 'name', $direction, $ordering);

        $headers['abbreviation']
            = HTML::sort('ABBREVIATION', 'abbreviation', $direction, $ordering);

        $headers['code'] = HTML::sort('DEGREE_CODE', 'code', $direction, $ordering);

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

        $index = 0;
        $processedItems = [];

        foreach ($this->items as $item) {
            $processedItems[$index]                 = [];
            $processedItems[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']         = HTML::_('link', $item->link, $item->name);
            $processedItems[$index]['abbreviation'] = HTML::_('link', $item->link, $item->abbreviation);
            $processedItems[$index]['code']         = HTML::_('link', $item->link, $item->code);
            $index++;
        }

        $this->items = $processedItems;
    }
}
