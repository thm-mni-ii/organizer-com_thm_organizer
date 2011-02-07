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
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewmonitor_manager extends JView
{

    public function display($tpl = null)
    {
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $this->addToolBar();
        thm_organizerHelper::addSubmenu('monitor_manager');
        $model = $this->getModel();
        $monitors = $model->monitors;
        $this->assignRef( 'monitors', $monitors );
        parent::display($tpl);
    }

    private function addToolBar()
    {
        JToolBarHelper::title( JText::_( 'Monitor Manager' ), 'generic.png' );
        $allowedActions = thm_organizerHelper::getActions('monitor_manager');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
        {
            if($allowedActions->get("core.admin") or $allowedActions->get("core.create"))
                    JToolBarHelper::addNew( 'monitor.new' );
            if($allowedActions->get("core.admin") or $allowedActions->get("core.edit"))
                    JToolBarHelper::editList('monitor.edit');
            if($allowedActions->get("core.admin") or $allowedActions->get("core.delete"))
                    JToolBarHelper::deleteList( JText::_('Are you sure you wish to delete the marked entries?'), 'monitor.delete');
        }
    }

}
	