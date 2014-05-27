<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        create/edit appointment/event view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Dominik Bassing, <dominik.bassing@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.view');
jimport('jquery.jquery');
require_once JPATH_SITE . '/components/com_thm_organizer/helper/event.php';

/**
 * Decides if its an save or preview task, outputs a string explaining possible conflicts,
 * which would merge if an event were saved or returns JSONString for preview PopUp
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class Thm_OrganizerViewEvent_Ajax extends JViewLegacy
{
    /**
     * loads model data into view context
     *
     * @param   string  $tpl  the name of the template to be used
     *
     * @return void
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function display($tpl = null)
    {
        $function = JRequest::getString('task');
        switch ($function)
        {
            case 'booking':
                $this->booking();
                break;
            case 'preview':
                $this->preview();
                break;
        }
    }

    /**
     * Generates a list of conflicts with event resources
     *
     * @return  void
     */
    private function booking()
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

    /**
     * Generates a preview of an event
     *
     * @return  void
     */
    private function preview()
    {
        $data = array();
        $data['title'] = JRequest::getVar('title', null, null, null, 4);
        $data['id'] = JRequest::getVar('id', null, null, null, 4);
        $data['startdate'] = JRequest::getVar('startdate', null, null, null, 4);
        $data['enddate'] = JRequest::getVar('enddate', null, null, null, 4);
        $data['starttime'] = JRequest::getVar('starttime', null, null, null, 4);
        $data['endtime'] = JRequest::getVar('endtime', null, null, null, 4);
        $data['categoryID'] = JRequest::getInt('category');
        $data['description'] = JRequest::getVar('description', null, null, null, 4);
        THM_OrganizerHelperEvent::buildtext($data);
        $user = JFactory::getUser();
        $username = $user->name;
        $written_by = "<p>" . JText::_('COM_THM_ORGANIZER_E_WRITTEN_BY') . $username . "</p>";
        $data['username'] = $written_by;
        $published_at = "<p>" . JText::_('COM_THM_ORGANIZER_PREVIEW_CREATED') . JFactory::getDate()->toFormat('%A %d. %B %Y %H:%M:%S') . "</p>";
        $data['created_at'] = $published_at;
        $jsonstring = json_encode($data);
        echo $jsonstring;
    }
}
