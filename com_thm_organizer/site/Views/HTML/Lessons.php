<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

use Joomla\CMS\Toolbar\Toolbar;
use Organizer\Helpers\Campuses;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class which loads data into the view output context
 */
class Lessons extends ListView
{
    public $filters = [];

    public $model = null;

    public $showFilters;

    public $state = null;

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_COURSES_TITLE'), 'contract-2');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'course.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'course.edit', true);
        $toolbar->appendButton(
            'Confirm',
            Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
            'delete',
            Languages::_('THM_ORGANIZER_DELETE'),
            'course.delete',
            true
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
        return true;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $direction             = $this->state->get('list.direction');
        $headers               = [];
        $headers['checkbox']   = '';
        $headers['name']       = HTML::sort('NAME', 'name', $direction, 'name');
        $headers['department'] = Languages::_('THM_ORGANIZER_DEPARTMENT');
        $headers['term']       = Languages::_('THM_ORGANIZER_TERM');
        $headers['status']     = Languages::_('THM_ORGANIZER_STATE');
        $headers['campus']     = Languages::_('THM_ORGANIZER_CAMPUS');

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
            $name     = empty($item->subject) ? $item->name : $item->subject;
            $name     .= empty($item->method) ? '' : " - $item->method";
            $campusID = empty($item->campusID) ? $item->abstractCampusID : $item->campusID;
            $campus   = Campuses::getName($campusID);

            $today = date('Y-m-d');
            if (empty($item->start) and empty($item->end)) {
                $status = Languages::_('THM_ORGANIZER_UNPLANNED');
            } elseif ($item->end < $today) {
                $status = Languages::_('THM_ORGANIZER_EXPIRED');
            } elseif ($item->start > $today) {
                $status = Languages::_('THM_ORGANIZER_PENDING');
            } else {
                $status = Languages::_('THM_ORGANIZER_CURRENT');
            }

            $processedItems[$index]               = [];
            $processedItems[$index]['checkbox']   = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']       = HTML::_('link', $item->link, $name);
            $processedItems[$index]['department'] = HTML::_('link', $item->link, $item->department);
            $processedItems[$index]['term']       = HTML::_('link', $item->link, $item->term);
            $processedItems[$index]['status']     = HTML::_('link', $item->link, $status);
            $processedItems[$index]['campus']     = HTML::_('link', $item->link, $campus);

            $index++;
        }

        $this->items = $processedItems;
    }
}
