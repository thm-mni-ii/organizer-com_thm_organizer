<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewPool_Selection
 * @author      Alexander Boll, <alexander.boll@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/views/list.php';
JHtml::_('jquery.framework');

/**
 * Class provides methods to display the view degrees
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewPool_Selection extends THM_OrganizerViewList
{
    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JToolbarHelper::addNew('', 'COM_THM_ORGANIZER_ACTION_ADD', true);
    }

    /**
     * Adds styles and scripts to the document
     *
     * @return  void  modifies the document
     */
    protected function modifyDocument()
    {
        parent::modifyDocument();
        JHtml::_('searchtools.form', '#adminForm', array());

        $document = Jfactory::getDocument();
        $document->addStyleSheet(JUri::root() . "/media/com_thm_organizer/css/child_selection.css");
    }
}
