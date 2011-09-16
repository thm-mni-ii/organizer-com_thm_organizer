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
        JHtml::_('behavior.modal', 'a.modal');
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        JToolBarHelper::title( JText::_( 'Semester Manager' ), 'generic.png' );
        if(thm_organizerHelper::getActions('semester_manager')->get("core.admin"))$this->addToolBar();
        thm_organizerHelper::addSubmenu('semester_manager');
        $model = $this->getModel();
        $semesters = $model->semesters;
        $this->assignRef('semesters', $semesters);
        parent::display($tpl);
    }

    private function addToolBar()
    {
        $semesterEditLink = 'index.php?option=com_thm_organizer&view=semester_edit';
        $semesterEditLink .= '&layout=modal&tmpl=component&semesterID=0';
        $tb = JToolBar::getInstance();
        $tb->appendButton('Popup', 'new', JText::_('COM_THM_ORGANIZER_NEW'), $semesterEditLink, 600, 180 );
        JToolBarHelper::deleteList
        (
            JText::_( 'COM_THM_ORGANIZER_SM_DELETE_CONFIRM'),
            'semester.delete'
        );
    }
}
	