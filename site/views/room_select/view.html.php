<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room selection view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
class thm_organizerViewroom_select extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();

        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');

        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_edit.js'));
        $document->setTitle(JText::_('COM_THM_ORGANIZER_RS_TITLE'));

        $this->form = $this->get('Form');
        $item->item = $this->get('Item');

        parent::display($tpl);
    }
}