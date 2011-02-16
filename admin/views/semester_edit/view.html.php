<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewsemester_edit extends JView
{
    function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');

        $model = $this->getModel();
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $id = $model->id;
        $this->assignRef( 'id', $id );

        $semesterDesc = $model->semesterDesc;
        $this->assignRef( 'semesterDesc', $semesterDesc );

        $organization = $model->organization;
        $this->assignRef( 'organization', $organization );

        $manager = $model->manager;
        $userGroups = $model->userGroups;
        $userGroupsBox = JHTML::_('select.genericlist', $userGroups, 'manager', 'id="manager" class="thm_organizer_" size="1"', 'id', 'title', $manager);
        $this->assignRef('userGroupsBox', $userGroupsBox);

        $isNew = ($id == 0)? true : false;
        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
            $this->addToolBar($allowedActions, $isNew);

        if($isNew)
        {
            $scheduleText = JText::_("Once the schedule has been created by saving it, schedules and display content can be added here.");
            $this->assignRef( 'scheduleText', $scheduleText );
        }
        else
        {
            $schedules = $model->schedules;
            if(!empty($schedules))
            {
                $links = $this->buildLinks(&$schedules);
                $this->assignRef( 'schedules', $schedules );
            }
            else
            {
                $noSchedulesText = JText::_("There are currently no saved schedules.");
                $this->assignRef('noSchedulesText', $noSchedulesText);
            }
        }

        parent::display($tpl);
    }

    private function addToolBar($allowedActions, $isNew = true)
    {
        $canSave = false;
        if($isNew)
        {
            $titleText = JText::_( 'Semester Manager: Add a New Semester' );
            if($allowedActions->get("core.create") or $allowedActions->get("core.edit"))
                    $canSave = true;
        }
        else
        {
            $titleText = JText::_( 'Semester Manager: Edit an Existing Semester' );
            if($allowedActions->get("core.edit")) $canSave = true;
        }
        JToolBarHelper::title( $titleText, 'generic.png' );
        if($canSave) JToolBarHelper::save('semester.save', 'JTOOLBAR_SAVE');
        if($allowedActions->get("core.create"))
            JToolBarHelper::custom('semester.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        
//            JToolBarHelper::custom ($task, $icon, $iconOver, $alt, $listSelect)
        if($isNew and $allowedActions->get("core.edit"))
            JToolBarHelper::custom('semester.save2schedules', 'edit.png', '', JText::_("Save & Manage Schedules"), false);
        if($isNew) JToolBarHelper::cancel('semester.cancel', 'JTOOLBAR_CANCEL');
        else JToolBarHelper::cancel( 'semester.cancel', 'JTOOLBAR_CANCEL');
    }

    private function buildLinks(&$schedules)
    {
        $links = array();
        if(!empty($schedules))
            foreach($schedules as $k => $v)
            {
                $attribs = "class='thm_organizer_se_image_button'";

                $image = JHTML::_('image.site', 'publish.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
                $tiptext = JText::_( 'Activate this schedule.' );
                $tiptitle = JText::_( 'Activate' );
                $link = 'index.php?option=com_thm_organizer&task=schedule.activate&scheduleID='.$schedule['id'];
                $class = "thm_organizer_se_activate_button";
                $schedules[$k]['activatelink'] = "<a href='".JRoute::_($link)."' class='$class hasTip' title='$tiptitle::$tiptext'>$image</a>";

                $image = JHTML::_('image.site', 'unpublish.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
                $tiptext = JText::_( 'Deactivate this schedule.' );
                $tiptitle = JText::_( 'Deactivate' );
                $link = 'index.php?option=com_thm_organizer&task=schedule.deactivate&scheduleID='.$schedule['id'];
                $class = "thm_organizer_se_deactivate_button";
                $schedules[$k]['deactivatelink'] = "<a href='".JRoute::_($link)."' class='$class hasTip' title='$tiptitle::$tiptext'>$image</a>";

                $image = JHTML::_('image.site', 'delete.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
                $tiptext = JText::_( 'Delete this schedule.' );
                $tiptitle = JText::_( 'Delete' );
                $link = 'index.php?option=com_thm_organizer&task=schedule.delete_schedule&scheduleID='.$schedule['id'];
                $class = "thm_organizer_se_delete_button";
                $schedules[$k]['deletelink'] = "<a href='".JRoute::_($link)."' class='$class hasTip' title='$tiptitle::$tiptext'>$image</a>";

                $image = JHTML::_('image.site', 'edit.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, NULL, $attribs);
                $tiptext = JText::_( 'Add/Edit the description of this schedule.' );
                $tiptitle = JText::_( 'Description' );
                $class = "thm_organizer_se_update_button";
                $schedules[$k]['updatelink'] = "<input type='image' class='$class hasTip' title='$tiptitle::$tiptext'src='$image' name='submit' value='submit' />";

           }
    }
}?>
	