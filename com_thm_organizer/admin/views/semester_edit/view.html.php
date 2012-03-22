<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor view
 * @description provides a form for editing semester information
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewsemester_edit extends JView
{
    public function display($tpl = null)
    {
        if(!JFactory::getUser()->authorise('core.admin'))
            return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
        
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->assignRef( 'title', $title );
        $this->assignRef( 'semesterID', $model->semesterID );
        $this->assignRef( 'semesterDesc', $model->semesterDesc );
        $this->assignRef( 'organization', $model->organization );

        $title = ($model->semesterID)?
            JText::_('COM_THM_ORGANIZER_SEM_EDIT_TITLE') : JText::_('COM_THM_ORGANIZER_SEM_EDIT_TITLE_NEW');
        JToolBarHelper::title($title);
        $this->addToolBar();

        parent::display($tpl);
    }
    
    private function addToolBar()
    {
        JToolBarHelper::apply('semester.apply', JText::_('COM_THM_ORGANIZER_APPLY'));
        JToolBarHelper::save('semester.save', JText::_('COM_THM_ORGANIZER_SAVE'));
        JToolBarHelper::save2new('semester.save2new', JText::_('COM_THM_ORGANIZER_SAVE2NEW'));
        JToolBarHelper::cancel('semester.cancel', JText::_('COM_THM_ORGANIZER_CLOSE'));
    }
}?>
	