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
     * public function save_semester
     *
     * saves the details of a semester to the database and redirects to the semster manager view
     */
    public function apply()
    {
        $model = $this->getModel('semester');
        $semesterID = $model->save();
        if($semesterID)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SM_APPLY_SUCCESS");
            $url = "index.php?option=com_thm_organizer&view=semester_edit&tmpl=component&semesterID=$semesterID";
            $this->setRedirect( $url, $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SM_APPLY_FAIL");
            $url = "index.php?option=com_thm_organizer&view=semester_edit&&tmpl=component&semesterID=0";
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
        $model = $this->getModel('semester_edit');
        $result = $model->delete();
        if($result)
        {
            $msg = JText::_("COM_THM_ORGANIZER_SM_DELETE_SUCCESS");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg);
        }
        else
        {
            $msg = JText::_("COM_THM_ORGANIZER_SM_DELETE_FAIL");
            $this->setRedirect( 'index.php?option=com_thm_organizer&view=semester_manager', $msg, 'error');
        }
    }
}