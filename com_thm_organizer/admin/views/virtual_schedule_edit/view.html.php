<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        form for the editing of virtual schedules
 * @author      Wolf Normann Gordian Rost wolf.rostATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
class thm_organizersViewvirtual_schedule_edit extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        
        $cids = JRequest::getVar('cid', null, 'ARRAY');
        $task = JRequest::getVar('task', null, 'STRING');
        $cid = base64_decode($cids[0], true);
        if($cid === false)
        	$cid = $cids[0];
        
        $title = JText::_('COM_THM_ORGANIZER').': ';
        $title .= ($cid)? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW');
        $title .= " ".JText::_('COM_THM_ORGANIZER_VIRTUAL_SCHEDULE');        
        JToolBarHelper::title( $title, 'mni' );
        JToolBarHelper::save('virtual_schedule_edit.save');
        JToolBarHelper::cancel('virtual_schedule_edit.cancel');

        $model = $this->getModel();
        $responsibles = $model->getResponsibles();

        if(!is_array($responsibles))
                $responsibles = array();

        $types = $model->getTypes();
        if(!is_array($types)) $types = array();
        $classes = $model->getClasses();
        if(!is_array($classes)) $classes = array();
        $rooms = $model->getRooms();
        if(!is_array($rooms)) $rooms = array();
        $teachers = $model->getTeachers();
        if(!is_array($teachers)) $teachers = array();
        $semesters = $model->getSemesters();
        if(!is_array($semesters)) $semesters = array();

        $roomDepartments = $model->getRoomDepartments();
        $teacherDepartments = $model->getTeacherDepartments();
        $classesDepartments = $model->getDepartments("classes");

        $tempDepartments = null;
        $tempDepartments[0]["id"] = "none";
        $tempDepartments[0]["name"] = JText::_( "COM_THM_ORGANIZER_VSE_VALUE_NONE" );
        
        if(is_array($roomDepartments))
            foreach($roomDepartments as $v) $tempDepartments[] = $v;

        $roomDepartments = $tempDepartments;

        $tempDepartments = null;
        $tempDepartments[0]["id"] = "none";
        $tempDepartments[0]["name"] = JText::_( "COM_THM_ORGANIZER_VSE_VALUE_NONE" );

        if(is_array($teacherDepartments))
            foreach($teacherDepartments as $v) $tempDepartments[] = $v;

        $teacherDepartments = $tempDepartments;

        $tempDepartments = null;
        $tempDepartments[0]["id"] = "none";
        $tempDepartments[0]["name"] = JText::_( "COM_THM_ORGANIZER_VSE_VALUE_NONE" );

        if(is_array($classesDepartments))
            foreach($classesDepartments as $v) $tempDepartments[] = $v;

        $classesDepartments = $tempDepartments;

        $session =& JFactory::getSession();
        $oldPost = $session->get('oldPost');
        $session->clear('oldPost');

        if($oldPost != null) $_POST = $oldPost;

        if($cid == null || $task == "add")
        {
            $vscheduler_name = JRequest::getVar('vscheduler_name', '', 'STRING');
            $vscheduler_types = JRequest::getVar('vscheduler_types', null, 'STRING');
            $vscheduler_semid = JRequest::getVar('vscheduler_semid', null, 'STRING');
            $vscheduler_resps = JRequest::getVar('vscheduler_resps', null, 'STRING');
            $vscheduler_classesDepartments = JRequest::getVar('vscheduler_classesDepartments', null, 'STRING');
            $vscheduler_teacherDepartments = JRequest::getVar('vscheduler_teacherDepartments', null, 'STRING');
            $vscheduler_roomDepartments = JRequest::getVar('vscheduler_roomDepartments', null, 'STRING');

            $vscheduler_classes = JRequest::getVar('vscheduler_classes', null, 'ARRAY');
            $vscheduler_rooms = JRequest::getVar('vscheduler_rooms', null, 'ARRAY');
            $vscheduler_teachers = JRequest::getVar('vscheduler_teachers', null, 'ARRAY');
        }
        else
        {
            $data = $model->getData($cid);
            foreach($data as $k=>$v)
            {
                if(!isset($newitem[$v->vid])) $newitem[$v->vid] = $v;
                else $newitem[$v->vid]->eid = $newitem[$v->vid]->eid.";".$v->eid;
            }
            $data = array_values($newitem);
            foreach($data as $k=>$v)
            {
                $v->eid = explode(';', $v->eid);
                $vscheduler_name = $v->vname;
                $vscheduler_types = $v->vtype;
                $vscheduler_semid = $v->sid;
                $vscheduler_resps = $v->vresponsible;
                $vscheduler_roomDepartments = null;
                $vscheduler_teacherDepartments = null;
                $vscheduler_classesDepartments = null;
                $vscheduler_classes = null;
                $vscheduler_rooms = null;
                $vscheduler_teachers = null;
                $vscheduler_roomTypes = null;
                $vscheduler_classTypes = null;
                if($vscheduler_types == "room")
                {
                    $vscheduler_roomDepartments = $v->department;
                    $vscheduler_rooms = $v->eid;
                }
                if($vscheduler_types == "teacher")
                {
                    $vscheduler_teacherDepartments = $v->department;
                    $vscheduler_teachers = $v->eid;
                }
                if($vscheduler_types == "class")
                {
                    $vscheduler_classesDepartments = $v->department;
                    $vscheduler_classes = $v->eid;
                }
            }
            $this->assignRef('cid', $cid);
        }

        $this->assignRef('name', $vscheduler_name);
        $this->assignRef('classes', JHTML::_('select.genericlist', $classes, 'vscheduler_classes[]','size="10" class="inputbox" multiple="multiple"', 'id', 'name', $vscheduler_classes));
        $this->assignRef('rooms', JHTML::_('select.genericlist', $rooms, 'vscheduler_rooms[]','size="10" class="inputbox" multiple="multiple"', 'id', 'name', $vscheduler_rooms));
        $this->assignRef('teachers', JHTML::_('select.genericlist', $teachers, 'vscheduler_teachers[]','size="10" class="inputbox" multiple="multiple"', 'id', 'name', $vscheduler_teachers));
        $this->assignRef('resps', JHTML::_('select.genericlist', $responsibles, 'vscheduler_resps','size="1" class="inputbox"', 'id', 'name', $vscheduler_resps));
        $this->assignRef('types', JHTML::_('select.genericlist', $types, 'vscheduler_types','size="1" class="inputbox" onChange="setRessource();"', 'id', 'name', $vscheduler_types));
        $this->assignRef('semesters', JHTML::_('select.genericlist', $semesters, 'vscheduler_semid','size="1" class="inputbox"', 'id', 'name', $vscheduler_semid));

        $this->assignRef('roomDepartments', JHTML::_('select.genericlist', $roomDepartments, 'vscheduler_roomDepartments','size="1" class="inputbox"', 'id', 'name', $vscheduler_roomDepartments));
        $this->assignRef('teacherDepartments', JHTML::_('select.genericlist', $teacherDepartments, 'vscheduler_teacherDepartments','size="1" class="inputbox"', 'id', 'name', $vscheduler_teacherDepartments));
        $this->assignRef('classesDepartments', JHTML::_('select.genericlist', $classesDepartments, 'vscheduler_classesDepartments','size="1" class="inputbox"', 'id', 'name', $vscheduler_classesDepartments));

        parent::display($tpl);
    }

}
