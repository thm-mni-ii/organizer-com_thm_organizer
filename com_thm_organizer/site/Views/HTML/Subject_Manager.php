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
 * Class loads persistent information a filtered set of subjects into the display context.
 */
class Subject_Manager extends ListView
{
    /**
     * Sets Joomla view title and action buttons
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_SUBJECT_MANAGER'), 'book');
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', 'THM_ORGANIZER_ADD', 'subject.add', false);
        $toolbar->appendButton('Standard', 'edit', 'THM_ORGANIZER_EDIT', 'subject.edit', true);
        $toolbar->appendButton(
            'Standard', 'upload', Languages::_('THM_ORGANIZER_IMPORT_LSF'), 'subject.importLSFData', true
        );
        $toolbar->appendButton(
            'Confirm', Languages::_('THM_ORGANIZER_DELETE_CONFIRM'), 'delete',
            Languages::_('THM_ORGANIZER_DELETE'), 'subject.delete', true
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

        $headers['checkbox']   = '';
        $headers['name']       = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['externalID'] = HTML::sort('EXTERNAL_ID', 'externalID', $direction, $ordering);
        $headers['field']      = HTML::sort('FIELD', 'field', $direction, $ordering);

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
            $processedItems[$index]               = [];
            $processedItems[$index]['checkbox']   = HTML::_('grid.id', $index, $item->id);
            $processedItems[$index]['name']       = HTML::_('link', $item->link, $item->name);
            $processedItems[$index]['externalID'] = HTML::_('link', $item->link, $item->externalID);
            if (!empty($item->field)) {
                if (!empty($item->color)) {
                    $processedItems[$index]['field'] = HTML::colorField($item->field, $item->color);
                } else {
                    $processedItems[$index]['field'] = $item->field;
                }
            } else {
                $processedItems[$index]['field'] = '';
            }

            $index++;
        }

        $this->items = $processedItems;
    }
}
