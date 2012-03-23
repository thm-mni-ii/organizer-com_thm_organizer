<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @description provides a form for editing semester information
 * @author      Wolf Normann Gordian Rost wolfDOTrostATmniDOTthmDOTde
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersViewSettings extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        
        $model = $this->getModel();
        $settings = $model->getSettings();
        $this->assignRef('settings', $settings);
        $categories = ($model->getCategories())? $model->getCategories() : array("no category found!");
        $attribs = 'size="1" class="inputbox"';
        if($categories !== false AND $settings !== false)
            $this->assignRef('categories', JHTML::_('select.genericlist', $categories, 'scheduler_vacationcat',$attribs, 'id', 'name', $settings[0]->vacationcat));
        else
            $this->assignRef('categories', JHTML::_('select.genericlist', $categories, 'scheduler_vacationcat',$attribs, 'id', 'name'));

        $title = JText::_('COM_THM_ORGANIZER').': '.JText::_('COM_THM_ORGANIZER_COM_NAME');        
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::save('settings.save');
        JToolBarHelper::back();
        
        parent::display($tpl);
    }
}
