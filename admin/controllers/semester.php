<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester controller
 * @description exectutes tasks from the semester manager and semester edit views
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
class thm_organizersControllerSemester extends JController
{
    function display(){ parent::display(); }

    function __construct()
    {
        parent::__construct();
        $this->registerTask( 'new', 'edit' );
    }

    function edit()
    {
        JRequest::setVar( 'view', 'semester_edit' );
        parent::display();
    }
	
    public function save()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->store();
        if($result)
        {
            $msg = JText::_("The semester entry has been saved successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the semester entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
        }
    }

    public function save2new()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->store();
        if($result)
        {
            $msg = JText::_("The semester entry has been saved successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_edit&semesterID=0', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while saving the semester entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_edit&semesterID=0', $msg, 'error');
        }
    }
	
    function delete()
    {
        $model = $this->getModel('semester_edit');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_("The semester entry has been deleted successfully.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg);
        }
        else
        {
            $msg = JText::_("An error has occurred while deleting the semester entry.");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
        }
        $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_list', $msg);
    }

    public function cancel()
    {
        JRequest::setVar( 'view', 'semester_manager' );
        parent::display();
    }
}