<?php
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.model' );
 
/**
 * Room Display Model
 *
 */
class GiessenSchedulerModelRoomDisplay extends JModel
{

    var $data = null;

//    var $data->roomname = null;
//    var $data->roomid = null;
//    var $data->layout = null;
//    var $data->postdate = null; a date in the format yyyy-mm-dd
//    var $data->displaydate = null; a date in the format dd.mm.yy
//    var $data->semester = null; the semester schedule to be displayed
//    var $data->dayname = null; the german name of the weekday
//    var $daynumber = null; the number of the weekday (in respect to the week)
//    var $url = null;

    /**
     * Constructor
     *
     * @since 1.5
     */
    function __construct()
    {
        parent::__construct();
        $this->data = new stdClass();
        $this->checkIP();//sets roomname and layout
        $this->setDate();//sets date variables

//        //registered layout can also display resources
//        if(isset($this->data->layout) && $this->data->layout == registered)
//        {
//            $query = "SELECT MAX(start) AS start, url, sid
//                      FROM #__thm_organizer_monitor_schedule AS ms
//                        INNER JOIN #__thm_organizer_monitors AS m
//                            ON ms.monitorid = m.monitorid
//                      WHERE room = '".$this->data->roomname."'
//                          AND start <= '".$this->data->postdate."'";
//        }
//        //unregistered layouts will always display schedule data
//        else
//        {
//            $query = "SELECT MAX(start) AS start, url, sid
//                      FROM #__thm_organizer_monitor_schedule AS ms
//                        INNER JOIN #__thm_organizer_monitors AS m
//                            ON ms.monitorid = m.monitorid
//                      WHERE room = '".$this->data->roomname."'
//                          AND start <= '".$this->data->postdate."'
//                          AND sid != '-1'";
//        }
        $dbo = & JFactory::getDBO();
        $query = "SELECT sid FROM #__thm_organizer_roomip WHERE room = '".$this->data->roomname."'";
        $dbo->setQuery( $query );
        $result = $dbo->loadAssoc();
        if(!isset($result) || empty($result))
            $this->goodbye('No content has been selected for this monitor.');
        else
        {
            $this->data->semester = $result['sid'];
//            if(isset($result['url']))$this->url = $result['url'];
        }
        $this->getBlocks();
        $this->getData();
    }

     /**
      * Checks if the clients ip matches one of those stored in the db,
      * if not the roomname is set from post data if available
      * the layout is then set dependant upon registration
      *
      * @return void
      */
    function checkIP()
    {
        $client = $_SERVER['REMOTE_ADDR'];
        $dbo = & JFactory::getDBO();
        $query = "SELECT room, oid
                  FROM #__thm_organizer_roomip
                    INNER JOIN #__thm_organizer_objects ON oname = room
                  WHERE ip = '$client'";
        $dbo->setQuery( $query );
        $result = $dbo->loadAssoc();
        if(isset($result) || !empty($result))
        {
            $this->data->roomname = $result['room'];
            $this->data->roomid = $result['oid'];
            $this->data->layout = 'registered';
            return;
        }
        else
        {
            $roomname = JRequest::getVar('room');
            $query = "SELECT oname, oid
                      FROM #__thm_organizer_objects
                      WHERE oname = '$roomname'";
            $dbo->setQuery( $query );
            $result = $dbo->loadAssoc();
        }
        if(isset($result) || !empty($result))
        {
            $this->data->roomname = $result['oname'];
            $this->data->roomid = $result['oid'];
            $this->data->layout = 'unregistered';
            return;
        }
        else $this->goodbye("The room entered is not present in the available data.");
    }
	
    /**
     * Resolves a date from $_POST or system
     * to its german name and a date string with german formatting.
     *
     */
    function setDate()
    {
        $postdate = JRequest::getVar('date');
        if(isset($postdate)) $date = strtotime($postdate);
        else $date = false;
        if($date != false)
        {
            $this->data->postdate = $postdate;
            $date = getdate($date); //date info as array
            $this->data->daynumber = $date['wday'];
            $this->data->displaydate = $date['mday'].".".$date['mon'].".".substr($date['year'], 2);
        }
        //no date was entered in the form /an automatic redirect occured /invalid data
        else
        {
            $this->data->postdate = date('y-m-d');
            $this->data->daynumber = date('w');
            $this->data->displaydate = date('d.m.y');
        }
        switch($this->data->daynumber)
        {
            case 0:
                $this->data->dayname = "Sonntag";
                break;
            case 1:
                $this->data->dayname = "Montag";
                break;
            case 2:
                $this->data->dayname = "Dienstag";
                break;
            case 3:
                $this->data->dayname = "Mittwoch";
                break;
            case 4:
                $this->data->dayname = "Donnerstag";
                break;
            case 5:
                $this->data->dayname = "Freitag";
                break;
            case 6:
                $this->data->dayname = "Samstag";
                break;
        }
    }

	
    /**
    * Creates an array with the start and end times of the periods
    *
    * @return either an array or void if the text file was empty/incorrectly formatted
    */
    function getBlocks()
    {
        //establish db object
        $dbo = & JFactory::getDBO();
        $query = "SELECT tpid, period,
                    SUBSTRING(starttime, 1, 5) AS starttime,
                    SUBSTRING(endtime, 1, 5) AS endtime
                  FROM #__thm_organizer_timeperiods
                  WHERE sid ='".$this->data->semester."'
                        AND day = '".$this->data->daynumber."'";
        $dbo->setQuery( $query );
        $timeperiods = $dbo->loadAssocList();
        if ($dbo->getErrorNum()) return "error";
        $blocks = array();
        foreach($timeperiods as $timeperiod)
        {
            $blocks[$timeperiod['period']]['starttime'] = $timeperiod['starttime'];
            $blocks[$timeperiod['period']]['endtime'] = $timeperiod['endtime'];
            $blocks[$timeperiod['period']]['tpid'] = $timeperiod['tpid'];
        }
        asort($blocks);
        $this->data->blocks = $blocks;
        return;
    }
	
    /**
     * Gets the lessons for the room and day
     *
     * @return either an array of lessons or void if none were found
     */
    function getData()
    {
        $dbo = & JFactory::getDBO();

        $blocks =& $this->data->blocks;
        $roomid = $this->data->roomid;
        $semester = $this->data->semester;
        $globalobjects = array();
        $globalobjects[] = $roomid;
        $wheretime = "((startdate <= '".$this->data->postdate."' AND enddate >= '".$this->data->postdate."')
                            OR (startdate = '".$this->data->postdate."' AND enddate = '0000-00-00'))";

        $query = "SELECT lid AS lessonid, oname AS name, period, oalias AS moduleid, tp.tpid
                  FROM #__thm_organizer_lessonperiods AS lp
                    INNER JOIN #__thm_organizer_objects AS o ON lid = oid
                    INNER JOIN #__thm_organizer_timeperiods AS tp ON lp.tpid = tp.tpid
                  WHERE rid = '$roomid'
                    AND lp.sid = '$semester'
                    AND o.sid = '$semester'
                    AND day = '".$this->data->daynumber."';";
        $dbo->setQuery( $query );
        $lessons = $dbo->loadAssocList('period');
        foreach($blocks as $k => $v)
        {
            $blockobjectstring = '';
            $blocks[$k]['objects'] = array();
            $blocks[$k]['objects'][] = $roomid;
            if(isset($lessons[$k]))
            {
                $blocks[$k]['lessonid'] = $lessons[$k]['lessonid'];
                $blocks[$k]['subject'] = $lessons[$k]['name'];
                if(isset($lessons[$k]['module']) && !empty($lessons[$k]['module']))
                    $blocks[$k]['module'] = $lessons[$k]['module'];
                $blocks[$k]['lessonid'] = $lessons[$k]['lessonid'];
                $blocks[$k]['lessonid'] = $lessons[$k]['lessonid'];

                //teacher processing
                $query = "SELECT tid
                          FROM #__thm_organizer_lessonperiods AS lp
                            INNER JOIN #__thm_organizer_objects ON tid = oid
                          WHERE lid = '".$lessons[$k]['lessonid']."'
                            AND tpid = '".$lessons[$k]['tpid']."'
                            AND lp.sid = '$semester'";
                $dbo->setQuery( $query );
                $blockteacherids = $dbo->loadResultArray();
                foreach($blockteacherids as $btid)
                {
                    $blocks[$k]['objects'][] = $btid;
                    $globalobjects[] = $btid;
                }
                $query = "SELECT DISTINCT oname
                          FROM #__thm_organizer_lessonperiods AS lp
                            INNER JOIN #__thm_organizer_objects ON tid = oid
                          WHERE lid = '".$lessons[$k]['lessonid']."'
                            AND tpid = '".$lessons[$k]['tpid']."'
                            AND lp.sid = '$semester'";
                $dbo->setQuery( $query );
                $blockteachernamess = $dbo->loadResultArray();
                $blocks[$k]['teachers'] = implode(', ', $blockteachernamess);

                //class processing
                $query = "SELECT cid
                          FROM #__thm_organizer_lessons AS l
                            INNER JOIN #__thm_organizer_objects ON cid = oid
                          WHERE lid = '".$lessons[$k]['lessonid']."'
                            AND l.sid = '$semester'";
                $dbo->setQuery( $query );
                $blockclassids = $dbo->loadResultArray();
                foreach($blockclassids as $bcid)
                {
                    $blocks[$k]['objects'][] = $bcid;
                    $globalobjects[] = $bcid;
                }
            }

            //block specific event processing
            //reserving events can overwrite the lesson name
            $blockobjectstring = "( '".implode("', '", $blocks[$k]['objects'])."' )";
            $query = "SELECT DISTINCT (eid), title,
                        SUBSTRING(starttime, 1, 5) AS starttime,
                        SUBSTRING(endtime, 1, 5) AS endtime
                      FROM #__thm_organizer_events as gse
                        LEFT JOIN #__thm_organizer_eventobjects ON eid = eventid
                        INNER JOIN #__thm_organizer_categories ON ecatid = ecid
                      WHERE $wheretime
                        AND access = '0'
                        AND reservingp = '1'
                        AND objectid IN $blockobjectstring;";
            $dbo->setQuery( $query );
            $blockevents = $dbo->loadAssocList();
            if(isset($blockevents) && count($blockevents) == 1)
            {
                $blocks[$k]['subject'] = $blockevents[0]['title'];
                $blocks[$k]['times'] = $this->makeEventTime($blockevents[0]);
                $blocks[$k]['eventid'] = $blockevents[0]['eid'];
            }
            if(isset($blockevents) && count($blockevents) > 1)
            {
                $blocks[$k]['subject'] = "verschiedene Termine";
            }
        }

        $globalobjectstring = "('".implode("', '", $globalobjects)."')";

        //reserved resources in a given room
        $query = "SELECT DISTINCT (eid), title, edescription, startdate, enddate, name AS author,
                    SUBSTRING(starttime, 1, 5) AS starttime,
                    SUBSTRING(endtime, 1, 5) AS endtime
                  FROM #__thm_organizer_events as gse
                    LEFT JOIN #__thm_organizer_eventobjects ON eid = eventid
                    INNER JOIN #__thm_organizer_categories ON ecatid = ecid
                    INNER JOIN #__users ON created_by = id
                  WHERE $wheretime
                    AND access = '0'
                    AND reservingp = '1'
                    AND objectid IN $globalobjectstring;";
        $dbo->setQuery( $query );
        $reservingevents = $dbo->loadAssocList();
        $reservingeventarray = array();//used in global events query
        foreach($reservingevents as $k => $v)
        {
            $reservingeventarray[] = $v['eid'];//used in global events query
            $eventtimes = '';
            $eventtimes = $this->makeEventTime($v);
            $reservingevents[$k]['times'] = $eventtimes;
            $eventdatess = '';
            $eventdates = $this->makeEventDate($v);
            $reservingevents[$k]['dates'] = $eventdates;
        }
        $this->data->reservingevents = $reservingevents;

        //annotations to resources which are not reserving
        $query = "SELECT DISTINCT (eid), title, edescription, startdate, enddate, name AS author,
                    SUBSTRING(starttime, 1, 5) AS starttime,
                    SUBSTRING(endtime, 1, 5) AS endtime
                  FROM #__thm_organizer_events as gse
                    LEFT JOIN #__thm_organizer_eventobjects ON eid = eventid
                    INNER JOIN #__thm_organizer_categories ON ecatid = ecid
                    INNER JOIN #__users ON created_by = id
                  WHERE $wheretime
                    AND access = '0'
                    AND reservingp = '0'
                    AND objectid IN $globalobjectstring;";
        $dbo->setQuery( $query );
        $notes = $dbo->loadAssocList();
        foreach($notes as $k => $v)
        {
            $eventtimes = '';
            $eventtimes = $this->makeEventTime($v);
            $notes[$k]['times'] = $eventtimes;
            $eventdatess = '';
            $eventdates = $this->makeEventDate($v);
            $notes[$k]['dates'] = $eventdates;
        }
        $this->data->notes = $notes;

        //future events for the room
        $query = "SELECT DISTINCT (eid), title, edescription, startdate, enddate, name AS author,
                    SUBSTRING(starttime, 1, 5) AS starttime,
                    SUBSTRING(endtime, 1, 5) AS endtime
                  FROM #__thm_organizer_events as gse
                    LEFT JOIN #__thm_organizer_eventobjects ON eid = eventid
                    INNER JOIN #__thm_organizer_categories ON ecatid = ecid
                    INNER JOIN #__users ON created_by = id
                  WHERE startdate > '".$this->data->postdate."'
                    AND access = '0'
                    AND objectid = '$roomid'";
        $dbo->setQuery( $query );
        $futureevents = $dbo->loadAssocList();
        foreach($futureevents as $k => $v)
        {
            $eventtimes = '';
            $eventtimes = $this->makeEventTime($v);
            $futureevents[$k]['times'] = $eventtimes;
            $eventdatess = '';
            $eventdates = $this->makeEventDate($v);
            $futureevents[$k]['dates'] = $eventdates;
        }
        $this->data->futureevents = $futureevents;

        //global events which do not reserve resources in this room
        $reservingevents = "( '".implode("', '", $reservingeventarray)."' )";
        $query = "SELECT DISTINCT (eid), title, edescription, startdate, enddate, name AS author,
                    SUBSTRING(starttime, 1, 5) AS starttime,
                    SUBSTRING(endtime, 1, 5) AS endtime
                  FROM #__thm_organizer_events as gse
                    LEFT JOIN #__thm_organizer_eventobjects ON eid = eventid
                    INNER JOIN #__thm_organizer_categories ON ecatid = ecid
                    INNER JOIN #__users ON created_by = id
                  WHERE $wheretime
                    AND access = '0'
                    AND globalp = '1'
                    AND eid NOT IN $reservingevents";
        $dbo->setQuery( $query );
        $globalevents = $dbo->loadAssocList();
        foreach($globalevents as $k => $v)
        {
            $eventtimes = '';
            $eventtimes = $this->makeEventTime($v);
            $globalevents[$k]['times'] = $eventtimes;
            $eventdatess = '';
            $eventdates = $this->makeEventDate($v);
            $globalevents[$k]['dates'] = $eventdates;
        }
        $this->data->globalevents = $globalevents;
    }

    /**
     * Makes a time string from the start and end times of an event
     * if existent
     *
     * @param array $e sql result array
     * @return string times of event / all day event
     */
    function makeEventTime($e)
    {
        $timestring = "";
        if(isset($e['starttime']) && $e['starttime'] != "00:00")
        {
            $timestring .= "von ".$e['starttime'];
            if(isset($e['endtime']) && $e['endtime'] != "00:00")
                $timestring .= " bis ".$e['endtime'];
        }
        else if(isset($e['endtime']) && $e['endtime'] != "00:00")
            $timestring .= " bis ".$e['endtime'];
        else $timestring .= "ganzt&auml;giges Ereignis<br />";
        return $timestring;
    }

        /**
     * Makes a date string from the start and end dates of an event
     * if existent
     *
     * @param array $e sql result array
     * @return string date(s) of event
     */
    function makeEventDate($e)
    {
        if(!isset($e['startdate']))return '';
        $datestring = "";
        if(isset($e['enddate']) && $e['enddate'] != "0000-00-00")
        {
            $datestring = "ab ".$e['startdate']." bis ".$e['enddate'];
        }
        else $datestring = "am ".$e['startdate'];
        return $datestring;
    }

    function goodbye($partingthoughts)
    {
            $app =& JFactory::getApplication();
            $rd_string = 'index.php?option=com_thm_organizer&view=roomlist';
            $app->redirect($rd_string, $partingthoughts);
    }
}

