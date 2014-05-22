<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewEvent
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Retrieves event data and loads it into the view context
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewEvent_Details extends JViewLegacy
{
    /**
     * Loads event information into the view context
     *
     * @param   string  $tpl  the name of the template to use
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        JHtml::_('behavior.tooltip');
        $document = JFactory::getDocument();
        $document->addStyleSheet($this->baseurl . "/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $this->event = $model->event;
        $this->itemID = JRequest::getVar('Itemid');
        $this->listLink = $model->listLink;
        $this->canWrite = $model->canWrite;

        $item = new stdClass;
        $dispatcher = JDispatcher::getInstance();
        $item->text = $this->event['description'];
        JPluginHelper::importPlugin('content');
        $dispatcher->trigger('onContentPrepare', array ('com_content.article', &$item, &$this->params));
        $this->event['description'] = $item->text;
        unset($item);

        $this->createTextElements();

        parent::display($tpl);
    }

    /**
     * Creates the text elements used for event output
     *
     * @return  void
     */
    private function createTextElements()
    {
        // Creation of the sentence display of the dates & times
        $dateTimeText = JText::_("COM_THM_ORGANIZER_E_DATES_START");
        $timeText = "";
        if (isset($this->event['starttime']) AND isset($this->event['endtime']))
        {
            $timeText = JText::_("COM_THM_ORGANIZER_E_BETWEEN");
            $timeText .= $this->event['starttime'] . JText::_("COM_THM_ORGANIZER_E_AND") . $this->event['endtime'];
        }
        elseif (isset($this->event['starttime']))
        {
            $timeText = JText::_("COM_THM_ORGANIZER_E_FROM") . $this->event['starttime'];
        }
        elseif (isset($this->event['endtime']))
        {
            $timeText = JText::_("COM_THM_ORGANIZER_E_TO") . $this->event['endtime'];
        }
        else
        {
            $timeText = JText::_("COM_THM_ORGANIZER_E_ALLDAY");
        }

        if (isset($this->event['startdate']) and isset($this->event['enddate']) and $this->event['startdate'] != $this->event['enddate'])
        {
            if ($this->event['rec_type'] == 0)
            {
                if (isset($this->event['starttime']) AND isset($this->event['endtime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_BETWEEN") . $this->event['starttime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON") . $this->event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_AND") . $this->event['endtime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON") . $this->event['enddate'];
                }
                elseif (isset($this->event['starttime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM") . $this->event['starttime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON") . $this->event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_TO") . $this->event['enddate'];
                }
                elseif (isset($this->event['endtime']))
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM") . $this->event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_TO") . $this->event['endtime'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON") . $this->event['enddate'];
                }
                else
                {
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM") . $this->event['startdate'];
                    $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_UNTIL") . $this->event['enddate'];
                    $dateTimeText .= $timeText;
                }
            }
            else
            {
                $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_FROM") . $this->event['startdate'];
                $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_UNTIL") . $this->event['enddate'];
                $dateTimeText .= $timeText;
            }
        }
        else
        {
            $dateTimeText .= JText::_("COM_THM_ORGANIZER_E_ON") . $this->event['startdate'] . $timeText;
        }
        $this->dateTimeText = $dateTimeText . JText::_("COM_THM_ORGANIZER_E_DATES_END");

        $published = JText::_("COM_THM_ORGANIZER_E_PUBLISHED_START") . $this->event['publish_up'];
        $published .= JText::_("COM_THM_ORGANIZER_E_UNTIL") . $this->event['publish_down'];
        $published .= JText::_("COM_THM_ORGANIZER_E_PUBLISHED_END");
        $this->assignRef('published', $published);

        $teachers = $rooms = $groups = false;
        if (count($this->event['teachers']) > 0)
        {
            if (count($this->event['teachers']) > 1)
            {
                $teachersLabel = JText::_("COM_THM_ORGANIZER_E_TEACHERS");
            }
            else
            {
                $teachersLabel = JText::_("COM_THM_ORGANIZER_E_TEACHER");
            }
            $this->assignRef('teachersLabel', $teachersLabel);
            $teachers = implode(', ', $this->event['teachers']);
            $this->assignRef('teachers', $teachers);
        }
        else
        {
            $this->assignRef('teachers', $teachers);
        }
        if (count($this->event['rooms']) > 0)
        {
            if (count($this->event['rooms']) > 1)
            {
                $roomsLabel = JText::_("COM_THM_ORGANIZER_E_ROOMS");
            }
            else
            {
                $roomsLabel = JText::_("COM_THM_ORGANIZER_E_ROOM");
            }
            $this->assignRef('roomsLabel', $roomsLabel);
            $rooms = implode(', ', $this->event['rooms']);
            $this->assignRef('rooms', $rooms);
        }
        else
        {
            $this->assignRef('rooms', $rooms);
        }
        if (count($this->event['groups']) > 0)
        {
            if (count($this->event['groups']) > 1)
            {
                $groupsLabel = JText::_("COM_THM_ORGANIZER_E_AFFECTED");
            }
            else
            {
                $groupsLabel = JText::_("COM_THM_ORGANIZER_E_AFFECTED");
            }
            $this->assignRef('groupsLabel', $groupsLabel);
            $groups = implode(', ', $this->event['groups']);
            $this->assignRef('groups', $groups);
        }
        else
        {
            $this->assignRef('groups', $groups);
        }
    }
}
