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

defined('_JEXEC') or die;

use \THM_OrganizerHelperHTML as HTML;

require_once JPATH_COMPONENT . '/views/list.php';

/**
 * Class loads subject information into the display context.
 */
class THM_OrganizerViewSubject_Selection extends THM_OrganizerViewList
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        \JToolbarHelper::addNew('pool.addSubject', 'COM_THM_ORGANIZER_ACTION_ADD', true);
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

        $document = \JFactory::getDocument();
        $document->addStyleSheet(\JUri::root() . '/components/com_thm_organizer/css/child_selection.css');
    }
}
