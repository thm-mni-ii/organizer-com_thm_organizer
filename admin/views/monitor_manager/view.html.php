<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        view monitor_manager
 * @description lists registered monitors along with associated rooms and display content
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersViewmonitor_manager extends JView
{

    public function display($tpl = null)
    {
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->monitors = $model->monitors;
        $this->access = thm_organizerHelper::isAdmin('monitor_manager');
        JToolBarHelper::title( JText::_( 'COM_THM_ORGANIZER_MON_TITLE' ), 'generic.png' );
        if($this->access)
        {
            $this->addToolBar();
            thm_organizerHelper::addSubmenu('monitor_manager');
        }

        parent::display($tpl);
    }

    /**
     * addToolBar
     *
     * creates the toolbar for user actions
     */
    private function addToolBar()
    {
        JToolBarHelper::addNew( 'monitor.new' );
        JToolBarHelper::editList('monitor.edit');
        JToolBarHelper::deleteList( JText::_('COM_THM_ORGANIZER_MON_DELETE_CONFIRM'), 'monitor.delete');
    }

}
	