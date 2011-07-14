<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        reservation ajax response view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
class thm_organizerViewbooking extends JView
{
    function display($tpl = null)
    {
        $model = $this->getModel();
        $conflicts = $model->conflicts;
        if(count($conflicts)) $this->conflictList($conflicts);
        else $this->free();
    }

    private function free()
    {
        echo JText::_('COM_THM_ORGANIZER_NO_COLLISIONS');
    }

    private function conflictList($conflicts)
    {
        $message = "";
        echo $message;
    }
}
?>