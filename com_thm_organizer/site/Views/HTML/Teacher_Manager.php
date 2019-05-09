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
use Organizer\Helpers\Teachers;

/**
 * Class loads persistent information a filtered set of teachers into the display context.
 */
class Teacher_Manager extends ListView
{
    public $items;

    public $pagination;

    public $state;

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_TEACHER_MANAGER'), 'users');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'teacher.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'teacher.edit', true);
        if (Access::isAdmin()) {
            $toolbar->appendButton(
                'Standard', 'attachment', Languages::_('THM_ORGANIZER_MERGE'), 'teacher.mergeView', true
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
        return Access::allowHRAccess();
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $headers                 = [];
        $headers['checkbox']     = '';
        $headers['surname']      = Languages::_('THM_ORGANIZER_SURNAME');
        $headers['forename']     = Languages::_('THM_ORGANIZER_FORENAME');
        $headers['username']     = Languages::_('THM_ORGANIZER_USERNAME');
        $headers['t.gpuntisID']  = Languages::_('THM_ORGANIZER_GPUNTISID');
        $headers['departmentID'] = Languages::_('THM_ORGANIZER_DEPARTMENT');

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
            $itemForename  = empty($item->forename) ? '' : $item->forename;
            $itemUsername  = empty($item->username) ? '' : $item->username;
            $itemGPUntisID = empty($item->gpuntisID) ? '' : $item->gpuntisID;

            $processedItems[$index]                = [];
            $processedItems[$index]['checkbox']    = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['surname']     = HTML::_('link', $item->link, $item->surname);
            $processedItems[$index]['forename']    = HTML::_('link', $item->link, $itemForename);
            $processedItems[$index]['username']    = HTML::_('link', $item->link, $itemUsername);
            $processedItems[$index]['t.gpuntisID'] = HTML::_('link', $item->link, $itemGPUntisID);

            $departments = Teachers::getDepartmentNames($item->id);

            if (empty($departments)) {
                $processedItems[$index]['departmentID'] = Languages::_('JNONE');
            } elseif (count($departments) === 1) {
                $processedItems[$index]['departmentID'] = $departments[0];
            } else {
                $processedItems[$index]['departmentID'] = Languages::_('THM_ORGANIZER_MULTIPLE_DEPARTMENTS');
            }

            $index++;
        }

        $this->items = $processedItems;
    }
}
