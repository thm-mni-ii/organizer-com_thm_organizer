<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewColor_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
include_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/editview.php';

/**
 * Class loads persistent color information into display context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerViewColor_Edit extends JViewLegacy
{
    /**
     * Method to get display
     *
     * @param   Object  $tpl  template  (default: null)
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        THM_OrganizerHelperEditView::setStandardEditView($this);
        $this->addToolBar();
        parent::display($tpl);
    }

    /**
     * Method to generate buttons for user interaction
     *
     * @return  void
     */
    protected function addToolBar()
    {
        JRequest::setVar('hidemainmenu', true);
        $isNew = ($this->item->id == 0);
        $title = $isNew? JText::_('COM_THM_ORGANIZER_CLM_NEW_TITLE') : JText::_('COM_THM_ORGANIZER_CLM_EDIT_TITLE');
        JToolbarHelper::title($title, 'organizer_colors');
        JToolbarHelper::save('color.save');
        JToolbarHelper::cancel('color.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
