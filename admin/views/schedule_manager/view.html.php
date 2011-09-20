<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        schedule manager view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );
require_once JPATH_COMPONENT.'/assets/helpers/thm_organizerHelper.php';

class thm_organizersViewschedule_manager extends JView
{
    function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');
        JHtml::_('behavior.modal', 'a.modal');
        
        $model = $this->getModel();
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $this->assignRef( 'semesterID', $model->semesterID );
        $this->assignRef( 'semesterName', $model->semesterName );
        $this->assignRef( 'schedules', $model->schedules );

         parent::display($tpl);
    }
}?>

