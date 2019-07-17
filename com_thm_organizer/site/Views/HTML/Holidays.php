<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2019 TH Mittelhessen
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
 * Class loads persistent information a filtered set of holidays into the display context.
 */
class Holidays extends ListView
{
    const OPTIONAL = 1, PARTIAL  = 2, BLOCKING = 3;

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_HOLIDAYS_TITLE'), 'calendar');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'holiday.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'holiday.edit', true);
        $toolbar->appendButton(
            'Confirm',
            Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
            'delete',
            Languages::_('THM_ORGANIZER_DELETE'),
            'holiday.delete',
            true
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
        return Access::isAdmin();
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

        $headers['checkbox']    = '';
        $headers['name']        = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['startDate']   = HTML::sort('DATE', 'startDate', $direction, $ordering);
        $headers['type']        = HTML::sort('TYPE', 'type', $direction, $ordering);
        $headers['status']      = Languages::_('THM_ORGANIZER_STATE');

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
        $link           = 'index.php?option=com_thm_organizer&view=holiday_edit&id=';
        $processedItems = [];

        foreach ($this->items as $item) {

            $today     = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime($item->startDate));
            $endDate   = date('Y-m-d', strtotime($item->endDate));
            $year = date('Y', strtotime($item->startDate));

            if ($endDate < $today) {
                $status = Languages::_('THM_ORGANIZER_EXPIRED');
            } elseif ($startDate > $today) {
                $status = Languages::_('THM_ORGANIZER_PENDING');
            } else {
                $status = Languages::_('THM_ORGANIZER_CURRENT');
            }

            $thisLink                               = $link . $item->id;
            $processedItems[$index]                 = [];
            $processedItems[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']         = HTML::_('link', $thisLink, $item->name).' ('.HTML::_('link', $thisLink, $year).')';
            $dateString = $this->getDateString($item->startDate,$item->endDate);
            $processedItems[$index]['startDate']    = HTML::_('link', $thisLink, $dateString);
            $processedItems[$index]['type']         = HTML::_('link', $thisLink,
                ($item->type == self::OPTIONAL ? Languages::_('THM_ORGANIZER_PLANNING_OPTIONAL') : ($item->type == self::PARTIAL ? Languages::_('THM_ORGANIZER_PLANNING_MANUAL')
                    : Languages::_('THM_ORGANIZER_PLANNING_BLOCKED'))));
            $processedItems[$index]['status'] = HTML::_('link', $thisLink, $status);

            $index++;
        }

        $this->items = $processedItems;
    }

    /**
     * Checks the start date and end date
     *
     * @return startDate if start date and end date are same
     *
     * @return string by concatenating start date and end date if both are not equal
     */
    private function getDateString($startDate,$endDate)
    {
        $startDate = date('d.m.Y', strtotime($startDate));
        $endDate   = date('d.m.Y', strtotime($endDate));

        if($startDate == $endDate){
            return $startDate;
        } else {

            $string = $startDate;
            $string.=" - ".$endDate;

            return $string;
        }
    }
}