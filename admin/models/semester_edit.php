<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        semester editor model
 * @description db abstraction file for the editing  of semester entries
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen <year>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */

defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelsemester_edit extends JModel
{
    public $id;
    public $organization;
    public $semesterDesc;
    public $manager;
    public $userGroups;
    public $schedules;

    function __construct()
    {
        parent::__construct();
        $this->getData();
        $this->getSchedules();
        $this->getUserGroups();
    }

    function getData()
    {
        $sids = JRequest::getVar('cid',  null, '', 'array');
        if(!empty($sids)) $sid = $sids[0];
        if(!isset($sid)) $sid = JRequest::getVar('semesterID');
        if(is_numeric($sid) and $sid != 0)
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $query->select("*");
            $query->from("#__thm_organizer_semesters");
            $query->where("id = '$sid'");
            $dbo->setQuery((string)$query);
            $semesterData = $dbo->loadAssoc();
            if(!empty($semesterData))
                foreach($semesterData as $k => $v)$this->$k = $v;
        }
        else
        {
            $this->id = 0;
            $this->manager = 0;
            $this->organization = '';
            $this->semesterDesc = '';
        }
    }

    /**
     * private function getSemesters
     *
     * gets the IDs and names of user groups with admin login priveledges
     */
    private function getUserGroups()
    {
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select('id, title');
        $query->from('#__usergroups');
        $dbo->setQuery((string)$query);
        $usergroups = $dbo->loadAssocList();
        foreach($usergroups as $k => $v)
            if(!JAccess::checkGroup($v['id'], 'core.login.admin'))
                unset($usergroups[$k]);
        $this->userGroups = $usergroups;
    }

    /**
     * private function getSchedules
     * 
     * creates a list of schedules asscociated with the selected semester
     */
    private function getSchedules()
    {		
        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->select("id, includedate, filename, active, description");
        $query->from("#__thm_organizer_schedules");
        $query->where("sid = '{$this->id}'");
        $dbo->setQuery((string)$query);
        $schedules = $dbo->loadAssocList();
        $this->schedules = $schedules;
    }

    function store()
    {
        $id = JRequest::getVar('id');
        $manager =  JRequest::getVar('manager');
        $organization = trim(JRequest::getVar( 'organization', '', 'post','string', JREQUEST_ALLOWRAW ));
        $semester = trim(JRequest::getVar( 'semester', '', 'post','string', JREQUEST_ALLOWRAW ));
        if(empty($organization) or empty($semester)) return 0;

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        if(empty($id))
        {
            $query->insert("#__thm_organizer_semesters ( manager, organization, semesterDesc)
                            VALUES ( '$manager', '$organization', '$semester' );");
        }
        else
        {
            $query->update("#__thm_organizer_semesters");
            $query->set("manager = '$manager', organization = '$organization', semesterDesc = '$semester'");
            $query->where("id = '$id';");
        }
        $dbo->setQuery((string)$query);
        $dbo->query();
        if($dbo->getErrorNum()) return (string)$query;

        $query->select("id");
        $query->from("#__thm_organizer_semesters");
        $query->where("manager = '$manager' AND semesterDesc = '$semester'");
        $dbo->setQuery((string)$query);
        $id = $dbo->loadResult();
        if($dbo->getErrorNum()) return (string)$query;
        else return $id;
    }

    function delete()
    {
        global $mainframe;
        $ids = JRequest::getVar( 'cid' );
        if(count( $ids ))
        {
            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $ids = "'".implode("', '", $ids)."'";

            $query->delete();
            $query->from("#__thm_organizer_semesters");
            $query->where("id IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();;

            $query->from("#__thm_organizer_schedules");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query->from("#__thm_organizer_user_schedules");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query->from("#__thm_organizer_virtual_schedules");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();
            
            $query->from("#__thm_organizer_virtual_schedule_elements");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query->update("#__thm_organizer_monitors");
            $query->set("semesterID = ''");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $dbo->query();

            $query->select("id");
            $query->from("#__thm_organizer_lessons");
            $query->where("sid IN ( $ids )");
            $dbo->setQuery((string)$query);
            $lessonIDs = $dbo->loadResultArray();

            if(!empty($lessonIDS))
            {
                $lessonIDs = "'".implode("', '", $lessonIDs)."'";

                $query->delete();
                $query->from("#__thm_organizer_lessons");
                $query->where("sid IN ( $ids )");
                $dbo->setQuery((string)$query);
                $dbo->query();

                $query->from("#__thm_organizer_lesson_times");
                $query->where("lessonID IN ( $lessonIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();

                $query->from("#__thm_organizer_lesson_teachers");
                $query->where("lessonID IN ( $lessonIDs )");
                $dbo->setQuery((string)$query);
                $dbo->query();
            }
        }
        return true;
    }

    /**
     * public function upload
     *
     * saves a gp-untis schedule file in the database for later use
     */
    public function uploadXML(&$errors)
    {
        $sid = JRequest::getVar('semesterID');
        $tmpName  = $_FILES['file']['tmp_name'];
        $file = simplexml_load_file($tmpName);
        $creationdate = (string)$file[0]['date'];
        $startdate = (string)$file->general->schoolyearbegindate;
        $startdate = (string)$file->general->schoolyearenddate;
        $this->validate(&$file, &$errors);
        unset($file);

        if(count($errors['dataerrors']) === 0)
        {
            $fileSize = $_FILES['file']['size'];
            $fileName = $_FILES['file']['name'];
            $date = date('Y-m-d');
            $fp = fopen($tmpName, 'r');
            $content = fread($fp, filesize($tmpName));
            fclose($fp);
            $content = addslashes($content);

            $dbo = JFactory::getDBO();
            $query = $dbo->getQuery(true);
            $statement = "#__thm_organizer_schedules(filename, file, includedate, creationdate, startdate, enddate, sid)
                          VALUES ('$fileName', '$content', '$date','$creationdate', '$startdate', '$enddate', '$sid')";
            $query->insert($statement);
            $dbo->setQuery((string)$query );
            $dbo->query();
            if ($dbo->getErrorNum())$errors['dberrors'] = true;
        }
    }

    /**
     * public funtion validate
     *
     * checks a given schedule in gp-untis xml format for data completeness and consistency
     */
    public function validate(&$file, &$errors)
    {

        //variables for checking data consistency
        $descriptions = array(); $departments = array(); $timeperiods = array();
        $subjects = array(); $classes = array(); $teachers = array(); $rooms = array();
        $lessons = array(); $lessonclasses = array();
        $subjectobjects = array();
        $teachers = array();

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
    }*/

    /**
     * public funtion activate
     *
     * earmarks an gp-untis schedule as being active for the given planning period
     */
    public function activate(){}

    /**
     * private funtion delta
     *
     * creates a change set between the currently active schedule and the schedule to
     * become active, and saves this data as a json string in the structure used by
     * the scheduler rich internet application
     */
    private function delta(){}
				

    /**
    * public function deactivate
    *
    * sets the current active schedule to inactive. this entails the deletion
    * of the delta, and the removal of schedule specific data from the db.
    */
    public function deactivate()
    {
        $semesterID = JRequest::getVar('semesterID');

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        //set active to false
        $query = "UPDATE #__giessen_scheduler_schedules SET active = NULL WHERE active IS NOT NULL AND sid = '$sid';";
        $dbo->setQuery( $query );
        $dbo->query();

        $query = "DELETE FROM #__giessen_scheduler_user_schedules WHERE username = 'delta' AND sid = '$sid';";
        $dbo->setQuery( $query );
        $dbo->query();//no error check there may be no delta in the db


        //remove active data
        $query = "DELETE FROM #__giessen_scheduler_objects WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        $query = "DELETE FROM #__giessen_scheduler_lessons WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        $query = "DELETE FROM #__giessen_scheduler_lessonperiods WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();
        $query = "DELETE FROM #__giessen_scheduler_timeperiods WHERE sid = '$sid';";
        $dbo->setQuery($query);
        $dbo->query();

        $this->setRedirect($link, JText::_("Die Datei wurde inaktive gestellt").".");
    }

    /**
     * Removes the schedule from the DB.
     */
    function schedule_delete()
    {
        $scheduleID = JRequest::getVar('schedule_id');

    	$dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->delete();
        $query->from("#__thm_organizer_schedules");
        $query->where("id = '$id'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $semesterID = JRequest::getVar('id');
        if ($dbo->getErrorNum())return 0;
        else return $semesterID;
    }

    /**
     * public function updateText
     *
     * Adds or updates the description of the schedule.
     */
    function updateText()
    {
        $scheduleID = JRequest::getVar('schedule_id');
        $description = JRequest::getVar('description');

        $dbo = & JFactory::getDBO();
        $query = $dbo->getQuery(true);
        $query->update("#__thm_organizer_schedules");
        $query->set("description = '$description'");
        $query->where("id = '$id'");
        $dbo->setQuery((string)$query);
        $dbo->query();

        $semesterID = JRequest::getVar('id');
        if ($dbo->getErrorNum())return 0;
        else return $semesterID;
    }
}
?>