<?php
 // @codingStandardsIgnoreFile
require_once(dirname(__FILE__).'/iCalcreator.class.php');

class icaltestlib
{
    function get($what, $username = null, $resourcename = null, $fachsemester = null)
    {
        switch($what)
        {
            case 'EventCategories':
                return getEventCategories($username);
            case 'Events':
                return getEvents($username, $resourcename);
            case 'ResourceTypes':
                return getResourceTypes($username);
            case 'Fachsemesters':
                return getFachsemesters();
            case 'ResourcePlan':
                return getResourcePlan($username, $resourcename, $fachsemester);
        }
    }
 
 
    function getEventCategories($username)
    {
        $dbo =JFactory::getDBO();
        $query = "SELECT gid
                  FROM #__users
                  WHERE username = '$username'";
        $dbo->setQuery($query);
        $gid = $dbo->query();
        if($dbo->getErrorNum())
        {
            return array('error'=>'User nicht vorhanden');
        }
        $query = "SELECT ecid, ecname
                  FROM #__thm_organizer_categories
                  WHERE access <= '$gid'";
        $dbo->setQuery($query);
        return $dbo->loadAssocList();
    }
 
    function getEvents($username, $categoryid = null)
    {
        $dbo =JFactory::getDBO();
        $query = "SELECT gid
                  FROM #__users
                  WHERE username = '$username'";
        $dbo->setQuery($query);
        $gid = $dbo->query();
        if($dbo->getErrorNum())
        {
            return array('error'=>'User nicht vorhanden');
        }
        $query = "SELECT ecid
                  FROM #__thm_organizer_categories
                  WHERE access <= '$gid'";
        if($categoryid) $query .= " AND ecid = '$categoryid'";
        $dbo->setQuery($query);
        $cid = $dbo->loadResult();
        if($dbo->getErrorNum())
        {
            return array('error'=>'Zugriff verweigert oder Kategorie nicht vorhanden');
        }
        $query = "SELECT eid AS lid, title AS name,
                    (SELECT 'sporadic') AS type,(SELECT '') AS dow,
                    REPLACE( startdate, '-', '') AS sdate,
                    REPLACE( enddate, '-', '') AS edate,
                    starttime AS stime, endtime AS etime
                  FROM #__thm_organizer_events
                      INNER JOIN #__thm_organizer_categories
                          ON ecatid = ecid
                  WHERE ecid = '$cid'";
        $wtf = $query;
        $dbo->setQuery($query);
        $events = $dbo->loadAssocList();
        $eventids = array();
        foreach($events as $event)
            $eventids[] = $event['lid'];
        $query = "SELECT eventid AS lid, oname, otype
                  FROM #__thm_organizer_eventobjects
                      INNER JOIN #__thm_organizer_objects
                          ON objectid = oid
                  WHERE eventid IN ( '".implode("', '", $eventids)."' )";
        $dbo->setQuery($query);
        $eventobjects = $dbo->loadAssocList();
        foreach($events as $ek => $ev)
        {
            foreach($eventobjects as $eventobject)
            {
                if($ev['lid'] == $eventobject['lid'])
                {
                    switch ($eventobject['otype'])
                    {
                        case "teacher":
                            if($events[$ek]['doz']) $events[$ek]['doz'] = $events[$ek]['doz']." ".$eventobject['oname'];
                            else $events[$ek]['doz'] = $eventobject['oname'];
                            break;
                        case "room":
                            if($events[$ek]['room']) $events[$ek]['room'] = $events[$ek]['room']." ".$eventobject['oname'];
                            else $events[$ek]['room'] = $eventobject['oname'];
                            break;
                        case "class":
                            if($events[$ek]['clas']) $events[$ek]['clas'] = $events[$ek]['clas']." ".$eventobject['oname'];
                            else $events[$ek]['clas'] = $eventobject['oname'];
                            break;
                    }
                }
            }
        }
        $stuff = json_encode($events);
        return $stuff;
    }
 
    function getResourceTypes($username)
    {
        $dbo =JFactory::getDBO();
        $query = "SELECT gid
                  FROM #__users
                  WHERE username = '$username'";
        $dbo->setQuery($query);
        $gid = $dbo->query();
        if($dbo->getErrorNum())
        {
            return array('error'=>'User nicht vorhanden');
        }
        $resourcetypes = array('teacher' => 'Dozent', 'class' => 'Semestergang', 'room' => 'Raum');
        if($gid < 19) unset($resourcetypes['teacher']);
        return $resourcetypes;
    }
 
    function getFachsemesters()
    {
        $dbo =JFactory::getDBO();
        $query = "SELECT sid, CONCAT(orgunit, '/', semester) AS fachsemester
                  FROM #__thm_organizer_semesters";
        $dbo->setQuery($query);
        return $dbo->loadAssocList();
    }
 
    function getResourcePlan($username, $resourcename, $fachsemester)
    {
        if($resourcename == null || $fachsemester == null) return array('error' => 'fehlende Angaben');
        $dbo =JFactory::getDBO();
        $query = "SELECT gid
                  FROM #__users
                  WHERE username = '$username'";
        $dbo->setQuery($query);
        $gid = $dbo->loadResult();
        if($dbo->getErrorNum())
        {
            return array('error'=>'User nicht vorhanden');
        }
        $query = "SELECT otype
                  FROM #__thm_organizer_objects
                  WHERE oname LIKE '%$resourcename%'";
        $dbo->setQuery($query);
        $types = $dbo->loadAssocList();
        foreach($types as $type)
            if($type['otype'] == 'teacher')
            {
                if($gid < 19)
                {
                    return array('error'=>'Zugriff verweigert');
                }
            }
 
        $query = "SELECT lo.oid AS lid, lo.oname AS name, co.oname AS clas,
                            (SELECT 'cyclic') AS type, (SELECT '') AS sdate, (SELECT '') AS edate,
                            tobj.oname AS doz, ro.oname AS room, day AS dow, starttime AS stime, endtime AS etime
                  FROM #__thm_organizer_objects AS lo
                      INNER JOIN #__thm_organizer_lessons AS l
                          ON lo.oid = l.lid
                      INNER JOIN #__thm_organizer_timeperiods AS tp
                          ON l.tpid = tp.tpid
                      INNER JOIN #__thm_organizer_objects as ro
                          ON l.rid = ro.oid
                      INNER JOIN #__thm_organizer_objects as tobj
                          ON l.tid = tobj.oid
                      LEFT JOIN #__thm_organizer_moduleclasses AS mc
                          ON lo.oalias = mc.modid
                      LEFT JOIN #__thm_organizer_objects AS co
                          ON mc.cid = co.oid
                  WHERE lo.otype = 'lesson'
                      AND lo.sid = '$fachsemester'
                      AND ( co.oname = '$resourcename'
                          OR tobj.oname = '$resourcename'
                          OR ro.oname = '$resourcename' )";
        $dbo->setQuery($query);
        $hits = $dbo->loadAssocList();
        $counter = 0;
        if(count($hits) > 0)
        {
            $lessons = array();
            foreach($hits as $hit)
            {
                $contained = false;
                foreach($lessons as $lk => $lv)
                {
                    if($hit['lid'] == $lv['lid'] && $hit['stime'] == $lv['stime']
                        && $hit['etime'] == $lv['etime'] && $hit['dow'] == $lv['dow'])
                    {
                        $teachers = explode(" ", $lv['doz']);
                        $isthere = in_array($hit['doz'], $teachers);
                        if(!$isthere) $lessons[$lk]['doz'] = $lessons[$lk]['doz']." ".$hit['doz'];
                        $classes = explode(" ", $lv['clas']);
                        $isthere = in_array($hit['clas'], $classes);
                        if(!$isthere) $lessons[$lk]['clas'] = $lessons[$lk]['clas']." ".$hit['clas'];
                        $rooms = explode(" ", $lv['room']);
                        $isthere = in_array($hit['room'], $rooms);
                        if(!$isthere) $lessons[$lk]['room'] = $lessons[$lk]['room']." ".$hit['room'];
                        $contained = true;
                    }
                }
                if($contained == false)
                {
                    $lessons[]= $hit;
                }
            }
        }
        unset($hits);
        $query = "SELECT eid AS lid, title AS name,
                    (SELECT 'sporadic') AS type,(SELECT '') AS dow,
                    REPLACE( startdate, '-', '') AS sdate,
                    REPLACE( enddate, '-', '') AS edate,
                    starttime AS stime, endtime AS etime
                  FROM #__thm_organizer_events
                      INNER JOIN #__thm_organizer_eventobjects
                          ON eid = eventid
                      INNER JOIN #__thm_organizer_objects
                          ON objectid = oid
                      INNER JOIN #__thm_organizer_categories
                          ON ecatid = ecid
                  WHERE oname = '$resourcename'
                      AND access <= '$gid'";
        $wtf = $query;
        $dbo->setQuery($query);
        $events = $dbo->loadAssocList();
        $eventids = array();
        foreach($events as $event)
            $eventids[] = $event['lid'];
        $query = "SELECT eventid AS lid, oname, otype
                  FROM #__thm_organizer_eventobjects
                      INNER JOIN #__thm_organizer_objects
                          ON objectid = oid
                  WHERE eventid IN ( '".implode("', '", $eventids)."' )";
        $dbo->setQuery($query);
        $eventobjects = $dbo->loadAssocList();
        $test = "";
        foreach($events as $ek => $ev)
        {
            foreach($eventobjects as $eventobject)
            {
                if($ev['lid'] == $eventobject['lid'])
                {
                    switch ($eventobject['otype'])
                    {
                        case "teacher":
                            if($events[$ek]['doz']) $events[$ek]['doz'] = $events[$ek]['doz']." ".$eventobject['oname'];
                            else $events[$ek]['doz'] = $eventobject['oname'];
                            break;
                        case "room":
                            if($events[$ek]['room']) $events[$ek]['room'] = $events[$ek]['room']." ".$eventobject['oname'];
                            else $events[$ek]['room'] = $eventobject['oname'];
                            break;
                        case "class":
                            if($events[$ek]['clas']) $events[$ek]['clas'] = $events[$ek]['clas']." ".$eventobject['oname'];
                            else $events[$ek]['clas'] = $eventobject['oname'];
                            break;
                    }
                }
            }
        }
        if($events) $resourceplan = json_encode(array_merge($lessons,$events));
        else $resourceplan = $lessons;
        return $resourceplan;
    }
 
    function periodtotime($day, $sid, $period)
    {
        $dbo =JFactory::getDBO();
        $query = "SELECT *
                  FROM #__thm_organizer_timeperiods
                  WHERE day = '$day'
                      AND sid = '$sid'";
        $dbo->setQuery($query);
        $times = $dbo->loadAssocList();
        foreach($times as $time)
        {
            if($time['period'] == $period) return array('starttime' => $time['starttime'], 'endtime' => $time['endtime']);
        }
        return false;
    }
 
    function daynumtoday($daynum)
    {
        $days = array(
            0=>"SU",
            1=>"MO",
            2=>"TU",
            3=>"WE",
            4=>"TH",
            5=>"FR",
            6=>"SA");
        return $days[$daynum];
    }
 
    function stuffidontwanttoseerightnow()
    {
    $begin = $_REQUEST['begin'];
        $end = $_REQUEST['end'];
 
        $syear = substr($begin, 0, 4);
        $smonth = substr($begin, 4, 2);
        $sday = substr($begin, -2);
 
        $eyear = substr($end, 0, 4);
        $emonth = substr($end, 4, 2);
        $eday = substr($end, -2);
 
        $begin = date( "Y-m-d", strtotime($syear."-".$smonth."-".$sday));
        $end = date( "Y-m-d", strtotime($eyear."-".$emonth."-".$eday) + (1 *(3600 * 24)));
 
        $timestamp = time();
 
 
        if(date("Y-m-d",$timestamp) > $begin)
            $begin = date("Y-m-d",$timestamp);
 
        if($begin >= $merged[0]->dates && $begin < $merged[0]->enddates)
        {
            $begin = $merged[0]->enddates;
            unset($merged[0]);
            $merged = array_values($merged);
        }
 
        if($merged[count($merged)-1]->dates < $end && $end <= $merged[count($merged)-1]->enddates)
        {
            $end = $merged[count($merged)-1]->dates;
            unset($merged[count($merged)-1]);
            $merged = array_values($merged);
        }
 
        $alldates = array();
        $alldates[count($alldates)] = $begin;
        foreach($merged as $item)
        {
            $alldates[count($alldates)] = $item->dates;
            $alldates[count($alldates)] = $item->enddates;
        }
        $alldates[count($alldates)] = $end;
 
        $username = $_REQUEST['username'];
        $title = $_REQUEST['title'];
 
        if (!$title) {
            $title = 'stundenplan';
        }
 
        // Decodiert uebergebene POST Daten
        $arr = json_decode( file_get_contents("php://input") );//mysched per POST
 
        $v = new vcalendar();
        $v->setConfig( 'unique_id', "MySched" );
        $v->setConfig( "lang", "de" );
        $v->setProperty( "x-wr-calname", $_REQUEST['title'] );
        $v->setProperty( "X-WR-CALDESC", "Calendar Description" );
        $v->setProperty( "X-WR-TIMEZONE", "Europe/Berlin" );
        $v->setProperty("PRODID","-//212.201.14.161//NONSGML iCalcreator 2.6//");
        $v->setProperty("VERSION","2.0");
        $v->setProperty("METHOD","PUBLISH");
        $date = array("year" => 2009, "month" => 10, "day" => 20, "hour" => 10, "min" => 0, "sec" => 0, "tz" => "+0100");
 
        $t = new vtimezone( );
        $t->setProperty("TZID","Europe/Berlin");
 
        $ts = new vtimezone( 'standard' );
        $ts->setProperty("DTSTART",1601, 1, 1, 0 ,0, 0 );
        $ts->setProperty("TZOFFSETFROM","+0100");
        $ts->setProperty("TZOFFSETTO","+0100");
        $ts->setProperty("TZNAME","Standard Time");
 
        $t->setComponent( $ts );
        $v->setComponent( $t );
 
        $personaladded = array();
 
        if(is_array($arr))
        foreach($arr as $item)
        {
            $add = array();
            $item->cell = str_replace("<br/>", "-=|=-", $item->cell);
            $item->cell = strip_tags($item->cell, "<b><small>" );
            $item->cell = str_replace("<b>", "", $item->cell);
            $item->cell = str_replace("</b>", "", $item->cell);
            $item->cell = str_replace("<small>", "", $item->cell);
            $item->cell = str_replace("</small>", "", $item->cell);
            $item->cell = str_replace("/", "-=|=-", $item->cell);
            $itemdata = explode("-=|=-", $item->cell);
            //sollte enthalten $itemdata[0] = Veranstaltungsname, $itemdata[1] = Dozent(en), $itemdata[2] = Ra(e)um(e)
 
            if($item->type == "personal")
            {
                $times = explode("-", $item->clas);
                $add = explode("_", $item->key);
            }
            else
            {
                $times = blocktotime($item->block);
            }
 
            $begintime = explode(":", $times[0]);
            $endtime = explode(":", $times[1]);
 
            $ok = false;
 
            $tempstring = "";
            if(count($add) > 0)
            {
                $counter = count($add)-1;
                if(!$personaladded[$add[$counter]])
                {
                    $personaladded[$add[$counter]] = $add[$counter];
                    $tempstring = $tempstring.$add[$counter]." ";
                    $ok = true;
                }
            }
            else
            {
                $ok = true;
                $item->desc = strip_tags($item->desc, "");
                $desc = getModuleDesc(strtolower($item->desc), $LSFret);
                if(strtolower($desc) != strtolower($item->desc))
                {
                    $item->desc = str_replace("</br>", "\n",str_replace("<br/>", "\n",str_replace("<br />", "\n", strip_tags($desc, "<br>"))));
                }
            }
 
            if($ok)
            for($i = 0; $i < count($alldates); $i = $i+2)
            {
                $days = array("SU","MO","TU","WE","TH","FR","SA");
                $itemday = daynumtoday($item->dow);
 
                $itemtimestamp = strtotime($alldates[$i]);
                $counter = 0;
                $breakit = false;
 
                if($i+1 < count($alldates))
                {
                    while(!$breakit)
                    {
                        $tag = date("w", $itemtimestamp + ($counter *(3600 * 24)));
                        $heute = $days[$tag];
                        if($itemday == $heute)
                        {
                            if($alldates[count($alldates)-1] >= date("Y-m-d", $itemtimestamp + ($counter *(3600 * 24))))
                            {
                                for($a = 0; $a < count($alldates); $a = $a+2)
                                {
                                    if($alldates[$a] <= date("Y-m-d", $itemtimestamp + ($counter *(3600 * 24))) && $alldates[$a+1] >= date("Y-m-d", $itemtimestamp + ($counter *(3600 * 24))))
                                    {
                                        if($i < $a)
                                        {
                                            $i = $a;
                                        }
                                        $itemtimestamp = $itemtimestamp + ($counter *(3600 * 24));
                                        $endarr = explode("-", $alldates[$a+1]);
                                        $breakit = true;
                                        break;
                                    }
                                }
                            }
                            else
                            {
                                $breakit = true;
                            }
                        }
                        $counter++;
                    }
                    $beginarr = explode("-", date("Y-m-d", $itemtimestamp));
                }
                else
                {
                    $n = $i + 1;
                    $beginarr = explode("-", $alldates[$i]);
                    $endarr = explode("-", $alldates[$n]);
                }
 
                $startdate = array("year" => $beginarr[0], "month" => $beginarr[1], "day" => $beginarr[2], "hour" => $begintime[0], "min" => $begintime[1], "sec" => 0, "tz" => "Europe/Berlin");
                $enddate = array("year" => $beginarr[0], "month" => $beginarr[1], "day" => $beginarr[2], "hour" => $endtime[0], "min" => $endtime[1], "sec" => 0, "tz" => "Europe/Berlin");
                $endarrdate = array("year" => $endarr[0], "month" => $endarr[1], "day" => $endarr[2], "hour" => 0, "min" => 0, "sec" => 0, "tz" => "Europe/Berlin");
 
                $e = new vevent();
                $e->setProperty( "ORGANIZER", trim($itemdata[1]));
                $e->setProperty( "DTSTART", $startdate);
                $e->setProperty( "DTEND", $enddate);
                $e->setProperty( "RRULE", array("FREQ"=>"WEEKLY","UNTIL"=>$endarrdate,"BYDAY"=>array("DAY"=>daynumtoday($item->dow)),"WKST"=>"MO"));
                $e->setProperty( "LOCATION", trim($itemdata[2]));
                $e->setProperty( "TRANSP", "OPAQUE");
                $e->setProperty( "SEQUENCE", "0");
                $e->setProperty( "SUMMARY", trim($itemdata[0]));
                $e->setProperty( "PRIORITY", "5");
                $e->setProperty( "DESCRIPTION", $item->desc);
                //$e->setProperty( "EXDATE" , array( array('year'=>2009,'month'=>10,'day'=>29) ) , array( 'VALUE' => 'DATE' ));
 
                $v->setComponent( $e );
            }
        }
 
        if($cfg['sync_files'] == 1)
        {
            $res = $dbj->query( "SELECT registerDate FROM ".$cfg['jdb_table_user']." WHERE username='".$_REQUEST['username']."'" );
 
            if( count($res->num_rows) > 0 && trim($_REQUEST['username']) != "" && trim($_REQUEST['username']) != "undefined" )
            {
                $path = $_REQUEST['username'].strtotime($res->fetch_object()->registerDate);
            }
            else
            {
                $path = "";
            }
 
            if(!is_dir($cfg['pdf_downloadFolder'].$path))
            {
                //Ordner erstellen
                @mkdir ($cfg['pdf_downloadFolder'].$path, 0700);
            }
 
            $v->saveCalendar($cfg['pdf_downloadFolder'].$path."/", $title.'.ics');
 
            $resparr = array();
            if(isset($_REQUEST['username']))
            {
                if($_REQUEST['username'] != "")
                {
                    $arrexpl = explode("?", $_SERVER['HTTP_REFERER']);
                    $resparr['url'] = $arrexpl[0].str_replace(dirname(__FILE__)."/","" ,$cfg['pdf_downloadFolder']).$path."/".$_REQUEST['username'].".ics";
                    response(true, $resparr);
                }
                else
                {
                    $resparr['url'] = "false";
                    response(true, $resparr);
                }
            }
            else
            {
                $resparr['url'] = "false";
                response(true, $resparr);
            }
        }
        else
        {
 
            if($title == "Mein Stundenplan" && $username != "")
            {
                $title = $username." - ".$title;
            }
 
            $v->saveCalendar($cfg['pdf_downloadFolder'], $title.'.ics');
            $resparr['url'] = "false";
            response(true, $resparr);
        }
    }
}




?>