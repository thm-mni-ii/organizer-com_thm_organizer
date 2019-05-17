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
 * Class loads persistent information a filtered set of (scheduled degree) programs (or organizational groupings) into the display context.
 */
class Plan_Program_Manager extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_CATEGORY_MANAGER'), 'list');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'plan_program.edit', true);
        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard', 'attachment', Languages::_('THM_ORGANIZER_MERGE'), 'plan_program.mergeView', true
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
        return Access::allowSchedulingAccess();
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

        $headers['checkbox']  = '';
        $headers['gpuntisID'] = HTML::sort('GPUNTISID', 'ppr.gpuntisID', $direction, $ordering);
        $headers['name']      = HTML::sort('DISPLAY_NAME', 'ppr.name', $direction, $ordering);

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
            $processedItems[$index]              = [];
            $processedItems[$index]['checkbox']  = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['gpuntisID'] = HTML::_('link', $item->link, $item->gpuntisID);
            $processedItems[$index]['name']      = HTML::_('link', $item->link, $item->name);
            $index++;
        }

        $this->items = $processedItems;
    }
}
