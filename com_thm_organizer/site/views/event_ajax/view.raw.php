<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        create/edit appointment/event view
 * @author      Dominik Bassing, <dominik.bassing@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');


class Thm_OrganizerViewEvent_Ajax extends JView
{
    /**
     * loads model data into view context
     * 
     * @param   string  $tpl  the name of the template to be used
     * 
     * @return void
     */
    public function display($tpl = null)
    {   
        $function = JRequest::getString('task');
        if($function == "preview"){
            if (!JFactory::getUser()->authorise('core.admin'))
            {
                return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
            }
            $this->$function();
        }
        else{
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
    
    public function preview()
    {
        $model = JModel::getInstance('events', 'thm_organizerModel');
        $data = $model->cleanRequestData();
        THM_OrganizerEvent_Helper::buildtext($data);  
        $user = JFactory::getUser($data['userID']);
        $username = $user->name;        
        $written_by = "<p>" . JText::_('COM_THM_ORGANIZER_E_WRITTEN_BY') . $username . "</p>";
        $data['username'] = $written_by;
        $published_at = "<p>" . JText::_('COM_THM_ORGANIZER_PREVIEW_CREATED') .  JFactory::getDate()->toFormat('%A %d. %B %G %H:%M') . "</p>";
        $data['created_at'] = $published_at;
        $jsonstring = json_encode($data);
        echo $jsonstring;
    }
}
