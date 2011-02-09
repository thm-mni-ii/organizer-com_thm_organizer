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
        $model = $this->getModel();
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $sid = $model->sid;
        $this->assignRef( 'sid', $sid );

        $semester = $model->semester;
        $this->assignRef( 'semester', $semester );

        $orgunit = $model->orgunit;
        $this->assignRef( 'orgunit', $orgunit );

        $author = $model->author;
        $userGroups = $model->userGroups;
        $userGroupsBox = JHTML::_('select.genericlist', $userGroups, 'author', 'id="author" class="thm_organizer_" size="1"', 'id', 'title', $author);
        $this->assignRef('userGroupsBox', $userGroupsBox);

        $isNew = ($sid == 0)? true : false;
        $allowedActions = thm_organizerHelper::getActions('monitor_edit');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
            $this->addToolBar($allowedActions, $isNew);

        JToolBarHelper::save();
        if($isNew) JToolBarHelper::cancel();
        else JToolBarHelper::cancel( 'cancel', 'Close' );
        $this->assignRef('semester', $semester);

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
        if($isNew) JToolBarHelper::cancel('semester.cancel', 'JTOOLBAR_CANCEL');
        else JToolBarHelper::cancel( 'semester.cancel', 'JTOOLBAR_CANCEL');
    }
}
?>
	