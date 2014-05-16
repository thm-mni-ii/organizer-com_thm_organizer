<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerViewcategory_edit
 * @description category edit view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Class loading persistent data into the view context
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 */
class THM_OrganizerViewCategory_Edit extends JViewLegacy
{
    /**
     * loads model data into view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return void
     */
    public function display($tpl = null)
    {
        if (!JFactory::getUser()->authorise('core.admin'))
        {
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        }

        JHtml::_('behavior.framework', true);
        JHtml::_('behavior.formvalidation');
        JHtml::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet(JURI::root() . 'media/com_thm_organizer/css/thm_organizer.css');

        $this->form = $this->get('Form');
        $this->addToolBar();

        parent::display($tpl);
    }

    /**
     * generates joomla toolbar elements
     *
     * @return void
     */
    private function addToolBar()
    {
        JRequest::setVar('hidemainmenu', true);
        if ($this->form->getValue('id') == 0)
        {
            $title = JText::_('COM_THM_ORGANIZER_CAT_NEW_TITLE');
            $applyText = JText::_('COM_THM_ORGANIZER_APPLY_NEW');
            $cancelText = JText::_('JTOOLBAR_CANCEL');
        }
        else
        {
            $title = JText::_('COM_THM_ORGANIZER_CAT_EDIT_TITLE');
            $applyText = JText::_('COM_THM_ORGANIZER_APPLY_EDIT');
            $cancelText = JText::_('JTOOLBAR_CLOSE');
        }
        JToolbarHelper::title($title, 'organizer_categories');
        JToolbarHelper::apply('category.apply', $applyText);
        JToolbarHelper::save('category.save');
        JToolbarHelper::save2new('category.save2new');
        JToolbarHelper::cancel('category.cancel', $cancelText);
    }
}
