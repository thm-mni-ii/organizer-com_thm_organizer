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

class thm_organizersViewmonitor_edit extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $monitorID = $model->monitorID;
        $this->assignRef( 'monitorID', $monitorID );

        $ip = $model->ip;
        $this->assignRef( 'ip', $ip );

        $rooms = $model->rooms;
        if(!empty($rooms))
            $this->makeSelectBox($rooms, "room", $model->roomID);

        $isNew = ($monitorID == 0)? true : false;
        $allowedActions = thm_organizerHelper::getActions('monitor_edit');
        if($allowedActions->get("core.admin") or $allowedActions->get("core.manage"))
            $this->addToolBar($allowedActions, $isNew);

        parent::display($tpl);
    }

    private function makeSelectBox($dataobjects, $name, $selected = null)
    {
        $selectBox =  JHTML::_("select.genericlist", $dataobjects, "$name", 'class="thm_organizer_me_selectbox" size="1"', 'id', 'name', $selected);
        $this->assignRef($name, $selectBox);
    }

    private function addToolBar($allowedActions, $isNew = true)
    {
        $canSave = false;
        if($isNew)
        {
            $titleText = JText::_( 'Monitor Manager: Add a New Monitor' );
            if($allowedActions->get("core.create") or $allowedActions->get("core.edit"))
                    $canSave = true;
        }
        else
        {
            $titleText = JText::_( 'Monitor Manager: Edit an Existing Monitor' );
            if($allowedActions->get("core.edit")) $canSave = true;
        }
        JToolBarHelper::title( $titleText, 'generic.png' );
        if($canSave) JToolBarHelper::save('monitor.save', 'JTOOLBAR_SAVE');
        if($allowedActions->get("core.create"))
            JToolBarHelper::custom('monitor.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
        if($isNew) JToolBarHelper::cancel('monitor.cancel', 'JTOOLBAR_CANCEL');
        else JToolBarHelper::cancel( 'monitor.cancel', 'JTOOLBAR_CANCEL');
    }
}
?>
	