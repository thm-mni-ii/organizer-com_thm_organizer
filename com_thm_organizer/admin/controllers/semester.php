<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester controller
 * @description exectutes tasks from the semester manager and semester edit views
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';
class thm_organizersControllerSemester extends JController
{
    function display(){ parent::display(); }

    /**
     * add
     *
     * redirects to the semester edit view for the creation of a new semester
     */
    public function add()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        JRequest::setVar( 'view', 'semester_edit' );
        JRequest::setVar( 'semesterID', '0' );
        parent::display();
    }

    /**
     * edit
     *
     * redirects to the semester edit view to edit an existing semester
     */
    public function edit()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        JRequest::setVar( 'view', 'semester_edit' );
        parent::display();
    }

    /**
     * apply
     *
     * saves semester details and redirects to the semester edit view of the
     * current semester
     */
    public function apply()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        $url = "index.php?option=com_thm_organizer&view=semester_edit&semesterID=";
        $model = $this->getModel('semester');
        $semesterID = $model->save();
        if($semesterID)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_SAVE_SUCCESS");
            $this->setRedirect( $url.$semesterID, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_SAVE_FAIL");
            $this->setRedirect( $url."0", $msg, 'error');
        }
    }

    /**
     * save
     *
     * saves semester details and redirects to the semester manager view
     */
    public function save()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        $url = "index.php?option=com_thm_organizer&view=semester_manager";
        $model = $this->getModel('semester');
        $semesterID = $model->save();
        if($semesterID)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_SAVE_SUCCESS");
            $this->setRedirect( $url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_SAVE_FAIL");
            $this->setRedirect( $url, $msg, 'error');
        }
    }

    /**
     * save2new
     *
     * saves semester details and redirects to an empty semester edit view
     */
    public function save2new()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        $url = "index.php?option=com_thm_organizer&view=semester_edit&semesterID=0";
        $model = $this->getModel('semester');
        $semesterID = $model->save();
        if($semesterID)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_SAVE_SUCCESS");
            $this->setRedirect( $url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_SAVE_FAIL");
            $this->setRedirect( $url, $msg, 'error');
        }
    }

    /**
     * public function delete_semester
     *
     * removes the semester
     */
    function delete()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        $model = $this->getModel('semester');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_DELETE_SUCCESS");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SEM_DELETE_FAIL");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
        }
    }

    /**
     * cancel
     *
     * redirects to the semester manager view
     */
    public function cancel()
    {
        if(!thm_organizerHelper::isAdmin('semester')) thm_organizerHelper::noAccess ();
        $url = "index.php?option=com_thm_organizer&view=semester_manager";
        $this->setRedirect( $url);
    }
}