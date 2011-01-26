<?php

/**
* Notelist View Class for the Giessen Times Component
*
* @package    Giessen Scheduler
*/


// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
* HTML View class for the Giessen Scheduler Component
*
* @package    Giessen Scheduler
*/

class thm_organizerViewEventList extends JView
{
    function display($tpl = null)
    {
        //var_dump($this);
        global $mainframe;
        $model =& $this->getModel();
        $user =& JFactory::getUser();
        $username = $user->username;
        $this->assign('username' , $username);
        $usergid = $user->gid;
        $this->assign('usergid' , $usergid);


        //get data from model
        $events =  $model->getEvents();
        $categories =  $model->getCategories();
        $this->assign('categories', $categories);
        $total 	=  $model->getTotal();
        if(isset($events))
            foreach ($events as $event)
            {
                if(isset($event->author) == $username || $usergid >= 24) $event->access = true;
                else $event->access = false ;
            }
        // Create the pagination object
        $pageNav = & $this->get('Pagination');
        $this->assign('itemid' , JRequest::getInt('Itemid'));
        $this->assign('total', $total);
        $this->assign('events', $events);
        $this->assign('pageNav', $pageNav);
        $date = $model->getState('date');
        if(isset($date))
                $this->assign('date', $date);
        $filter = $model->getState('filter');
        if(isset($filter))
                $this->assign('filter', strtolower($filter));
        $category = $model->getState('category');
        if(isset($category))
                $this->assign('category', $category);
        $orderby = $model->getState('orderby');
        if(isset($orderby))
                $this->assign('orderby', $orderby);
        $orderbydir = $model->getState('orderbydir');
        if(isset($orderbydir))
                $this->assign('orderbydir', $orderbydir);

        //create select lists
        $lists	= $this->buildLists();
        $this->assign('lists' , $lists);

        $this->assign('newlink', JRoute::_( "index.php?option=com_thm_organizer&view=editevent&eventid=0&Itemid=$this->itemid" ));

        parent::display($tpl);
    }

    /**
    * Method to build the sortlists
    *
    * @access private
    * @return array
    * @since 0.9
    */
    function buildLists()
    {
        JHTML::_('behavior.tooltip');
        $lists['newimage'] = JHTML::_('image.site', 'add.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Termin Erzeugen' ));
        $lists['newtext'] = JText::_( 'Neuer Termin erstellen' );
        $lists['newtitle'] = JText::_( 'Neues Event' );
        $lists['editimage'] = JHTML::_('image.site', 'edit.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Termin Editieren' ));
        $lists['edittext'] = JText::_( 'Dieser Termin editieren' );
        $lists['edittitle'] = JText::_( 'Event Editieren' );
        $lists['deleteimage']= JHTML::_('image.site', 'delete.png', 'components/com_thm_organizer/assets/images/', NULL, NULL, JText::_( 'Termin L&ouml;schen' ));
        $lists['deletetext']= JText::_( 'Dieser Termin l&ouml;schen' );
        $lists['deletetitle'] = JText::_( 'Event L&ouml;schen' );

        //the column is sorted with ascending values so the displayed image is an up arrow, but the link will sort them to have descending values
        $downsortimage= JHTML::_('image.site', 'uparrow.png', 'administrator/images/', NULL, NULL, JText::_( 'Absteigend Sortieren' ));
        $downsorttext= JText::_( 'Nach dieser Spalte absteigend sortieren.' );
        //the column is sorted with decending values so the displayed image is a down arrow, but the link will sort them to have ascending values
        $upsortimage= JHTML::_('image.site', 'downarrow.png', 'administrator/images/', NULL, NULL, JText::_( 'Aufsteigend Sortieren' ));
        $upsorttext= JText::_( 'Nach dieser Spalte aufsteigend sortieren.' );

        $columnlinkstart = "<h2 class='thm_organizer_el_headlinktext'><a class='sortLink hasTip' title='";
        $columnlinkmiddle = "' href='javascript:reSort(";
        $lists['titlehead'] = $columnlinkstart."Titel::";
        $lists['authorhead'] = $columnlinkstart."Author::";
        $lists['roomhead'] = "<h2 class='thm_organizer_el_headlinktext'>Ressourcen</h2>";
        $lists['categoryhead'] = $columnlinkstart."Category::";
        $lists['datehead'] = $columnlinkstart."Datum::";
        if(isset($this->orderbydir) && isset($this->orderby))
        {
            if($this->orderby == 'title')
            {
                if($this->orderbydir == 'ASC')
                {
                    $lists['titlehead'] .= $downsorttext.$columnlinkmiddle."\"title\", \"DESC\")' >";
                    $lists['titlehead'] .= "Titel".$downsortimage."</a></h2>";
                }
                else
                {
                    $lists['titlehead'] .= $upsorttext.$columnlinkmiddle."\"title\", \"ASC\")' >";
                    $lists['titlehead'] .= "Titel".$upsortimage."</a></h2>";
                }
            }
            else
            {
                $lists['titlehead'] .= $upsorttext.$columnlinkmiddle."\"title\", \"ASC\")' >";
                $lists['titlehead'] .= "Titel</a></h2>";
            }
            if($this->orderby == 'author')
            {
                if($this->orderbydir == 'ASC')
                {
                    $lists['authorhead'] .= $downsorttext.$columnlinkmiddle."\"author\", \"DESC\")' >";
                    $lists['authorhead'] .= "Author".$downsortimage."</a></h2>";
                }
                else
                {
                    $lists['authorhead'] .= $upsorttext.$columnlinkmiddle."\"author\", \"ASC\")' >";
                    $lists['authorhead'] .= "Author".$upsortimage."</a></h2>";
                }
            }
            else
            {
                $lists['authorhead'] .= $upsorttext.$columnlinkmiddle."\"author\", \"ASC\")' >";
                $lists['authorhead'] .= "Author</a></h2>";
            }
            if($this->orderby == 'category')
            {
                if($this->orderbydir == 'ASC')
                {
                    $lists['categoryhead'] .= $downsorttext.$columnlinkmiddle."\"category\", \"DESC\")' >";
                    $lists['categoryhead'] .= "Kategorie".$downsortimage."</a></h2>";
                }
                else
                {
                    $lists['categoryhead'] .= $upsorttext.$columnlinkmiddle."\"category\", \"ASC\")' >";
                    $lists['categoryhead'] .= "Kategorie".$upsortimage."</a></h2>";
                }
            }
            else
            {
                $lists['categoryhead'] .= $upsorttext.$columnlinkmiddle."\"category\", \"ASC\")' >";
                $lists['categoryhead'] .= "Kategorie</a></h2>";
            }
            if($this->orderby == 'date')
            {
                if($this->orderbydir == 'ASC')
                {
                    $lists['datehead'] .= $downsorttext.$columnlinkmiddle."\"date\", \"DESC\")' >";
                    $lists['datehead'] .= "Datum".$downsortimage."</a></h2>";
                }
                else
                {
                    $lists['datehead'] .= $upsorttext.$columnlinkmiddle."\"date\", \"ASC\")' >";
                    $lists['datehead'] .= "Datum".$upsortimage."</a></h2>";
                }
            }
            else
            {
                $lists['datehead'] .= $upsorttext.$columnlinkmiddle."\"date\", \"ASC\")' >";
                $lists['datehead'] .= "Datum</a></h2>";
            }
        }
        else
        {
            $lists['titlehead'] .= $upsorttext.$columnlinkmiddle."\"title\", \"ASC\")' >";
            $lists['titlehead'] .= "Titel</a></h2>";
            $lists['authorhead'] .= $upsorttext.$columnlinkmiddle."\"author\", \"ASC\")' >";
            $lists['authorhead'] .= "Author</a></h2>";
            $lists['categoryhead'] .= $upsorttext.$columnlinkmiddle."\"category\", \"ASC\")' >";
            $lists['categoryhead'] .= "Kategorie</a></h2>";
            $lists['datehead'] .= $downsorttext.$columnlinkmiddle."\"date\", \"DESC\")' >";
            $lists['datehead'] .= "Datum".$downsortimage."</a></h2>";
        }

        $lists['date'] =  JHTML::_('calendar', $this->date = "", 'date', 'date', '%Y-%m-%d', array('size'=>'7',  'maxlength'=>'10'));

        $nocategories = array(1=>array('ecid'=>'-1','ecname'=>JText::_('Alle Kategorien')));
        $categories = array_merge($nocategories, $this->categories);
        $lists['category'] = JHTML::_('select.genericlist', $categories, 'category[]','id="category" class="inputbox" size="1"', 'ecid', 'ecname', $this->category );

        return $lists;
    }
}