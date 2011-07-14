<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        create/edit appointment/event view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
class thm_organizerViewevent_edit extends JView
{
    function display($tpl = null)
    {
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        JHTML::_('behavior.mootools');

        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");
        $document->addScript(JRoute::_('components/com_thm_organizer/models/forms/event_edit.js'));

        $this->form = $this->get('Form');
        $item->item = $this->get('Item');

        $model = $this->getModel();
        $event = $model->event;
        $this->event = $event;
        $rooms = $model->rooms;
        $this->rooms = $rooms;
        $teachers = $model->teachers;
        $this->teachers = $teachers;
        $groups = $model->groups;
        $this->groups = $groups;
        $categories = $model->categories;
        $this->categories = $categories;

        $listLink = $model->listLink;
        $this->assignRef('listLink', $listLink);
        $eventLink = $model->eventLink;
        $this->assignRef('eventLink', $eventLink);

        if($event['recurrence_type'])
        {
            $blockchecked = '';
            $dailychecked = 'checked';
        }
        else
        {
            $blockchecked = 'checked';
            $dailychecked = '';
        }
        $this->assignRef('blockchecked', $blockchecked);
        $this->assignRef('dailychecked', $dailychecked);

        $isNew = ($event['id'] == 0);
        $document->setTitle($isNew ? JText::_('COM_THM_ORGANIZER_EE_TITLE_NEW') : JText::_('COM_THM_ORGANIZER_EE_TITLE_EDIT'));
        
        $this->createHTMLElements();
        parent::display($tpl);
    }

    private function createHTMLElements()
    {
        $event = $this->event;

        $otherrooms = array();
        $otherrooms[] = array('id' => '-1', 'name' => 'keine Räume');
        //$otherrooms[] = array( 'id' => '-2', 'name' => 'alle Räume' );
        $rooms = array_merge($otherrooms, $this->rooms);
        if(isset($this->event['rooms']))
        {
            $roomselect = JHTML::_('select.genericlist', $rooms, 'rooms[]',
                                   'id="rooms" class="inputbox" size="4" multiple="multiple"',
                                   'id', 'name', $this->event['rooms']);
        }
        else
        {
            $roomselect = JHTML::_('select.genericlist', $rooms, 'rooms[]',
                                   'id="rooms" class="inputbox" size="4" multiple="multiple"',
                                   'id', 'name');
        }
        $this->assignRef('roomselect', $roomselect);

        $otherteachers = array();
        $otherteachers[] = array('id' => '-1', 'name' => 'keine Dozenten');
        //$otherteachers[] = array( 'oid' => '-2', 'oname' => 'alle Dozenten' );
        $teachers = array_merge($otherteachers, $this->teachers);
        if(isset($this->event['teachers']))
        {
            $teacherselect = JHTML::_('select.genericlist', $teachers, 'teachers[]',
                                      'id="teachers" class="inputbox" size="4" multiple="multiple"',
                                      'id', 'name', $this->event['teachers']);
        }
        else
        {
            $teacherselect = JHTML::_('select.genericlist', $teachers, 'teachers[]',
                                      'id="teachers" class="inputbox" size="4" multiple="multiple"',
                                      'id', 'name');
        }
        $this->assignRef('teacherselect', $teacherselect);


        $othergroups = array();
        $othergroups[] = array( 'id' => '-1', 'name' => 'keine Gruppen' );
        //$othergroups[] = array( 'oid' => '-2', 'oname' => 'alle Gruppen' );
        $groups = array_merge($othergroups, $this->groups);
        if(isset($this->event['groups']))
        {
            $groupselect = JHTML::_('select.genericlist', $groups, 'groups[]',
                                    'id="groups" class="inputbox" size="4" multiple="multiple"',
                                    'id', 'name', $this->event['groups']);
        }
        else
        {
            $groupselect = JHTML::_('select.genericlist', $groups, 'groups[]',
                                    'id="groups" class="inputbox" size="4" multiple="multiple"',
                                    'id', 'name');
        }
        $this->assignRef('groupselect', $groupselect);

        $categoryselect = JHTML::_('select.genericlist', $this->categories, 'category',
                                       'id="category" class="inputbox" onChange="changeCategoryInformation()"', 'id', 'title',
                                       $this->event['categoryID']);
        $this->assignRef('categoryselect', $categoryselect);

        $saveLink = "<a href='#' onclick='Joomla.submitbutton('save')'>";
        $saveImage = JHTML::_('image', 'components/com_thm_organizer/assets/images/save.png', JText::_( 'Save' ),
                              array( 'class' => 'thm_organizer_ee_image_button',
                                     'onclick' =>"return submitbutton('saveevent');"));
        $saveLink .= $saveImage."</a>";
        $this->assignRef('savelink', $saveLink);

        $resetLink = "<a href='#' onclick='Joomla.submitbutton('reset')'>";
        $resetImage = JHTML::_('image', "components/com_thm_organizer/assets/images/reset.png",
                              JText::_( 'Reset' ), array( 'class' => 'thm_organizer_ee_image_button'));
        $resetLink .= $resetImage."</a>";
        $this->assignRef('resetlink', $resetLink);

        $cancelLink = "<a href='javascript:history.back()'>";
        $cancelImage = JHTML::_('image', 'components/com_thm_organizer/assets/images/cancel.png', JText::_( 'Cancel' ),
                                array( 'class' => 'thm_organizer_ee_image_button',
                                       'onclick' => "return submitbutton('cancelevent');"));
        $cancelLink .= $cancelImage."</a>";
        $this->assignRef('cancellink', $cancelLink);
    }
}