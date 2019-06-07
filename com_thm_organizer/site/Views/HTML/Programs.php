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
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

/**
 * Class loads persistent information a filtered set of degree programs into the display context.
 */
class Programs extends ListView
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_PROGRAMS_TITLE'), 'list');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'program.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'program.edit', true);
        $toolbar->appendButton(
            'Standard',
            'upload',
            Languages::_('THM_ORGANIZER_IMPORT_LSF'),
            'program.importLSFData',
            true
        );
        $toolbar->appendButton(
            'Standard',
            'loop',
            Languages::_('THM_ORGANIZER_UPDATE_SUBJECTS'),
            'program.updateLSFData',
            true
        );
        $toolbar->appendButton(
            'Confirm',
            Languages::_('THM_ORGANIZER_DELETE_CONFIRM'),
            'delete',
            Languages::_('THM_ORGANIZER_DELETE'),
            'program.delete',
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
        return Access::allowDocumentAccess();
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
        $headers['dp.name']      = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['degreeID']     = Languages::_('THM_ORGANIZER_DEGREE');
        $headers['version']      = Languages::_('THM_ORGANIZER_VERSION');
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
        $link           = 'index.php?option=com_thm_organizer&view=program_edit&id=';
        $processedItems = [];

        foreach ($this->items as $item) {
            $thisLink                               = $link . $item->id;
            $processedItems[$index]['checkbox']     = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['dp.name']      = HTML::_('link', $thisLink, $item->name);
            $processedItems[$index]['degreeID']     = HTML::_('link', $thisLink, $item->abbreviation);
            $processedItems[$index]['version']      = HTML::_('link', $thisLink, $item->version);
            $processedItems[$index]['departmentID'] = HTML::_('link', $thisLink, $item->department);
            $index++;
        }

        $this->items = $processedItems;
    }
}
