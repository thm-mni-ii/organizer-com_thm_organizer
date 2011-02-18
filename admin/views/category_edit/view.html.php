<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewcategory_edit extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $categoryID = $model->categoryID;
        $this->assignRef( 'categoryID', $categoryID );

        $ip = $model->ip;
        $this->assignRef( 'ip', $ip );

        $room = $model->room;
        $rooms = $model->rooms;
        if(!empty($rooms))
        {
            $roombox = JHTML::_('select.genericlist', $rooms, 'room', 'id="thm_organizer_me_roombox" class="thm_organizer_me_rsemesterbox" size="1"', 'id', 'name', $room);
            $this->assignRef('roombox', $roombox);
        }

        $sid = $model->sid;
        $semesters = $model->semesters;
        if(!empty($semesters))
        {
            $semesterbox =  JHTML::_('select.genericlist', $semesters, 'semester', 'id="thm_organizer_me_rsemesterbox" class="thm_organizer_me_rsemesterbox" size="1"', 'sid', 'name', $sid);
            $this->assignRef('semesterbox', $semesterbox);
        }

        $isNew = ($categoryID == 0)? true : false;
        $allowedActions = thm_organizerHelper::getActions('category_edit');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
            $this->addToolBar($allowedActions, $isNew);

        parent::display($tpl);
    }

    private function addToolBar($allowedActions, $isNew = true)
    {
        $canSave = false;
        if($isNew)
        {
            $titleText = JText::_( 'category Manager: Add a New category' );
            if($allowedActions->get("core.create") or $allowedActions->get("core.edit"))
                    $canSave = true;
        }
        else
        {
            $titleText = JText::_( 'category Manager: Edit an Existing category' );
            if($allowedActions->get("core.edit")) $canSave = true;
        }
        JToolBarHelper::title( $titleText, 'generic.png' );
        if($canSave) JToolBarHelper::save('category.save', 'JTOOLBAR_SAVE');
        if($allowedActions->get("core.create"))
            JToolBarHelper::custom('category.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        if($isNew) JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CANCEL');
        else JToolBarHelper::cancel( 'category.cancel', 'JTOOLBAR_CANCEL');
    }
}
	