<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        booking raw view
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
/**
 * Outputs a string explaining possible conflicts which would emerge if an event were saved
 * 
 * @package  Joomla.Site
 * 
 * @since    1.5
 */
class thm_organizerViewbooking extends JView
{
    /**
     * Initiates model checks for conflicts and 'displays' them
     * 
     * @param   string  $tpl  the name of the template to be use on the output 
     * 
     * @return  void
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();
        $conflicts = $model->getConflicts();
        if (count($conflicts))
        {
            $count = 0;
            $total = count($conflicts);
            $message = JText::_('COM_THM_ORGANIZER_B_CONFLICTS_FOUND') . ":\n";
            foreach ($conflicts as $conflict)
            {
                if ($count == 4)
                {
                    $message .= "\n" . JText::sprintf('COM_THM_ORGANIZER_B_CONFLICTS_REMAINING', (string) $total - $count);
                    break;
                }
                $count++;
                $message .= "\n" . $conflict['text'] . "\n";
            }
            echo $message;
        }
    }
}
