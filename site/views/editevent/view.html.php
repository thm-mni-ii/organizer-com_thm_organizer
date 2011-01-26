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

        $model =& $this->getModel();

        $user =& JFactory::getUser();
        $this->assignRef( 'userid', $user->id );
        $this->assignRef( 'usergid', $user->gid );

        $itemid = JRequest::getVar('Itemid');
        $this->assignRef( 'itemid', $itemid);

        $event = $model->event;
        if(isset($event)) $this->assignRef('event', $event);

        $this->assignRef( 'lists', $this->buildLists());

        parent::display($tpl);
    }

    /*
     * Creates HTML Elements for the edit event form
     */
    function buildLists()
    {
        if(!isset($this->event['sectionid'])) $this->event['sectionid'] = $this->event['sections'][0]['id'];
        $javascript = 'onchange="changeCCatList( \'ccatid\', sectioncategories, document.eventForm.sectionid.options[document.eventForm.sectionid.selectedIndex].value);"';
        $lists['sectionid'] = JHTML::_('select.genericlist',  $this->event['sections'], 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', $this->event['sectionid']);
        
        $sectioncategories = array ();
        // Uncategorized category mapped to uncategorized section
        foreach ($this->event['sections'] as $section)
        {
            $sectioncategories[$section['id']] = array ();
            $rows2 = array ();
            foreach ($this->event['ccategories'] as $ccat)
                if($ccat['section'] == $section['id']) $rows2[] = $ccat;
            foreach ($rows2 as $row2)
                $sectioncategories[$section['id']][] = JHTML::_('select.option', $row2['id'], $row2['title'], 'id', 'title');
        }

        $lists['sectioncategories'] = $sectioncategories;
        if(isset($this->event['ccatid']))
            $lists['ccats'] = JHTML::_('select.genericlist',  $sectioncategories[$this->event['sectionid']], 'ccatid', 'class="inputbox" size="1"', 'id', 'title', $this->event['ccatid']);
        else
            $lists['ccats'] = JHTML::_('select.genericlist',  $sectioncategories[$this->event['sectionid']], 'ccatid', 'class="inputbox" size="1"', 'id', 'title');

        if(isset($this->event['ecatid']))
            $lists['ecats'] = JHTML::_('select.genericlist', $this->event['ecategories'], 'ecatid','size="1" class="inputbox"', 'ecid', 'ecname', $this->event['ecatid'] );
        else
            $lists['ecats'] = JHTML::_('select.genericlist', $this->event['ecategories'], 'ecatid','size="1" class="inputbox"', 'ecid', 'ecname' );

        $othersemesters = array(1=>array('oid' => '-1', 'oname' => 'keine Semestergänge')/*, 2 => array( 'oid' => '-2', 'oname' => 'alle Semestergänge' )*/);
        $semesters = array_merge($othersemesters, $this->event['semesters']);
        if(isset($semesters) && isset($this->event['savedObjects']))
            $lists['semesters'] = JHTML::_('select.genericlist', $semesters, 'semesters[]', 'id="fachsemesters" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname', $this->event['savedObjects']);
        else if(isset($semesters))
            $lists['semesters'] = JHTML::_('select.genericlist', $semesters, 'semesters[]', 'id="fachsemesters" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname');
        else $lists['semesters'] = null;

        $otherrooms = array(1=>array('oid' => '-1', 'oname' => 'keine Räume')/*, 2 => array( 'oid' => '-2', 'oname' => 'alle Räume' )*/);
        $rooms = array_merge($otherrooms, $this->event['rooms']);
        if(isset($rooms) && isset($this->event['savedObjects']))
            $lists['rooms'] = JHTML::_('select.genericlist', $rooms, 'rooms[]', 'id="rooms" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname', $this->event['savedObjects']);
        else if(isset($rooms))
            $lists['rooms'] = JHTML::_('select.genericlist', $rooms, 'rooms[]', 'id="rooms" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname');
        else $lists['rooms'] = null;

        $otherteachers = array(1=>array('oid' => '-1', 'oname' => 'keine Dozenten')/*, 2 => array( 'oid' => '-2', 'oname' => 'alle Dozenten' )*/);
        $teachers = array_merge($otherteachers, $this->event['teachers']);
        if(isset($teachers) && isset($this->event['savedObjects']))
            $lists['teachers'] = JHTML::_('select.genericlist', $teachers, 'teachers[]', 'id="teachers" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname', $this->event['savedObjects']);
        else if(isset($teachers))
            $lists['teachers'] = JHTML::_('select.genericlist', $teachers, 'teachers[]', 'id="teachers" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname');
        else $lists['teachers'] = null;

        $othergroups = array(1 => array('oid' => '-1', 'oname' => 'keine Gruppen' )/*, 2 => array( 'oid' => '-2', 'oname' => 'alle Gruppen' )*/);
        $groups = array_merge($othergroups, $this->event['groups']);
        if(isset($groups) && isset($this->event['savedObjects']))
            $lists['groups'] = JHTML::_('select.genericlist', $groups, 'groups[]', 'id="groups" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname', $this->event['savedObjects']);
        else if(isset($groups))
            $lists['groups'] = JHTML::_('select.genericlist', $groups, 'groups[]', 'id="groups" class="inputbox" size="4" multiple="multiple"', 'oid', 'oname');
        else $lists['groups'] = null;

        return $lists;
    }
}