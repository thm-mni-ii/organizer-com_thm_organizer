<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Views\HTML;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Access;
use Organizer\Helpers\Fields;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Mappings;

/**
 * Class loads pool information into the display context.
 */
class Pool_Selection extends ListView
{
    protected $_layout = 'pool_selection';

    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        $toolbar = Toolbar::getInstance();
        $toolbar->appendButton('Standard', 'new', Languages::_('THM_ORGANIZER_ADD'), 'program.addPool', true);
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

        $headers              = [];
        $headers['checkbox']  = '';
        $headers['name']      = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['programID'] = Languages::_('THM_ORGANIZER_PROGRAM');
        $headers['fieldID']   = HTML::sort('FIELD', 'field', $direction, $ordering);

        return $headers;
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();
        HTML::_('jquery.framework');
        HTML::_('searchtools.form', '#adminForm', []);

        $document = Factory::getDocument();
        $document->addStyleSheet(Uri::root() . 'components/com_thm_organizer/css/child_selection.css');
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
            $processedItems[$index]['name']      = $item->name;
            $programName                         = Mappings::getProgramName('pool', $item->id);
            $processedItems[$index]['programID'] = $programName;
            $processedItems[$index]['fieldID']   = Fields::getListDisplay($item->fieldID);
            $index++;
        }

        $this->items = $processedItems;
    }
}
