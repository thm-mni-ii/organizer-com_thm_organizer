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
use Organizer\Helpers\OrganizerHelper;

/**
 * Class loads persistent information a filtered set of subjects into the display context.
 */
class Subject_Manager extends ListView
{
    const ALPHA = 0;

    const NUMBER = 1;

    const POOL = 2;

    const TEACHER = 3;

    private $administration = true;

    private $documentAccess = false;

    /**
     * Sets Joomla view title and action buttons
     *
     * @return void
     */
    protected function addToolBar()
    {
        HTML::setTitle(Languages::_('THM_ORGANIZER_SUBJECT_MANAGER'), 'book');
        if ($this->documentAccess) {
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

            if (OrganizerHelper::getApplication()->isClient('administrator') and Access::isAdmin()) {
                HTML::setPreferencesButton();
            }
        }
    }

    /**
     * Function determines whether the user may access the view.
     *
     * @return bool true if the use may access the view, otherwise false
     */
    protected function allowAccess()
    {
        $this->administration = OrganizerHelper::getApplication()->isClient('administrator');
        $this->documentAccess = Access::allowDocumentAccess();

        return $this->administration ? $this->documentAccess : true;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $direction = $this->state->get('list.direction');
        $ordering  = $this->state->get('list.ordering');
        $grouping  = $this->state->get('list.grouping');
        $headers   = [];

        $headers['checkbox'] = '';
        if ($this->administration) {
            $headers['name'] = HTML::sort('NAME', 'name', $direction, $ordering);
        } else {
            $headers['name'] = Languages::_('THM_ORGANIZER_NAME');
        }

        if ($grouping == self::TEACHER) {
            $headers['responsibility'] = Languages::_('THM_ORGANIZER_RESPONSIBILITY');
        } else {
            $headers['teachers'] = Languages::_('THM_ORGANIZER_TEACHERS');
        }
        $headers['creditpoints'] = Languages::_('THM_ORGANIZER_CREDIT_POINTS');

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

        $editIcon       = '<span class="icon-edit"></span>';
        $grouping       = $this->administration ? '0' : $this->state->get('list.grouping');
        $index          = 0;
        $processedItems = [];

        foreach ($this->items as $item) {
            $access   = Access::allowSubjectAccess($item->id);
            $editLink = $item->url . '&view=subject_edit';
            if ($grouping == '1') {
                $name = empty($item->externalID) ? '' : "$item->externalID - ";
                $name .= $item->name;
            } else {
                $name = $item->name;
                $name .= empty($item->externalID) ? '' : " ($item->externalID)";
            }
            $itemLink               = HTML::_('link', $item->url . '&view=subject_details', $name);
            $processedItems[$index] = [];

            if ($access) {
                $processedItems[$index]['checkbox'] = HTML::_('grid.id', $index, $item->id);
                $processedItems[$index]['name']     = $this->administration ?
                    HTML::_('link', $editLink, $name) : $itemLink . HTML::_('link', $editLink, $editIcon);
            } else {
                $processedItems[$index]['checkbox'] = '';
                $processedItems[$index]['name']     = $itemLink;
            }

            if ($grouping == self::TEACHER) {
                $processedItems[$index]['responsibility'] = 'responsibility display';
            } else {
                $processedItems[$index]['teachers'] = 'teachers display';
            }
            $processedItems[$index]['creditpoints'] = empty($item->creditpoints) ? '' : $item->creditpoints;

            $index++;
        }

        $this->items = $processedItems;
    }
}
