<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester manager view
 * @description organizes data from the model to use in the template
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewsemester_manager extends JView {

    function display($tpl = null)
    {
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->semesters = $model->semesters;

        JToolBarHelper::title( JText::_( 'Semester Manager' ), 'generic.png' );
        if(thm_organizerHelper::isAdmin('semester_manager'))
        {
            $this->addToolBar();
            thm_organizerHelper::addSubmenu('semester_manager');
        }

        parent::display($tpl);
    }

    private function addToolBar()
    {
        JToolBarHelper::addNew('semester.add');
        JToolBarHelper::editList('semester.edit');
        JToolBarHelper::deleteList
        (
            JText::_( 'COM_THM_ORGANIZER_SEM_DELETE_CONFIRM'),
            'semester.delete'
        );
    }
}
	