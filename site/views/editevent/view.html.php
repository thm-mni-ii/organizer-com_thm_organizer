<?php // no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');

/**
* HTML View class for the Giessen Scheduler Component
*
* @package    Giessen Scheduler
*/

class thm_organizerViewEditEvent extends JView
{
    function display($tpl = null)
    {
        JHTML::_('behavior.formvalidation');
        JHTML::_('behavior.tooltip');
        
        $document = & JFactory::getDocument();
        $document->addStyleSheet($this->baseurl."/components/com_thm_organizer/assets/css/thm_organizer.css");

        $model = $this->getModel();
        $event = $model->event;
        $this->assignRef('event', $event);
        $rooms = $model->rooms;
        $this->assignRef('rooms', $rooms);
        $teachers = $model->teachers;
        $this->assignRef('teachers', $teachers);
        $groups = $model->groups;
        $this->assignRef('groups', $groups);
        $categories = $model->categories;
        $this->assignRef('categories', $categories);

        $this->createHTMLElements();
        parent::display($tpl);
    }

    private function createHTMLElements()
    {
        $event = $this->event;

        $startcalendar = JHTML::_('calendar', $event['startdate'], 'startdate', 'startdate', '%d.%m.%Y',
                                  array('class' => 'inputbox required validate-date', 'size'=>'7',  'maxlength'=>'11'));
        $this->assignRef('startcalendar', $startcalendar);
        $endcalendar = JHTML::_('calendar', $event['enddate'], 'enddate', 'enddate', '%d.%m.%Y',
                                  array('class' => 'inputbox required validate-date', 'size'=>'7',  'maxlength'=>'11'));
        $this->assignRef('endcalendar', $endcalendar);

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
                                       'id="category" class="inputbox"', 'id', 'title',
                                       $this->event['categoryID']);
        $this->assignRef('categoryselect', $categoryselect);
    }
}