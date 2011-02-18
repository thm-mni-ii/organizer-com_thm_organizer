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
                $this->assignRef( 'schedules', $schedules );
                $schedsExist = true;
            }
            else
            {
                $noSchedulesText = JText::_("There are currently no saved schedules.");
                $this->assignRef('noSchedulesText', $noSchedulesText);
            }
        }

        $allowedActions = thm_organizerHelper::getActions('semester_edit');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
            $this->addToolBar($allowedActions, $isNew, $schedsExist);

        parent::display($tpl);
    }

    private function addToolBar($allowedActions, $isNew = true, $schedsExist = false)
    {
        /*
         *
            JToolBarHelper::custom('articles.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
		}

		if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'articles.delete','JTOOLBAR_EMPTY_TRASH');
			JToolBarHelper::divider();
			JToolBarHelper::trash('articles.trash','JTOOLBAR_TRASH');
         */
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
        if($canSave)
        {
            if($schedsExist)
            {
                JToolBarHelper::custom('semester.activate', 'publish.png', 'publish_f2.png','Activate', true);
                JToolBarHelper::custom('semester.deactivate', 'unpublish.png', 'unpublish_f2.png', 'Deactivate', true);
		JToolBarHelper::trash('semester.schedule_delete','Delete');
                JToolBarHelper::custom('semester.comment', 'edit.png', 'edit_f2.png', 'Edit', true);
                JToolBarHelper::divider();
            }
            JToolBarHelper::apply('semester.apply', 'Apply');
            JToolBarHelper::save('semester.save', 'Save');
        }
        
//            JToolBarHelper::custom ($task, $icon, $iconOver, $alt, $listSelect)
        if($isNew and $allowedActions->get("core.edit"))
            JToolBarHelper::custom('semester.save2schedules', 'edit.png', '', JText::_("Save & Manage Schedules"), false);
        JToolBarHelper::cancel( 'semester.cancel', 'Close');
    }
}?>
	