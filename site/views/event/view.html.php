<?php
/**
 * 
 * view.html.php
 * view = viewnote
 * 
 */
 
// no direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for the Giessen Scheduler Component
 *
 * @package    Giessen Scheduler
 */
 
class thm_organizerViewevent extends JView
{
    public function display($tpl = null)
    {
        JHTML::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

    	$model = $this->getModel();
	$event = $model->event;
        $this->assignRef('event', $event);
        $itemID = JRequest::getVar('Itemid');
        $this->assignRef( 'itemID', $itemID );
        $listLink = $model->listLink;
        $this->assignRef('listLink', $listLink);

        $this->createTextElements(&$event);

        parent::display($tpl);
    }

    private function createTextElements(&$event)
    {

        //creation of the sentence display of the dates & times
        $dateTimeText = JText::_("COM_THM_ORGANIZER_E_DATES_START");
        $timeText = "";
        if(isset($event['starttime']) && isset($event['endtime']))
        {
            $timeText = JText::_("COM_THM_ORGANIZER_E_BETWEEN");
            $timeText .= $event['starttime'].JText::_("COM_THM_ORGANIZER_E_AND").$event['endtime'];
        }
        else if(isset($event['starttime']))
            $timeText = JText::_("COM_THM_ORGANIZER_E_FROM").$event['starttime'];
        else if(isset($event['endtime']))
            $timeText = JText::_("COM_THM_ORGANIZER_E_TO").$event['endtime'];
        else
            $timeText = JText::_ ("COM_THM_ORGANIZER_E_ALLDAY");


        if(isset($event['startdate']) && isset($event['enddate']))
        {
            if($event['rec_type'] == 0)
            {
                if(isset($event['starttime']) && isset($event['endtime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_BETWEEN").$event['starttime'];
                    $dateTimeText.= JText::_("COM_THM_ORGANIZER_E_ON").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_AND").$event['endtime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['enddate'];
                }
                else if(isset($event['starttime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['starttime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_TO").$event['enddate'];
                }
                else if(isset($event['endtime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_TO").$event['endtime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['enddate'];
                }
            }
            else
            {
                $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM").$event['startdate'];
                $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_UNTIL").$event['enddate'];
                $dateTimeText .= $timeText;
            }
        }
        else
        {
            $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON").$event['startdate'].$timeText;
        }
        $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_DATES_END");
        $this->assignRef('dateTimeText', $dateTimeText);

        $published = JText::_("COM_THM_ORGANIZER_E_PUBLISHED_START").$event['publish_up'];
        $published .= JText::_("COM_THM_ORGANIZER_E_UNTIL").$event['publish_down'];
        $published .= JText::_("COM_THM_ORGANIZER_E_PUBLISHED_END");
        $this->assignRef('published', $published);

        $teachers = $rooms = $groups = false;
        if(count($event['teachers']) > 0)
        {
            if(count($event['teachers']) > 1) $teachersLabel = JText::_("COM_THM_ORGANIZER_E_TEACHERS");
            else $teachersLabel = JText::_("COM_THM_ORGANIZER_E_TEACHER");
            $this->assignRef('teachersLabel', $teachersLabel);
            $teachers = implode(', ', $event['teachers']);
            $this->assignRef('teachers', $teachers);
        }
        else $this->assignRef('teachers', $teachers);
        if(count($event['rooms']) > 0)
        {
            if(count($event['rooms']) > 1) $roomsLabel = JText::_("COM_THM_ORGANIZER_E_ROOMS");
            else $roomsLabel = JText::_("COM_THM_ORGANIZER_E_ROOM");
            $this->assignRef('roomsLabel', $roomsLabel);
            $rooms = implode(', ', $event['rooms']);
            $this->assignRef('rooms', $rooms);
        }
        else $this->assignRef('rooms', $rooms);
        if(count($event['groups']) > 0)
        {
            if(count($event['groups']) > 1) $groupsLabel = JText::_("COM_THM_ORGANIZER_E_GROUPS");
            else $groupsLabel = JText::_("COM_THM_ORGANIZER_E_GROUP");
            $this->assignRef('groupsLabel', $groupsLabel);
            $groups = implode(', ', $event['groups']);
            $this->assignRef('groups', $groups);
        }
        else $this->assignRef('groups', $groups);


        $contentorg = "";
        if(isset($event['sectname']) && isset($event['ccatname']))
                $contentorg = $event['sectname']." / ".$event['ccatname'];
        else if(isset($event['sectname']))
                $contentorg = $event['sectname'];
        if(isset($event['publish_up']) && isset($event['publish_down']))
            $published = "Der Beitrag wird vom ".$event['publish_up']." bis ".$event['publish_down']." angezeigt";

    }
}