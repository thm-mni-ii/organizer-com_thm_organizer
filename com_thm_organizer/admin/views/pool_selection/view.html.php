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
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';
JHtml::_('jquery.framework');

/**
 * Class loads pool information into the display context.
 */
class THM_OrganizerViewPool_Selection extends THM_OrganizerViewList
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return void
     */
    protected function addToolBar()
    {
        JToolbarHelper::addNew('', 'COM_THM_ORGANIZER_ACTION_ADD', true);
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return void  modifies the document
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();
        JHtml::_('searchtools.form', '#adminForm', []);

        $document = JFactory::getDocument();
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/child_selection.css");
    }
}
