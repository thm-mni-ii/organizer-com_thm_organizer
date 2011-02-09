<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager view
 * @description organizes data from the model to use in the template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewsemester_manager extends JView {

    function display($tpl = null)
    {
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $this->addToolBar();
        thm_organizerHelper::addSubmenu('semester_manager');
        $model = $this->getModel();
        $semesters = $model->semesters;
        $this->assignRef('semesters', $semesters);
        parent::display($tpl);
    }

    private function addToolBar()
    {
        JToolBarHelper::title( JText::_( 'Semester Manager' ), 'generic.png' );
        $allowedActions = thm_organizerHelper::getActions('monitor_manager');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
        {
            if($allowedActions->get("core.admin") or $allowedActions->get("core.create"))
                    JToolBarHelper::addNew( 'semester.new' );
            if($allowedActions->get("core.admin") or $allowedActions->get("core.edit"))
                    JToolBarHelper::editList('semester.edit');
            if($allowedActions->get("core.admin") or $allowedActions->get("core.delete"))
                    JToolBarHelper::deleteList( JText::_('Are you sure you wish to delete the marked entries?'), 'semester.delete');
        }
    }
}
	