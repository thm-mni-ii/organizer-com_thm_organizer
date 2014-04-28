<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        room select view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Provides a form for room selection
 *
 * @category  Joomla.Component.Site
 * @package   thm_organizer
 */
class THM_OrganizerViewRoom_Select extends JViewLegacy
{
    /**
     * Sets context variables for output
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');

        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_edit.js'));
        $document->setTitle(JText::_('COM_THM_ORGANIZER_RS_TITLE'));

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        parent::display($tpl);
    }
}
