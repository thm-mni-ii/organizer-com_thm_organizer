<?php
/**
 * Controller for the ScheduleManager View of Giessen Scheduler
 */
 
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.application.component.controller');
 
class thm_organizerControllerScheduleList extends JController
{
	/**
	 * Uploads the file to the DB, and checks Data for consistency and missing information.
	 */
    function schedule_upload()
    {
		$dbo = & JFactory::getDBO();
    	$user =& JFactory::getUser();
        if($user->gid >= 24) $access = true;
        else $access = false ;
        if($access)
        {
	    	$fileType = $_FILES['schedule']['type'];
			$sid = JRequest::getVar('semesterid');
			$link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
			if($fileType == "text/xml")
			{
				$fileName = $_FILES['schedule']['name'];
				$tmpName  = $_FILES['schedule']['tmp_name'];
				$fileSize = $_FILES['schedule']['size'];
				$date = date('Y-m-d');
				$dbo = & JFactory::getDBO();
				$fp = fopen($tmpName, 'r');
				$content = fread($fp, filesize($tmpName));
				fclose($fp);

				//var_dump($content);
				$dDoc = new DOMDocument();
				$dDoc->loadXML($content);
				$document = $dDoc->documentElement;
				
				//variables for checking data consistency
				$error = ""; $erray = array(); $descriptions = array();
				$departments = array(); $timeperiods = array(); $classes = array();
				$lessonclasses = array(); $subjects = array(); $subjectobjects = array();
				$teachers = array(); $lessons = array(); $rooms = array();
				
				$creationdate = $document->getAttribute("date");
				if(!$creationdate)
				{
					$error = "Fehlende Erzeugungsdatum des Dokuments.";
			    	if(!in_array($error,$erray))$erray[] = $error;
				}
				
				$begindatenodes = $document->getElementsByTagName("schoolyearbegindate");
				if($begindatenodes)
					foreach($begindatenodes as $bdn)
					{
						$startdate = trim($bdn->textContent);
					    if(!$startdate)
					    {
					    	$error = "Fehlende Beginndatum.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					}
				else
				{
					$error = "Fehlende Beginndatum.";
			    	if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($begindatenodes, $bdn);
					
				$enddatenodes = $document->getElementsByTagName("schoolyearenddate");
				if($enddatenodes)
					foreach($enddatenodes as $edn)
					{
						$enddate = trim($edn->textContent);
					    if(!$enddate)
					    {
					    	$error = "Fehlende Enddatum.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					}
				else
				{
					$error = "Fehlende Enddatum.";
			    	if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($enddatenodes, $edn);
				
				$header1nodes = $document->getElementsByTagName("header1");
				if($header1nodes)
					foreach($header1nodes as $h1n)
					{
					    $details = explode(',', $h1n->textContent);
					    if(!count($details) > 3)
					    {
					    	$error = "Fehlende Angaben zum Document Department.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					}
				else
				{
					$error = "Fehlende Angabe zum Document Department.";
			    	if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($header1nodes, $h1n);
				
				//the structure of this element is not fully developed yet
				/*$header2nodes = $document->getElementsByTagName("header2");
				if($header2nodes)
					foreach($header2nodes as $h2n)
					{
					    $details = explode(',', $h2n->textContent);
					    if(!count($details) > 3)
					    {
					    	$error = "Fehlende Angaben zum Document Semester.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					}
				else
				{
					$error = "Fehlende Angaben zum Document Semester.";
			    	if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($header2nodes, $h2n);*/
					
				//descriptions are used to "type" a room
				$descriptionnodes = $document->getElementsByTagName( "description" );
				if($descriptionnodes)
					foreach( $descriptionnodes as $description )
					{		
						$descid = "";
					    $descid = trim($description->getAttribute("id"));
					    foreach($description->getElementsByTagName("longname") as $longname)
					    {
					    	$desc = trim($longname->textContent);
					    }
					    if(!$desc)
					    {
					    	$error = "Fehlende Longname in Descriptions $descid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
			    			continue;
					    }
					    $descriptions[$descid] = $desc;
					    unset( $longname);
					}
				else
				{
					$error = "S&auml;mtliche Descriptionangaben fehlen.";
	    			if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($description, $descriptionnodes);

				//collects data specific to departments
				//departments are used to assign an org. unit to installations
				//or dept(curricula) to teachers
				$departmentnodes = $document->getElementsByTagName( "department" );
				if(count($departmentnodes) > 0)
					foreach( $departmentnodes as $department )
					{				
						$deptid = "";
					    $deptid = trim($department->getAttribute("id"));
					    foreach($department->getElementsByTagName("longname") as $longname)
					    {
					    	$details = explode(',', $longname->textContent);
						    if(!count($details) > 0)
						    {
						    	$error = "Fehlende Angaben in Department $deptid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
				    			continue;
						    }
					    	$departments[$deptid]['school'] = trim($details [0]);
						    if(!isset($departments[$deptid]['school']))
						    {
						    	$error = "Fehlende Hochschuleangabe in Department $deptid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    	$departments[$deptid]['campus'] = trim($details [1]);
						    if(!isset($departments[$deptid]['campus']))
						    {
						    	$error = "Fehlende Campusangabe in Department $deptid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    	$departments[$deptid]['department'] = trim($details [2]);
						    if(!isset($departments[$deptid]['department']))
						    {
						    	$error = "Fehlende Departmentangabe in Department $deptid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    	$departments[$deptid]['curriculum'] = trim($details [3]);
					    }
						unset($longname);
					}
				else
				{
					$error = "S&auml;mtliche Departmentangaben fehlen.";
	    			if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($department, $departmentnodes);
				
				//collects data specific to time periods
				$timeperiodnodes = $document->getElementsByTagName( "timeperiod" );
				foreach( $timeperiodnodes as $timeperiod )
				{
					$tpid = "";
				    $tpid = trim($timeperiod->getAttribute("id"));
				    foreach($timeperiod->getElementsByTagName("day") as $eday)
				    {
				    	$day = trim($eday->textContent);
				    }
				    foreach($timeperiod->getElementsByTagName("period") as $eperiod)
				    {
				    	$period = trim($eperiod->textContent);
				    }
				    unset($eday, $eperiod);
				    $timeperiods[$day][$period] = $tpid;
				}
				unset($tpid, $timeperiod, $timeperiodnodes);

				//subjects are abstract guidelines for lessons
				//lessons implement subjects and carry their names
				$subjectnodes = $document->getElementsByTagName( "subject" );
				if(count($subjectnodes) > 0 )
					foreach( $subjectnodes as $subject )
					{
						$suid = "";
					    $suid = trim($subject->getAttribute("id"));
						$subjects[$suid]['id'] = $suid;
					    foreach($subject->getElementsByTagName("longname") as $longname)
					    {
					    	$subjects[$suid]['name'] = trim($longname->textContent);
					    }
					    if(!isset($subjects[$suid]['name']))
					    {
					    	$error = "Fehlende Angabe zum Subjectname in $suid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    //not yet supposed to be completely filled out
					    /*foreach($subject->getElementsByTagName("subjectgroup") as $sge)
					    {
					    	$subjects[$suid]['module'] = trim($sge->textContent);
					    }
					    if(!isset($subjects[$suid]['module']))
					    {
					    	$error = "Fehlende Angabe zum Subjectmodul in $suid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }*/
						unset($longname, $sge);
					}
				else
			    {
			    	$error = "S&auml;mtliche Subjects fehlen.";
	    			if(!in_array($error,$erray))$erray[] = $error;
			    }
				unset($subject, $subjectnodes);
								
				$teachernodes = $document->getElementsByTagName( "teacher" );
				if(count($teachernodes) > 0 )
					foreach( $teachernodes as $teacher )
					{
						$oid = $oname = "";
					    $oid = trim($teacher->getAttribute("id"));
						$teachers[$tid]['id'] = $oid;
					    foreach($teacher->getElementsByTagName("surname") as $surname)
					    {
					    	$oname = trim($surname->textContent);
					    }
					    if(!$oname)
					    {
					    	$error = "Fehlende Nachname zum Dozent $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    $payrollnumbers = $teacher->getElementsByTagName("payrollnumber");
					    if($payrollnumbers)
						    foreach($payrollnumbers as $prn)
						    {
						    	$manager = trim($prn->textContent);
						    }
					    if(!$manager)
					    {
					    	$error = "Kein Username eingetragen für Dozent $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    foreach($teacher->getElementsByTagName("teacher_department") as $td)
					    {
					    	$dept = trim($departments[$td->getAttribute("id")]['curriculum']);
					    }
					    if(!$dept)
					    {
					    	$error = "Fehlendes Department für Dozent $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
						unset($surname, $prn, $td);
				    	$teachers[$oid]['manager'] = $manager;
					}
				else
			    {
			    	$error = "S&auml;mtliche Teachers fehlen.";
	    			if(!in_array($error,$erray))$erray[] = $error;
			    }
				unset($teachernodes, $teacher);
				
				//classes are majors divided among their semesters
				//exceptions being other departments using rooms under the management of IT dept
				$classnodes = $document->getElementsByTagName( "class" );
				if($classnodes)
					foreach( $classnodes as $class )
					{
						$oid = $oname = $manager = $tid = "";
					    $oid = $class->getAttribute("id");
					    $oname = str_replace("CL_", "", $oid);
					    foreach($class->getElementsByTagName("longname") as $ln)
					    {
					    	$oalias = trim($ln->textContent);
					    	$parts = explode(',', $oalias);
						    if(!count($parts) > 0)
						    {
						    	$error = "Fehlende Department und Semester in Class $oid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
				    			continue;
						    }
					    	$department = trim($parts[0]);
						    if(!$department)
						    {
						    	$error = "Fehlendes Department in Class $oid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    	$semester = trim($parts[1]);
						    if(!$semester)
						    {
						    	$error = "Fehlender Semester in Class $oid.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    }
					    foreach($class->getElementsByTagName("class_teacher") as $ct)
					    {
					    	$tid = trim($ct->getAttribute('id'));
					    }
					    if(!$tid)
					    {
					    	$error = "Fehlende Verantwortliche in Class $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
						unset($ln, $ct);
						if($tid)
						{
						    $manager = $teachers[$tid]['manager'];
						    if(!$manager)
						    {
						    	$error = "Referenzierte Verantwortliche $tid in Class $oid existiert nicht.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
						}
					}
				else
				{
					$error = "S&auml;mtliche Classen fehlen.";
	    			if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($classnodes, $class);
				
				$roomnodes = $document->getElementsByTagName( "room" );
				if($roomnodes)
					foreach( $roomnodes as $room )
					{
						$oid = $oname = $oalias = $capacity = $typeid = $rtype = $deptid = $department = "";
					    $oid = trim($room->getAttribute("id"));
					    $rooms[$oid] = $oid;
					    $oname = str_replace("RM_","",$oid);
					    foreach($room->getElementsByTagName("longname") as $longname)
					    {
					    	$oalias = trim($longname->textContent);
					    }
					    if(!$oalias)
					    {
					    	$error = "Fehlende Longname in Room $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    foreach($room->getElementsByTagName("capacity") as $cap)
					    {
					    	$capacity = trim($cap->textContent);
					    }
					    if(!$oalias)
					    {
					    	$error = "Fehlende Longname in Room $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    foreach($room->getElementsByTagName("room_description") as $rdesc)
					    {
					    	$typeid = trim($rdesc->getAttribute("id"));
						    $rtype = $descriptions[$typeid];
						    if(!$rtype)
						    {
						    	$error = "Referenzierter Raumtyp $typeid in Room $oid existiert nicht.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    }
					    if(!$rtype)
					    {
					    	$error = "Fehlender Raumtyp in Room $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    foreach($room->getElementsByTagName("room_department") as $rdept)
					    {
					    	$deptid = trim($rdept->getAttribute("id"));
					    	$department = $departments[$deptid]['department'];
						    if(!$department)
						    {
						    	$error = "Referenzierter Department $deptid in Room $oid existiert nicht.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    }
					    if(!$department)
					    {
					    	$error = "Fehlendes Department in $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    unset($longname, $cap, $rdesc, $rdept);
					}
				else
				{
					$error = "S&auml;mtliche R&auml;ume fehlen.";
	    			if(!in_array($error,$erray))$erray[] = $error;
				}
				unset($roomnodes, $room);
				
				$lessonnodes = $document->getElementsByTagName( "lesson" );
				foreach( $lessonnodes as $lesson )
				{
					$oid = $suid = $lessontype = $oname = $oalias = null;
					$oid = substr($lesson->getAttribute("id"), 0, strlen($lesson->getAttribute("id")) - 2);
				    foreach($lesson->getElementsByTagName("lesson_subject") as $subjectnl)
				    {
				    	$suid= $subjectnl->getAttribute("id");
				    	if(!isset($subjects[$suid]))
				    	{
					    	$error = "Referentzierte Subject ID $suid in Lesson $oid existiert nicht.";
			    			if(!in_array($error,$erray))$erray[] = $error;
				    	}
				    }
				    unset($subjectnl);
				    if(!$suid)
				    {
				    	$error = "Fehlendes Subject in $oid.";
		    			if(!in_array($error,$erray))$erray[] = $error;
				    }
					foreach($lesson->getElementsByTagName("text1") as $t1)
				    {
				    	$lessontype = $t1->textContent;
				    }
				    unset($t1);
				    if(!$lessontype)
				    {
				    	$error = "Fehlende Lessontype in $oid.";
		    			if(!in_array($error,$erray))$erray[] = $error;
				    }					
					if(!isset($lessoncount[$oid])) $lessoncount[$oid] = 0;
					else $lessoncount[$oid] = $lessoncount[$oid] + 1;
					foreach($lesson->getElementsByTagName("lesson_classes") as $classesnl)
				    {
				    	$classids = $classesnl->getAttribute("id");
					    $tempclassidarray = explode(" ", $classids);
					    foreach($tempclassidarray as $tempclassid)
					    {
					    	if($lessoncount[$oid] > 0)
					    	{
					    		if(count($lessons[$oid]['classes']) != count($tempclassidarray))
					    		{
					    			$error = "Inkonsistente Klassen in $oid.";
					    			if(!in_array($error,$erray))$erray[] = $error;
					    		}
					    		if(!$lessons[$oid]['classes'][$tempclassid])
					    		{
					    			$lessons[$oid]['classes'][$tempclassid] = $tempclassid;
					    			$error = "Inkonsistente Klassen in $oid.";
					    			if(!in_array($error,$erray))$erray[] = $error;
					    		}
					    	}
					    	else
					    		$lessons[$oid]['classes'][$tempclassid] = $tempclassid;
					    }
				    }
				    unset($classesnl);
				    if(!count($lessons[$oid]['classes']) > 0)
			    	{
				    	$error = "S&auml;mtliche Classes fehlen in Lesson $oid.";
		    			if(!in_array($error,$erray))$erray[] = $error;
			    	}
					foreach($lesson->getElementsByTagName("lesson_teacher") as $teachernl)
				    {
				    	$tid = $teachernl->getAttribute("id");
				    	if(!isset($teachers[$tid]))
				    	{
					    	$error = "Referenzierter Dozent $tid in Lesson $oid existiert nicht.";
			    			if(!in_array($error,$erray))$erray[] = $error;
				    	}
				    }
				    unset($teachernl);
					if(!$tid)
			    	{
				    	$error = "Fehlender Dozent in $oid.";
		    			if(!in_array($error,$erray))$erray[] = $error;
			    	}
					foreach($lesson->getElementsByTagName("time") as $time)
				    {
						foreach($time->getElementsByTagName("assigned_room") as $roomnl)
					    {
					    	$rid = $roomnl->getAttribute("id");
						    if(!isset($rooms[$rid]))
						    {
						    	$error = "Referenzierter Raum $rid in Lesson $oid existiert nicht.";
				    			if(!in_array($error,$erray))$erray[] = $error;
						    }
					    }
					    if(!$rid)
					    {
					    	$error = "Fehlender Raum in $oid.";
			    			if(!in_array($error,$erray))$erray[] = $error;
					    }
					    unset($rid, $day, $period, $tpid);
					    unset($lid);
				    }
				    unset($time);
				}
				unset($lesson, $lessonnodes, $dDoc);
								
				$content = addslashes($content);
				$query = "INSERT INTO #__thm_organizer_schedules 
							(filename, file, includedate, creationdate, startdate, enddate, sid)
						  VALUES ('$fileName', '$content', '$date','$creationdate', '$startdate', '$enddate', '$sid')";
				$dbo->setQuery( $query );
				$dbo->query();	
				if ($dbo->getErrorNum())
				{
					$this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error'  );
				}
				else if(count($erray) > 0)
				{
					$errorstring = "<br />".implode("<br />", $erray)."<br />";
					$this->setRedirect($link, 
										JText::_('Ihre Datei wurde erfolgreich hochgeladen.<br /> Die Datei erweist die folgenden Fehler:').$errorstring,
										'notice'  );
				}
				else
					$this->setRedirect($link, JText::_('Ihre Datei wurde erfolgreich hochgeladen')."."  );
			}
			else
			{
				$this->setRedirect($link, JText::_('Ihre Datei ist von einem unzugelassenen Typ')."."  );
			}
    	}
        else
        {
			$this->setRedirect(JRoute::_('index.php'), JText::_("Zugriff verweigert").".", 'error'  );
        }
    }
    
	/**
	 * Changes the active schedule, and calculates the difference to the last active schedule.
	 */
	function schedule_publish()
	{
		$dbo = & JFactory::getDBO();
    	$user =& JFactory::getUser();
    	$dumpname = print_r($user, true);
		$sid = JRequest::getVar('semesterid');
		$badboylink = JRoute::_('index.php');
		$link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
		$query = "SELECT author FROM #__thm_organizer_semester WHERE sid = '$sid'";
		$dbo->setQuery( $query );
		$username = $dbo->loadResult();
		if($user->username != $username) $this->setRedirect($badboylink, "Zugriff verweigert", 'error' );
		else
        {   			  	
			$id = JRequest::getVar('schedule_id');
			
			//load the schedule to be activated from the file in the database
			$query = "SELECT file, filename FROM #__thm_organizer_schedules WHERE id = '$id'";
			$dbo->setQuery( $query );
			$result = $dbo->query();
			if ($dbo->getErrorNum())
			{
					$this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error'  );
			}
			list($file, $to) = mysql_fetch_array($result); 
			
			//create php structures from the xml structures
			if($file)
			{
				$query = "SELECT filename
						  FROM #__thm_organizer_schedules 
						  WHERE active IS NOT NULL
						  	AND sid = '$sid'";			
				$dbo->setQuery( $query );
				$from = $dbo->loadResult();
				if($from)
				{
					
					$oldlessons = array();
					$query = "SELECT l.lid, o.oname AS name, o.oalias AS description
							  FROM #__thm_organizer_lessons AS l
							  	INNER JOIN #__thm_organizer_objects AS o
							  		ON l.lid = o.oid
							  WHERE l.sid = '$sid'
							  	AND o.sid = '$sid'";
					$dbo->setQuery($query);
					$lids = $dbo->loadAssocList();
					foreach($lids as $lk => $lv)
					{
						$oldlessons[$lv['lid']]['name'] = $lv['name'];
						$oldlessons[$lv['lid']]['desc'] = $lv['description'];
						//classes are independant of the implementing periods
						$query = "SELECT cid
							  FROM #__thm_organizer_lessons
							  WHERE lid = '".$lv['lid']."'
							  	AND sid = '$sid'";
						$dbo->setQuery($query);
						$cids = $dbo->loadAssocList();
						foreach($cids as $ck => $cv)
						{
							$oldlessons[$lv['lid']]['classes'][$cv['cid']] = $cv['cid'];
						}
						//teachers and rooms are dependant on the implementing periods
						//timeperiod data is checked here because is otherwise impossible to display time changes
						//if the timeperiod stays the same
						$query = "SELECT lp.rid, lp.tid, tp.tpid, tp.day,
									tp.period, tp.starttime, tp.endtime
								  FROM #__thm_organizer_lessonperiods AS lp
								  	INNER JOIN #__thm_organizer_timeperiods as tp
								  		ON lp.tpid = tp.tpid
								  WHERE lid = '".$lv['lid']."'
								  	AND lp.sid = '$sid'
								  	AND tp.sid = '$sid'";
						$dbo->setQuery($query);
						$lpdata = $dbo->loadAssocList();
						foreach($lpdata as $lpdk => $lpdv)
						{
							if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['day'])
								$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['day'] = $lpdv['day'];
							if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['period'])
								$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['period'] = $lpdv['period'];
							if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['startime'])
								$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['starttime'] = $lpdv['starttime'];
							if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['endtime'])
								$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['endtime'] = $lpdv['endtime'];
							if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['rooms'][$lpdv['rid']])
								$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['rooms'][$lpdv['rid']] = $lpdv['rid'];
							if(!$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['teachers'][$lpdv['tid']])
								$oldlessons[$lv['lid']]['periods'][$lpdv['tpid']]['teachers'][$lpdv['tid']] = $lpdv['tid'];
							
						}
					}
					//$dptdump = print_r($oldlessons, true);
					
					//remove active data
					$query = "DELETE FROM #__thm_organizer_objects WHERE sid = '$sid';";
					$dbo->setQuery($query);
					$dbo->query();
					$query = "DELETE FROM #__thm_organizer_lessons WHERE sid = '$sid';";
					$dbo->setQuery($query);
					$dbo->query();
					$query = "DELETE FROM #__thm_organizer_lessonperiods WHERE sid = '$sid';";
					$dbo->setQuery($query);
					$dbo->query();
					$query = "DELETE FROM #__thm_organizer_timeperiods WHERE sid = '$sid';";
					$dbo->setQuery($query);
					$dbo->query();
				}
				
				//arrays to contain the lists of resources from the schedule
				$descriptions = array(); $departments = array();
				$timeperiods = array(); $classes = array();
				$lessonclasses = array(); $subjects = array();
				$subjectobjects = array(); $teachers = array();
				$lessons = array(); $rooms = array();

				$dDoc = new DOMDocument();
				$dDoc->loadXML($file);
				$document = $dDoc->documentElement;
				
				//descriptions are used to "type" a room
				$descriptionnodes = $document->getElementsByTagName( "description" );
				if($descriptionnodes)
					foreach( $descriptionnodes as $description )
					{
					    $descid = trim($description->getAttribute("id"));
					    foreach($description->getElementsByTagName("longname") as $longname)
					    {
					    	$desc = trim($longname->textContent);
					    }
					    $descriptions[$descid] = $desc;
					    unset( $longname);
					}
				unset($description, $descriptionnodes);

				//collects data specific to departments
				//departments are used to assign an org. unit to installations
				//or dept(curricula) to teachers
				$departmentnodes = $document->getElementsByTagName( "department" );
				if(count($departmentnodes) > 0)
					foreach( $departmentnodes as $department )
					{
					    $deptid = trim($department->getAttribute("id"));
					    foreach($department->getElementsByTagName("longname") as $longname)
					    {
					    	$details = explode(',', $longname->textContent);
					    	$departments[$deptid]['school'] = trim($details [0]);
					    	$departments[$deptid]['campus'] = trim($details [1]);
					    	$departments[$deptid]['department'] = trim($details [2]);
					    	$departments[$deptid]['curriculum'] = trim($details [3]);
					    }
						unset($longname);
					}
				unset($department, $departmentnodes);
				
				//collects data specific to time periods
				$timeperiodnodes = $document->getElementsByTagName( "timeperiod" );
				foreach( $timeperiodnodes as $timeperiod )
				{
				    $tpid = trim($timeperiod->getAttribute("id"));
				    foreach($timeperiod->getElementsByTagName("day") as $eday)
				    {
				    	$day = trim($eday->textContent);
				    }
				    foreach($timeperiod->getElementsByTagName("period") as $eperiod)
				    {
				    	$period = trim($eperiod->textContent);
				    }
				    foreach($timeperiod->getElementsByTagName("starttime") as $estarttime)
				    {
				    	$tstarttime = trim($estarttime->textContent);
				    	$starttime = substr($tstarttime, 0, 2).":".substr($tstarttime, 2, 2).":00";
				    }
				    foreach($timeperiod->getElementsByTagName("endtime") as $eendtime)
				    {
				    	$tendtime = trim($eendtime->textContent);
				    	$endtime = substr($tendtime, 0, 2).":".substr($tendtime, 2, 2).":00";
				    }
				    unset($eday, $eperiod, $estarttime, $eendtime);
				    $starttime = $starttime;
				    $endtime = $endtime;
				    $timeperiods[$day][$period] = $tpid;
				    $timeperiods[$tpid]['tpid']= $tpid;
				    $timeperiods[$tpid]['day']= $day;
				    $timeperiods[$tpid]['period']= $period;
				    $timeperiods[$tpid]['starttime']= $starttime;
				    $timeperiods[$tpid]['endtime']= $endtime;
				    
				    $query = "INSERT IGNORE INTO #__thm_organizer_timeperiods 
				    								(tpid, day, period, starttime, endtime, sid)
				    							 VALUES('$tpid', '$day', '$period', '$starttime', '$endtime', '$sid');";
				    $dbo->setQuery($query);
				    $dbo->query();
				}
				unset($tpid, $timeperiod, $timeperiodnodes);

				//subjects are abstract guidelines for lessons
				//lessons implement subjects and carry their names
				$subjectnodes = $document->getElementsByTagName( "subject" );
				foreach( $subjectnodes as $subject )
				{
				    $suid = trim($subject->getAttribute("id"));
					$subjects[$suid]['id'] = $suid;
				    foreach($subject->getElementsByTagName("longname") as $longname)
				    {
				    	$subjects[$suid]['name'] = trim($longname->textContent);
				    }
				    foreach($subject->getElementsByTagName("subjectgroup") as $sge)
				    {
				    	$subjects[$suid]['module'] = trim($sge->textContent);
				    }
					unset($longname, $sge);
				}
				unset($subject, $subjectnodes);
								
				$teachernodes = $document->getElementsByTagName( "teacher" );
				foreach( $teachernodes as $teacher )
				{
				    $oid = trim($teacher->getAttribute("id"));
					$teachers[$tid]['id'] = $oid;
				    foreach($teacher->getElementsByTagName("surname") as $surname)
				    {
				    	$oname = trim($surname->textContent);
				    }
				    $payrollnumbers = $teacher->getElementsByTagName("payrollnumber");
				    if($payrollnumbers)
					    foreach($payrollnumbers as $prn)
					    {
					    	$manager = trim($prn->textContent);
					    }
				    foreach($teacher->getElementsByTagName("teacher_department") as $td)
				    {
				    	$dept = trim($departments[$td->getAttribute("id")]['curriculum']);
				    }
					unset($surname, $prn, $td);
			    	$teachers[$oid]['oid'] = $oid;
			    	$teachers[$oid]['oname'] = $oname;
			    	$teachers[$oid]['manager'] = $prno;
			    	$teachers[$oid]['department'] = $dept;
				    $query = "INSERT IGNORE INTO #__thm_organizer_objects 
				    			(oid, oname, otype, manager,  sid)
				    		  VALUES('$oid', '$oname', 'teacher', '$manager', '0');";
				    $dbo->setQuery($query);
				    $dbo->query();
				    $query = "INSERT IGNORE INTO #__thm_organizer_teachers 
				    			(tid, department)
				    		  VALUES('$oid', '$dept');";
				    $dbo->setQuery($query);
				    $lastquery;
				    $dbo->query();
				}
				unset($subject, $subjectnodes);
				
				//classes are majors divided among their semesters
				//exceptions being other departments using rooms under the management of IT dept
				$classnodes = $document->getElementsByTagName( "class" );
				foreach( $classnodes as $class )
				{
				    $oid = $class->getAttribute("id");
				    $oname = str_replace("CL_", "", $oid);
				    foreach($class->getElementsByTagName("longname") as $ln)
				    {
				    	$oalias = trim($ln->textContent);
				    	$parts = explode(',', $oalias);
				    	$department = trim($parts[0]);
				    	$semester = trim($parts[1]);
				    }
				    foreach($class->getElementsByTagName("class_teacher") as $ct)
				    {
				    	$tid = trim($ct->textContenttrim);
				    }
					unset($ln, $ct);
				    $classes[$oid]['oid'] = $oid;
				    $classes[$oid]['oname'] = $oname;
				    $classes[$oid]['oalias'] = $oalias;
				    $manager = $teachers[$tid]['manager'];
				    $classes[$oid]['manager'] = $manager;
				    $classes[$oid]['department'] = $department;
				    $classes[$oid]['semester'] = $semester;
				    $query = "INSERT IGNORE INTO #__thm_organizer_objects
				    			(oid, oname, oalias, otype, manager, sid)
			    			  VALUES('$oid', '$oname', '$oalias', 'class', '$manager', '0');";
				    $dbo->setQuery($query);
				    $dbo->query();
				    $query = "INSERT IGNORE INTO #__thm_organizer_classes
				    			(cid, department, semester)
			    			  VALUES('$oid', '$department', '$semester');";
				    $dbo->setQuery($query);
				    $dbo->query();
				}
				unset($classnodes, $class);
				
				$roomnodes = $document->getElementsByTagName( "room" );
				foreach( $roomnodes as $room )
				{
				    $oid = trim($room->getAttribute("id"));
				    $oname = str_replace("RM_","",$oid);
				    foreach($room->getElementsByTagName("longname") as $longname)
				    {
				    	$oalias = trim($longname->textContent);
				    }
				    foreach($room->getElementsByTagName("capacity") as $cap)
				    {
				    	$capacity = trim($cap->textContent);
				    }
				    foreach($room->getElementsByTagName("room_description") as $rdesc)
				    {
				    	$rtype = $descriptions[trim($rdesc->getAttribute("id"))];
				    }
				    foreach($room->getElementsByTagName("room_department") as $rdept)
				    {
				    	$department = $departments[trim($rdept->getAttribute("id"))]['department'];
				    }
				    unset($longname, $cap, $rdesc, $rdept);
				    $rooms[$oid]['oid'] = $oid;
				    $rooms[$oid]['oname'] = $oname;
				    $rooms[$oid]['oalias'] = $oalias;
				    $rooms[$oid]['capacity'] = $capacity;
				    $rooms[$oid]['rtype'] = $rtype;
				    $rooms[$oid]['department'] = $department;
				    $query = "INSERT IGNORE INTO #__thm_organizer_objects
				    			(oid, oname, otype, oalias, sid)
				    		  VALUES('$oid', '$oname', 'room', '$oalias', '0');";
				    $dbo->setQuery($query);
				    $dbo->query();
				    $query = "INSERT IGNORE INTO #__thm_organizer_rooms
				    			(rid, capacity, rtype, department)
				    		  VALUES('$oid', '$capacity', '$rtype', '$department');";
				    $dbo->setQuery($query);
				    $dbo->query();
				}
				unset($roomnodes, $room);
				
				$lessonnodes = $document->getElementsByTagName( "lesson" );
				foreach( $lessonnodes as $lesson )
				{
					$oid = $suid = $lessontype = $oname = $oalias = null;
					$oid = substr($lesson->getAttribute("id"), 0, strlen($lesson->getAttribute("id")) - 2);
					$lessons[$oid]['oldid'] = $oldid;
				    foreach($lesson->getElementsByTagName("lesson_subject") as $subjectnl)
				    {
				    	$suid= $subjectnl->getAttribute("id");
				    }
				    unset($subjectnl);
				    $lessons[$oid]['subjectid'] = $suid;
			    	$oname = $subjects[$suid]['name'];
					foreach($lesson->getElementsByTagName("text1") as $t1)
				    {
				    	$lessontype = $t1->textContent;
				    }
				    unset($t1);
				    if($lessontype != "V")//V(Vorlesung) does not need to be specially identified in the name
				    	$oname = $oname."-".$lessontype;
			    	$lessons[$oid]['name'] = $oname;
			    	$oalias = $subjects[$suid]['module'];
			    	if(!$oalias) $oalias = "";
			    	$lessons[$oid]['desc'] = $oalias;
			    	
			    	//details common to all resources
					$query = "INSERT INTO #__thm_organizer_objects (oid, oname, oalias, otype, sid)
					    			VALUES('$oid', '$oname', '$oalias', 'lesson', '$sid');";
					$dbo->setQuery( $query );
					$dbo->query();
					
					if(!isset($lessoncount[$oid])) $lessoncount[$oid] = 0;
					else $lessoncount[$oid] = $lessoncount[$oid] + 1;
					//details specific to a lesson
					foreach($lesson->getElementsByTagName("lesson_classes") as $classesnl)
				    {
				    	$classids = $classesnl->getAttribute("id");
					    $tempclassidarray = explode(" ", $classids);
					    foreach($tempclassidarray as $tempclassid)
					    {
					    	$lessons[$oid]['classes'][$tempclassid] = $tempclassid;
					    }
				    }
				    unset($classesnl);
				    foreach($lessons[$oid]['classes'] as $classid)
		    		{
		    			$query = "INSERT INTO #__thm_organizer_lessons (lid, cid, ltype, sid)
			    					VALUES('$oid', '$classid', '$lessontype', '$sid');";
						$dbo->setQuery( $query );
						$dbo->query();
		    		}
					//details specific to a lesson period
					foreach($lesson->getElementsByTagName("lesson_teacher") as $teachernl)
				    {
				    	$tid = $teachernl->getAttribute("id");
				    }
				    unset($teachernl);
					foreach($lesson->getElementsByTagName("time") as $time)
				    {
						foreach($time->getElementsByTagName("assigned_day") as $daynl)
					    {
					    	$day = $daynl->textContent;
					    }
						foreach($time->getElementsByTagName("assigned_period") as $periodnl)
					    {
					    	$period = $periodnl->textContent;
					    }
						foreach($time->getElementsByTagName("assigned_room") as $roomnl)
					    {
					    	$rid = $roomnl->getAttribute("id");
					    }
					    $tpid = $timeperiods[$day][$period];
					    $lessons[$oid]['periods'][$tpid]['day'] = $day;
					    $lessons[$oid]['periods'][$tpid]['period'] = $period;
					    $lessons[$oid]['periods'][$tpid]['starttime'] = $timeperiods[$tpid]['starttime'];
					    $lessons[$oid]['periods'][$tpid]['endtime'] = $timeperiods[$tpid]['endtime'];
					    $lessons[$oid]['periods'][$tpid]['teachers'][$tid] = $tid;
					    $lessons[$oid]['periods'][$tpid]['rooms'][$rid] = $rid;
					    $query = "INSERT INTO #__thm_organizer_lessonperiods (lid, rid, tpid, tid, sid)
						    			VALUES('$oid', '$rid', '$tpid', '$tid', '$sid');";
						$dbo->setQuery( $query );
						$dbo->query();
					    unset($rid, $day, $period, $tpid);
					    unset($lid);
				    }
				    unset($time);
				}
				unset($lesson, $lessonnodes, $dDoc);
			}
			
			//build the delta
			if($from)
			{
				$type = "cyclic";
				$predelta = array();
				$lessonsdelta = array();//holds a running count of movedto/movedfrom lessons
				foreach($lessons as $lessonkey => $lessonvalue)
				{
					//if a lesson does not exist in the old plan than every period is new
					if(!array_key_exists($lessonkey, $oldlessons))
					{
						foreach($lessons[$lessonkey]['periods'] as $lpkey => $lpvalue)
						{
							$key = $lessonkey." ".$lpkey;
							$predelta[$key]['type'] = $type;
							$predelta[$key]['dow'] = $lessons[$lessonkey]['periods'][$lpkey]['day'];
							$predelta[$key]['block'] = $lessons[$lessonkey]['periods'][$lpkey]['period'];
							$predelta[$key]['clas'] = implode(" ", $lessons[$lessonkey]['classes']);
							$predelta[$key]['room'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['rooms']);
							$predelta[$key]['doz'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['teachers']);
							$predelta[$key]['key'] = $lessonkey." ".$lpkey;					
							$predelta[$key]['name'] = $lessons[$lessonkey]['name'];
							$predelta[$key]['desc'] = $lessons[$lessonkey]['desc'];
							$predelta[$key]['css'] = 'new';
							unset($key);
						}
					}
					//the lesson exists in both plans
					else
					{
						$css = "";
						$changes = array();
						foreach($lessons[$lessonkey]['classes'] as $ck => $cv)
						{
							//classes new to the lesson
							if(!array_key_exists($ck, $oldlessons[$lessonkey]['classes']))
							{
								if(!$changes['classes'][$ck])$changes['classes'][$ck] = "new";
								$css = "changed";
							}
						}
						foreach($oldlessons[$lessonkey]['classes'] as $ck => $cv)
						{
							//classes removed from the lesson
							if(!array_key_exists($ck, $lessons[$lessonkey]['classes']))
							{
								if(!$changes['classes'][$ck])$changes['classes'][$ck] = "removed";
								$css = "changed";
							}
						}
						foreach($lessons[$lessonkey]['periods'] as $lpkey => $lpvalue)
						{
							//if the time period does not exist, but the lesson does
							//than a period in the old plan was moved here
							if(!array_key_exists($lpkey, $oldlessons[$lessonkey]['periods']))
							{
								$key = $lessonkey." ".$lpkey;
								$predelta[$key]['type'] = $type;
								$predelta[$key]['dow'] = $lessons[$lessonkey]['periods'][$lpkey]['day'];
								$predelta[$key]['block'] = $lessons[$lessonkey]['periods'][$lpkey]['period'];
								$predelta[$key]['clas'] = implode(" ", $lessons[$lessonkey]['classes']);
								$predelta[$key]['room'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['rooms']);
								$predelta[$key]['doz'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['teachers']);
								$predelta[$key]['key'] = $key ;					
								$predelta[$key]['name'] = $lessons[$lessonkey]['name'];
								$predelta[$key]['desc'] = $lessons[$lessonkey]['desc'];
								if($css == "changed")
								{
									$predelta[$key]['changes'] = $changes;
									$predelta[$key]['css'] = 'movedto '.$css;
								}
								else $predelta[$key]['css'] = 'movedto';
								if($lessonsdelta[$lessonkey]['movedto'])
									$lessonsdelta[$lessonkey]['movedto'] = $lessonsdelta[$lessonkey]['movedto'] + 1;
								else $lessonsdelta[$lessonkey]['movedto'] = 1;
								unset($key);
							}
							//check if the data represented by the time period has changed
							//i.e. the timeperiod are the same, but the day, block, etc. have changed
							else
							{
								$oldperiod = $oldlessons[$lessonkey]['periods'][$lpkey];
								if($oldperiod['starttime'] != $lpvalue['starttime'])
								{
									$css = "changed";
									$changes['starttime'] = $oldperiod['starttime']." => ".$lpvalue['starttime'];
								}
								if($oldperiod['endtime'] != $lpvalue['endtime'])
								{
									$css = "changed";
									$changes['endtime'] = $oldperiod['endtime']." => ".$lpvalue['endtime'];
								}
								foreach($lessons[$lessonkey]['periods'][$lpkey]['teachers'] as $lptkey => $lptvalue)
								{
									if(!array_key_exists($lptkey, $oldlessons[$lessonkey]['periods'][$lpkey]['teachers']))
									{
										$css = "changed";
										$changes['teachers'][$lptkey] = "new";
									}
									else
										unset($oldlessons[$lessonkey]['periods'][$lpkey]['teachers'][$lptkey]);
								}
								if(count($oldlessons[$lessonkey]['periods'][$lpkey]['teachers']) > 0)
									foreach($oldlessons[$lessonkey]['periods'][$lpkey]['teachers'] as $optkey => $optvalue)
									{
										$css = "changed";
										$changes['teachers'][$optkey] = "removed";
									}
								foreach($lessons[$lessonkey]['periods'][$lpkey]['rooms'] as $lprkey => $lprvalue)
								{
									if(!array_key_exists($lprkey, $oldlessons[$lessonkey]['periods'][$lpkey]['rooms']))
									{
										$css = "changed";
										$changes['rooms'][$lprkey] = "new";
									}
									else
										unset($oldlessons[$lessonkey]['periods'][$lpkey]['rooms'][$lprkey]);
								}
								if(count($oldlessons[$lessonkey]['periods'][$lpkey]['rooms']) > 0)
									foreach($oldlessons[$lessonkey]['periods'][$lpkey]['rooms'] as $oprkey => $oprvalue)
									{
										$css = "changed";
										$changes['rooms'][$oprkey] = "removed";
									}
								if($css == "changed")
								{
									$key = $lessonkey." ".$lpkey;
									$predelta[$key]['type'] = $type;
									$predelta[$key]['dow'] = $lessons[$lessonkey]['periods'][$lpkey]['day'];
									$predelta[$key]['block'] = $lessons[$lessonkey]['periods'][$lpkey]['period'];
									$predelta[$key]['clas'] = implode(" ", $lessons[$lessonkey]['classes']);
									$predelta[$key]['room'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['rooms']);
									$predelta[$key]['doz'] = implode(" ", $lessons[$lessonkey]['periods'][$lpkey]['teachers']);
									$predelta[$key]['key'] = $key;					
									$predelta[$key]['name'] = $lessons[$lessonkey]['name'];
									$predelta[$key]['desc'] = $lessons[$lessonkey]['desc'];
									$predelta[$key]['css'] = 'changed';
									$predelta[$key]['changes'] = $changes;
									unset($key);
								}
								unset($oldlessons[$lessonkey]['periods'][$lpkey]);
							}
						}
						//periods that were not in the new plan have been moved
						if(count($oldlessons[$lessonkey]['periods']) > 0)
						{
							foreach($oldlessons[$lessonkey]['periods'] as $lpkey => $lpvalue)
							{
								$key = $lessonkey." ".$lpkey;
								$predelta[$key]['type'] = $type;
								$predelta[$key]['dow'] = $oldlessons[$lessonkey]['periods'][$lpkey]['day'];
								$predelta[$key]['block'] = $oldlessons[$lessonkey]['periods'][$lpkey]['period'];
								$predelta[$key]['clas'] = implode(" ", $oldlessons[$lessonkey]['classes']);
								$predelta[$key]['room'] = implode(" ", $oldlessons[$lessonkey]['periods'][$lpkey]['rooms']);
								$predelta[$key]['doz'] = implode(" ", $oldlessons[$lessonkey]['periods'][$lpkey]['teachers']);
								$predelta[$key]['key'] = $key;					
								$predelta[$key]['name'] = $oldlessons[$lessonkey]['name'];
								$predelta[$key]['desc'] = $oldlessons[$lessonkey]['desc'];
								$predelta[$key]['css'] = 'movedfrom';
								if($lessonsdelta[$lessonkey]['movedfrom'])
									$lessonsdelta[$lessonkey]['movedfrom'] = $lessonsdelta[$lessonkey]['movedfrom'] + 1;
								else $lessonsdelta[$lessonkey]['movedfrom'] = 1;
								unset($key);
							}
						}
						unset($oldlessons[$lessonkey], $changes);
					}
				}
				unset($lessons);
				if(count($oldlessons) > 0)
				{
					foreach($oldlessons as $olk => $olv)
					{
						foreach($olv['periods'] as $olpkey => $olpvalue)
						{
							$key = $olk." ".$olpkey;
							$predelta[$key]['type'] = $type;
							$predelta[$key]['dow'] = $oldlessons[$olk]['periods'][$olpkey]['day'];
							$predelta[$key]['block'] = $oldlessons[$olk]['periods'][$olpkey]['period'];
							$predelta[$key]['clas'] = implode(" ", $oldlessons[$olk]['classes']);
							$predelta[$key]['room'] = implode(" ", $oldlessons[$olk]['periods'][$olpkey]['rooms']);
							$predelta[$key]['doz'] = implode(" ", $oldlessons[$olk]['periods'][$olpkey]['teachers']);
							$predelta[$key]['key'] = $olk." ".$olpkey;					
							$predelta[$key]['name'] = $oldlessons[$olk]['name'];
							$predelta[$key]['desc'] = $oldlessons[$olk]['desc'];
							$predelta[$key]['css'] = 'removed';
							unset($key);
						}
					}
				}
			}
			
			//sometimes a lesson block is cancelled or added which leads to uneven numbers of movedfrom and movedto
			$discrepancies = array();
			foreach($lessonsdelta as $ldk => $ldv)
			{
				if(!$ldv['movedto'])$ldv['movedto'] = 0;
				if(!$ldv['movedfrom'])$lkv['movedfrom'] = 0;
				if($ldv['movedto'] > $ldv['movedfrom'])
				{
					$count = $ldv['movedto'] - $ldv['movedfrom'];
					$discrepancies[$ldk]['count'] = $count;
					$discrepancies[$ldk]['delta'] = 'movedto';
				}
				else if($ldv['movedto'] < $ldv['movedfrom'])
				{
					$count = $ldv['movedfrom'] - $ldv['movedto'];
					$discrepancies[$ldk]['count'] = $count;
					$discrepancies[$ldk]['delta'] = 'movedfrom';
				}
			}
			unset($lessonsdelta);
			foreach($discrepancies as $lessonkey => $lessondisc)
			{
				$ld = $lessondisc['delta'];
				foreach($predelta as $dk => $dv)
				{
					$keyparts = explode(' ', $dk);
					if($keyparts[0] == $lessonkey)
					{
						if($lessondisc['count'] > 0)
						{
							if($dv['css'] == ($ld == 'movedto'))
							{
								$predelta[$dk]['css'] = 'new';
								$lessondisc['count'] = $lessondisc['count'] - 1;
							}
							if($dv['css'] == ($ld == 'movedfrom'))
							{
								$predelta[$dk]['css'] = 'removed';
								$lessondisc['count'] = $lessondisc['count'] - 1;
							}
						}
					}
				}
			}
			$index = 0;
			$delta = array();
			foreach($predelta as $pdk => $pdv)
			{
				$delta[$index] = $predelta[$pdk];
				$index++;
			}
			
			
			//json_encode does not handle umlaute properly
			$malformedjsondelta = json_encode($delta);
			$jsondelta = str_replace('\u00d6', 'Ö',
							str_replace('\u00f6', 'ö',
							str_replace('\u00c4', 'Ä',
							str_replace('\u00e4', 'ä',
							str_replace('\u00dc', 'Ü',
							str_replace('\u00fc', 'ü',
							str_replace('\u00df', 'ß', $malformedjsondelta)))))));
			
			//deletes old delta
			$query = "DELETE FROM #__thm_organizer_user_schedules WHERE username = 'delta'";
			$dbo->setQuery( $query );
			$dbo->query();
					
			//inserts new delta
			$currenttime = time();
			$query = "INSERT INTO #__thm_organizer_user_schedules (username, data, created)
			VALUES ('delta', '$jsondelta', '$currenttime')";
			$dbo->setQuery( $query );
			$dbo->query();	
			if ($dbo->getErrorNum())
			{
				$this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error'  );
			}
					
			//save the most recent module catalogue
			//$this->buildModules($subjects);

			//set old active to false
			$query = "UPDATE #__thm_organizer_schedules SET active = NULL WHERE sid = '$sid';";
			$dbo->setQuery( $query );
			$dbo->query();	

			$currentdate = date('Y-m-d H:i:s');
			//set new active to true
			$query = "UPDATE #__thm_organizer_schedules SET active = '$currentdate' WHERE id = '$id'";
			$dbo->setQuery( $query );
			$dbo->query();
			$link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
			$dump = print_r($dptdump, true);
			if ($dbo->getErrorNum())
			{
					$this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error' );
			}
			else
			{
				if($from)
				{
					$this->setRedirect($link, $return.JText::_('Der aktueller Stundenplan wurde ge&auml;ndert von ').$from.JText::_(' auf ').$to.JText::_(" ge&auml;ndert.") );
				}
			    else $this->setRedirect($link, $return.JText::_('Der aktueller Stundenplan wurde auf ').$to.JText::_(" gesetzt." ));
			}
        }
	}
	
	/**
	 * Inactivates the active schedule, removes the delta, and schedule specific data from the DB.
	 */
	function schedule_unpublish()
	{
    	$dbo = & JFactory::getDBO();
    	$user =& JFactory::getUser();
        if($user->gid >= 24) $access = true;
        else $access = false ;
        if($access)
        {
			//establish db object
			$dbo = & JFactory::getDBO();
			
			$sid = JRequest::getVar('semesterid');
			$link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
			
			//set active to false
			$query = "UPDATE #__thm_organizer_schedules SET active = NULL WHERE active IS NOT NULL AND sid = '$sid';";
			$dbo->setQuery( $query );
			$dbo->query();
			
			$query = "DELETE FROM #__thm_organizer_user_schedules WHERE username = 'delta' AND sid = '$sid';";
			$dbo->setQuery( $query );
			$dbo->query();//no error check there may be no delta in the db
			
			
			//remove active data
			$query = "DELETE FROM #__thm_organizer_objects WHERE sid = '$sid';";
			$dbo->setQuery($query);
			$dbo->query();
			$query = "DELETE FROM #__thm_organizer_lessons WHERE sid = '$sid';";
			$dbo->setQuery($query);
			$dbo->query();
			$query = "DELETE FROM #__thm_organizer_lessonperiods WHERE sid = '$sid';";
			$dbo->setQuery($query);
			$dbo->query();
			$query = "DELETE FROM #__thm_organizer_timeperiods WHERE sid = '$sid';";
			$dbo->setQuery($query);
			$dbo->query();
			
			$this->setRedirect($link, JText::_("Die Datei wurde inaktive gestellt").".");
        }
        else
        {
        	$this->setRedirect(JRoute::_('index.php'), JText::_("Zugriff verweigert")."." );
        }
	}
	
	/**
	 * Removes the schedule from the DB.
	 */
    function schedule_delete()
    {
    	$dbo = & JFactory::getDBO();
    	$user =& JFactory::getUser();
        if($user->gid >= 24) $access = true;
        else $access = false ;
        if($access)
        {
			$sid = JRequest::getVar('semesterid');
			$link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
			$id = JRequest::getVar('schedule_id');
		  	$query = "DELETE FROM #__thm_organizer_schedules WHERE id = $id";
		  	$dbo->setQuery($query);
		  	$dbo->query();
			if ($dbo->getErrorNum())
			{
				$this->setRedirect($link, JText::_("Ein Fehler ist aufgetreten."), 'error'  );
			}
			else $this->setRedirect($link, JText::_("Ihre Datei wurde erfolgreich gel&ouml;scht.") );
        }
        else
        {
			$this->setRedirect(JRoute::_('index.php'), JText::_("Zugriff verweigert").".", 'error'  );
        }
    }
    
    /**
	 * Adds or updates the description of the schedule.
	 */
    function updatetext()
	{
    	$dbo = & JFactory::getDBO();
    	$user =& JFactory::getUser();
        if($user->gid >= 24) $access = true;
        else $access = false ;
        if($access)
        {
			$sid = JRequest::getVar('semesterid');
			$link = JRoute::_('index.php?option=com_thm_organizer&view=schedulelist&semesterid='.$sid);
			$id = JRequest::getVar('schedule_id');
			$description = JRequest::getVar('description');
			$dbo = & JFactory::getDBO();
			$query = "UPDATE #__thm_organizer_schedules SET description = '$description' WHERE id = '$id'";
			$dbo->setQuery( $query );
			$dbo->query();	
			if ($dbo->getErrorNum())
			{
	        	$this->setRedirect($link, JText::_("Zugriff verweigert").".", 'error'  );
			}
			else $this->setRedirect($link, JText::_("Text wurde erfolgreich ge&auml;ndert")."." );
        }
        else
        {
			$this->setRedirect(JRoute::_('index.php'), JText::_("Zugriff verweigert").".", 'error'  );
        }
	}
	
	/**
	 * Retrieves the documentation for the lessons.
	 */
	function buildModules(&$subjects)
	{
		require_once(dirname(__FILE__).'/../assets/LSF/lsfapi.php');
		
		/* SOAP-Anfrage durchf�hren */
		$LSFAPI = new LSFAPI("http://www-test.mni.fh-giessen.de:8080/axis2/services/dbinterface?wsdl");
		$qxmlbegin = "<SOAPDataService><general><object>person</object></general><filter><all>";
		$qxmlend = "</all></filter></SOAPDataService>";
		$qxml = $qxmlbegin.$nrmni.$qxmlend;
		$modules =  $LSFAPI->getDataXML($qxml);
		
		if(!$modules) return;
		
		$sid = JRequest::getVar('semesterid');
		$dbo = & JFactory::getDBO();
		$valuechain = "";
		foreach($subjects as $k => $v)
		{
			foreach($modules as $module)
			{
				$modid = $module->nrmni;
				if($modid == $v['module'])
				{
					$mtitle = $module->modultitel[0];
					$shortname = $module->kurzname[0];
					$objective = $module->lernziel[0];
					$content = $module->lerninhalt[0];
					$lit = $module->litverz[0];
					$lp = $module->lp[0];
					$required = $module->vorleistung[0];
					$test = $module->leistungsnachweis[0];
					$tstamp = $module->history->timestamp[0];
					if(count($module->voraussetzung[0]) > 0)
						foreach($module->voraussetzung[0] as $va)
						{
							if($va->getName() == "anmerkung") continue;
							$value = (string) $va;
							$query = "INSERT IGNORE INTO #__thm_organizer_prereq (pid, cid)
										VALUES ('$value', '$modid')";
							$dbo->setQuery( $query );
							$dbo->query();	
						}
					if(count($module->verwendbarkeit[0]) > 0)
						foreach($module->verwendbarkeit[0] as $k => $vw)
						{
							$value = (string) $vw;
							$query = "INSERT IGNORE INTO #__thm_organizer_prereq (pid, cid)
										VALUES ('$modid', '$value')";
							$dbo->setQuery( $query );
							$dbo->query();	
						}
					$query = "INSERT IGNORE 
								INTO #__thm_organizer_modules 
									( modid, mtitle, shortname, objective, content, lit, lp, required, test, tstamp)
								VALUES 
									( '$modid','$mtitle','$shortname','$objective','$content','$lit','$lp','$required','$test','$tstamp');";
					$dbo->setQuery( $query );
					$dbo->query();
					
					$query = "SELECT mid 
								FROM #__thm_organizer_modules
								WHERE modid = '$modid'
									AND tstamp = '$tstamp'";
					$dbo->setQuery( $query );
					$mid = $dbo->loadResult();
					
					$query = "INSERT IGNORE 
								INTO #__thm_organizer_modulesemester (mid, sid)
								VALUES ('$mid','$sid')";
					$dbo->setQuery( $query );
					$dbo->query();
				}
			}
		}
		return;
	}
 	
}
?>