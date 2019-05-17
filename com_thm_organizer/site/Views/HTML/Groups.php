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

use Exception;
use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Access;
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

        $if          = "alert('" . Languages::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST') . "');";
        $else        = "jQuery('#modal-publishing').modal('show'); return true;";
        $script      = 'if(document.adminForm.boxchecked.value==0){' . $if . '}else{' . $else . '}';
        $batchButton = '<button id="pool-publishing" data-toggle="modal" class="btn btn-small" onclick="' . $script . '">';

        $title       = Languages::_('THM_ORGANIZER_BATCH');
        $batchButton .= '<span class="icon-stack" title="' . $title . '"></span>' . " $title";

        $batchButton .= '</button>';

        $toolbar->appendButton('Custom', $batchButton, 'batch');

        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard', 'attachment', Languages::_('THM_ORGANIZER_MERGE'), 'group.mergeView', true
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
        $headers['full_name'] = HTML::sort('NAME', 'ppl.full_name', $direction, $ordering);
        $headers['name']      = HTML::sort('SHORT_NAME', 'ppl.name', $direction, $ordering);
        $headers['gpuntisID'] = HTML::sort('GPUNTISID', 'ppl.gpuntisID', $direction, $ordering);

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
            $processedItems[$index]['full_name'] = HTML::_('link', $item->link, $item->full_name);
            $processedItems[$index]['name']      = HTML::_('link', $item->link, $item->name);
            $processedItems[$index]['gpuntisID'] = HTML::_('link', $item->link, $item->gpuntisID);
            $index++;
        }

        $this->items = $processedItems;
    }
}
