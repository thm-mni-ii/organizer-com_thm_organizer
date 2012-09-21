<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        reservation ajax response view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     0.0.1
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
class thm_organizerViewbooking extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $model->prepareData();
        $conflicts = $model->conflicts;
        if(count($conflicts)) $this->conflictList($conflicts);
    }

    private function conflictList($conflicts)
    {
        $message = JText::_('COM_THM_ORGANIZER_B_CONFLICTS_FOUND').":\r\n";
        foreach($conflicts as $conflict)
        {
            $message .= "\r\n".$conflict['details'];
            $message .= " ".$conflict['resourcesText']."\r\n";
        }
        echo $message;
    }
}
?>