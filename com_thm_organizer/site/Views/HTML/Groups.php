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

use Exception;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
use Organizer\Helpers\Grids;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of (scheduled subject) pools into the display context.
 */
class Groups extends ListView
{
    public $batch;

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_GROUPS_TITLE'), 'list-2');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'group.edit', true);

        $if          = "alert('" . Languages::_('THM_ORGANIZER_LIST_SELECTION_WARNING') . "');";
        $else        = "jQuery('#modal-publishing').modal('show'); return true;";
        $script      = 'onclick="if(document.adminForm.boxchecked.value==0){' . $if . '}else{' . $else . '}"';
        $batchButton = '<button id="group-publishing" data-toggle="modal" class="btn btn-small" ' . $script . '>';

        $title       = Languages::_('THM_ORGANIZER_BATCH');
        $batchButton .= '<span class="icon-stack" title="' . $title . '"></span>' . " $title";

        $batchButton .= '</button>';

        $toolbar->appendButton('Custom', $batchButton, 'batch');

        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard',
                'attachment',
                Languages::_('THM_ORGANIZER_MERGE'),
                'group.mergeView',
                true
            );
            $toolbar->appendButton(
                'Standard',
                'eye-open',
                Languages::_('THM_ORGANIZER_PUBLISH_EXPIRED_TERMS'),
                'group.publishPast',
                false
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
     * Method to get display
     *
     * @param Object $tpl template  (default: null)
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        // Set batch template path
        $this->batch = ['batch_group_publishing'];

        parent::display($tpl);
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    protected function getHeaders()
    {
        $ordering             = $this->state->get('list.ordering');
        $direction            = $this->state->get('list.direction');
        $headers              = [];
        $headers['checkbox']  = '';
        $headers['full_name'] = HTML::sort('FULL_NAME', 'ppl.full_name', $direction, $ordering);
        $headers['name']      = HTML::sort('SELECT_BOX_DISPLAY', 'ppl.name', $direction, $ordering);
        $headers['grid']      = Languages::_('THM_ORGANIZER_GRID');
        $headers['untisID']   = HTML::sort('UNTIS_ID', 'ppl.untisID', $direction, $ordering);

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
        $link           = 'index.php?option=com_thm_organizer&view=group_edit&id=';
        $processedItems = [];

        foreach ($this->items as $item) {
            $thisLink                            = $link . $item->id;
            $processedItems[$index]              = [];
            $processedItems[$index]['checkbox']  = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['full_name'] = HTML::_('link', $thisLink, $item->full_name);
            $processedItems[$index]['name']      = HTML::_('link', $thisLink, $item->name);
            $gridName                            = empty($item->gridID) ? '' : Grids::getName($item->gridID);
            $processedItems[$index]['grid']      = HTML::_('link', $thisLink, $gridName);
            $processedItems[$index]['untisID']   = HTML::_('link', $thisLink, $item->untisID);
            $index++;
        }

        $this->items = $processedItems;
    }
}
