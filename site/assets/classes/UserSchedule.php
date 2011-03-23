<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class UserSchedule
{
	private $jsid = null;
	private $json = null;
	private $username = null;
	private $cfg = null;
	private $JDA = null;
	private $semID = null;
	private $tempusername = null;
	private $classemesterid = null;

	function __construct($JDA, $CFG, $options = array())
	{
		$this->JDA = $JDA;
		$this->jsid = $this->JDA->getUserSessionID();
		$this->json = $this->JDA->getDBO()->getEscaped( file_get_contents( "php://input" ) );

		if(isset($options["username"]))
		{
			$this->username = $options["username"];
		}
		else
		if($this->JDA->getRequest( "username" ))
			$this->username = $this->JDA->getRequest( "username" );
		else
			$this->username = $this->JDA->getUserName();
		$this->cfg = $CFG->getCFG();
		if(isset($options["sid"]))
			$this->semID = $options["sid"];
		else
			$this->semID = $this->JDA->getRequest( "sid" );
	}

	public function save()
	{
		// Wenn die Anfragen nicht durch Ajax von MySched kommt
		if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ) {
			if ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] != 'XMLHttpRequest' )
				die( 'Permission Denied!' );
		} else
			die( 'Permission Denied!' );

		if ( isset( $this->jsid ) ) {
			if ( $this->username != null && $this->username != "" ) {
				$timestamp = time();

				// Alte Eintraege loeschen - Performanter als abfragen und Updaten
				@$this->JDA->query( "DELETE FROM " . $this->cfg[ 'db_table' ] . " WHERE username='$this->username'" );
				$result = $this->JDA->query( "INSERT INTO " . $this->cfg[ 'db_table' ] . " (username, data, created) VALUES ('$this->username', '$this->json', '$timestamp')" );

				// ALLES OK
				return array("data"=>$result );
			} else {
				// FEHLER
				return array("succcess"=>false,"data"=>array(
					 'code' => 'expire',
					 'errors' => array(
						 'reason' => 'Ihre Sitzung ist abgelaufen oder ungültig. Bitte melden Sie sich neu an.'
					)
				) );
			}

		} else {
			// FEHLER
			return array("success"=>false,"data"=>array(
				 'code' => 'expire',
				'errors' => array(
					 'reason' => 'Ihre Sitzung ist abgelaufen oder ungültig. Bitte melden Sie sich neu an.'
				)
			) );
		}
	}

	public function load()
    {
          if (isset($this->username)) {

          	  $tempusername = $this->username;
              if (strpos( strtolower( $this->username ), "delta" ) !== 0 && strpos( strtolower( $this->username ), "respChanges" ) !== 0)
              {
                  $this->username = $this->JDA->getUserName();
              } else if ($this->username == "respChanges")
              {
                  $data = $this->JDA->query("SELECT organization AS orgunit, semesterDesc AS semester FROM #__thm_organizer_semesters WHERE id ='" . $this->semID . "'");
                  $data = $data[0];
                  $this->username = $data->orgunit . "-" . $data->semester;
              } else if($this->username == "delta")
     		  {
                  $this->username = $this->username.$this->semID;
              }
              else
				$this->username = $this->username;

              $data = $this->JDA->query("SELECT data FROM " . $this->cfg['db_table'] . " WHERE username='".$this->username."'");

			  if(is_array($data))
			  {
	              if (count($data) == 1) {
	                  $data = $data[0];
	                  $data = $data->data;
	              } else
	                  $data = array();
			  }
			  else
			  	  $data = array();

              if (strpos( strtolower( $this->username ), "delta" ) === 0) {
              	if(is_array($data))
              		return array("data"=>$data);
              	$data = json_decode($data);
              	$deltadata = array();

              	foreach($data as $lessonkey=>$lessonvalue)
              	{
              		foreach($lessonvalue as $periodkey=>$periodvalue )
              		{
              			if(!is_object($periodvalue))
              				continue;

              			$deltadata[] = array();
              			$query = "SELECT period, " .
              					 "day " .
              					 "FROM #__thm_organizer_periods " .
              					 "WHERE gpuntisID = '".$periodkey."' " .
              					 "AND semesterID = ".$periodvalue->source.";";

              			$ret = $this->JDA->query($query);

              			$deltadata[count($deltadata)-1]["block"] = $ret[0]->period;
              			$deltadata[count($deltadata)-1]["dow"] = $ret[0]->day;

						$query = "SELECT gpuntisID, alias, moduleID " .
              					 "FROM #__thm_organizer_subjects " .
              					 "WHERE id = ".$periodvalue->subjectID.";";

              			$ret = $this->JDA->query($query);

						$deltadata[count($deltadata)-1]["name"] = $ret[0]->alias;
						$deltadata[count($deltadata)-1]["desc"] = $ret[0]->moduleID;
						$deltadata[count($deltadata)-1]["cell"] = "";

						if(isset($lessonvalue->changes))
							$deltadata[count($deltadata)-1]["css"] = $lessonvalue->changes;
						else
							$deltadata[count($deltadata)-1]["css"] = "changed";

						$deltadata[count($deltadata)-1]["owner"] = "gpuntis";
						$deltadata[count($deltadata)-1]["showtime"] = "none";

						$deltadata[count($deltadata)-1]["etime"] = null;
						$deltadata[count($deltadata)-1]["stime"] = null;

                      	$deltadata[count($deltadata)-1]["key"] = $lessonkey." ".$periodkey;

                      	$deltadata[count($deltadata)-1]["id"] = $lessonkey;

                      	$deltadata[count($deltadata)-1]["subject"] = $ret[0]->gpuntisID;

                      	$query = "SELECT type " .
              					 "FROM #__thm_organizer_lessons " .
              					 "WHERE gpuntisID = '".$lessonkey."' " .
              					 "AND subjectID = ".$periodvalue->subjectID.";";

              			$ret = $this->JDA->query($query);

						if(isset($ret[0]->type))
                      		$deltadata[count($deltadata)-1]["category"] = $ret[0]->type;
                      	else
                      		$deltadata[count($deltadata)-1]["category"] = "";

                      	$deltadata[count($deltadata)-1]["type"] = "cyclic";

						$deltadata[count($deltadata)-1]["clas"] = "";
						$deltadata[count($deltadata)-1]["room"] = "";
						$deltadata[count($deltadata)-1]["doz"] = "";

						foreach($periodvalue->classIDs as $classvalue)
						{
							$query = "SELECT gpuntisID " .
              						 "FROM #__thm_organizer_classes " .
              					 	 "WHERE id = ".$classvalue.";";

              				$ret = $this->JDA->query($query);

							if($deltadata[count($deltadata)-1]["clas"] === "")
								$deltadata[count($deltadata)-1]["clas"] = $ret[0]->gpuntisID;
							else
              					$deltadata[count($deltadata)-1]["clas"] = $deltadata[count($deltadata)-1]["clas"]." ".$ret[0]->gpuntisID;
						}

						foreach($periodvalue->roomIDs as $roomvalue)
						{
							$query = "SELECT gpuntisID " .
              						 "FROM #__thm_organizer_rooms " .
              					 	 "WHERE id = ".$roomvalue.";";

              				$ret = $this->JDA->query($query);

							if($deltadata[count($deltadata)-1]["room"] === "")
								$deltadata[count($deltadata)-1]["room"] = $ret[0]->gpuntisID;
							else
              					$deltadata[count($deltadata)-1]["room"] = $deltadata[count($deltadata)-1]["room"]." ".$ret[0]->gpuntisID;
						}

						foreach($periodvalue->teacherIDs as $teachervalue)
						{
							$query = "SELECT gpuntisID " .
              						 "FROM #__thm_organizer_teachers " .
              					 	 "WHERE id = ".$teachervalue.";";

              				$ret = $this->JDA->query($query);

							if($deltadata[count($deltadata)-1]["doz"] === "")
								$deltadata[count($deltadata)-1]["doz"] = $ret[0]->gpuntisID;
							else
              					$deltadata[count($deltadata)-1]["doz"] = $deltadata[count($deltadata)-1]["doz"]." ".$ret[0]->gpuntisID;
						}

						if(isset($lessonvalue->changes))
						{
							$deltadata[count($deltadata)-1]["changes"] = $lessonvalue->changes;
						}
						else
						{
							if(is_string($periodvalue->changes))
							{
								$deltadata[count($deltadata)-1]["changes"] = $periodvalue->changes;
								$deltadata[count($deltadata)-1]["css"] = $periodvalue->changes;
							}
							else
							{
								$deltadata[count($deltadata)-1]["changes"] = array();
								if(isset($periodvalue->changes->roomIDs))
									foreach($periodvalue->changes->roomIDs as $k=>$v)
									{
										$query = "SELECT gpuntisID " .
	              						 "FROM #__thm_organizer_rooms " .
	              					 	 "WHERE id = ".$k.";";

	              					 	$ret = $this->JDA->query($query);

	              					 	if(!isset($deltadata[count($deltadata)-1]["changes"]["rooms"]))
	              					 		$deltadata[count($deltadata)-1]["changes"]["rooms"] = array();
	              					 	$deltadata[count($deltadata)-1]["changes"]["rooms"][$ret[0]->gpuntisID] = $v;
									}
								if(isset($periodvalue->changes->teacherIDs))
									foreach($periodvalue->changes->teacherIDs as $k=>$v)
									{
										$query = "SELECT gpuntisID " .
	              						 "FROM #__thm_organizer_teachers " .
	              					 	 "WHERE id = ".$k.";";

	              					 	$ret = $this->JDA->query($query);

	              					 	if(!isset($deltadata[count($deltadata)-1]["changes"]["teachers"]))
	              					 		$deltadata[count($deltadata)-1]["changes"]["teachers"] = array();
	              					 	$deltadata[count($deltadata)-1]["changes"]["teachers"][$ret[0]->gpuntisID] = $v;
									}
								if(isset($periodvalue->changes->classIDs))
									foreach($periodvalue->changes->classIDs as $k=>$v)
									{
										$query = "SELECT gpuntisID " .
	              						 "FROM #__thm_organizer_classes " .
	              					 	 "WHERE id = ".$k.";";

	              					 	$ret = $this->JDA->query($query);

	              					 	if(!isset($deltadata[count($deltadata)-1]["changes"]["classes"]))
	              					 		$deltadata[count($deltadata)-1]["changes"]["classes"] = array();
	              					 	$deltadata[count($deltadata)-1]["changes"]["classes"][$ret[0]->gpuntisID] = $v;
									}
							}
						}
              		}
              	}
              	$data = json_encode($deltadata);
				return array("success"=>true, "data"=>$data);
              }
              else
              	if($tempusername === "respChanges" )
              	{
					return array("success"=>true, "data"=>$data);
              	}

				if(count($data) === 0)
					return array("data"=>$data);

              $data = json_decode($data);

              $query = "SELECT " .
              			 "CONCAT('".$this->semID."',CONCAT('.1',CONCAT('.',CONCAT(CONCAT(#__thm_organizer_lessons.gpuntisID, ' '),#__thm_organizer_periods.gpuntisID)))) AS mykey," .
						 "#__thm_organizer_lessons.gpuntisID AS lid, " .
						 "#__thm_organizer_periods.gpuntisID AS tpid, " .
						 "#__thm_organizer_lessons.gpuntisID AS id, " .
						 "#__thm_organizer_subjects.alias AS description, " .
						 "#__thm_organizer_subjects.gpuntisID AS subject, " .
						 "#__thm_organizer_lessons.type AS ltype, " .
						 "#__thm_organizer_subjects.name AS name, " .
						 "#__thm_organizer_classes.gpuntisID AS cid, " .
						 "#__thm_organizer_teachers.gpuntisID AS tid, " .
						 "#__thm_organizer_rooms.gpuntisID AS rid, " .
						 "#__thm_organizer_periods.day AS dow, " .
						 "#__thm_organizer_periods.period AS block, " .
						 "(SELECT 'cyclic') AS type, ";

				if ($this->JDA->isComponentavailable("com_giessenlsf"))
				{
					$query .= " modultitel AS longname FROM #__thm_organizer_objects AS lo LEFT JOIN #__giessen_lsf_modules AS mo ON lo.oalias = mo.modulnummer ";
				}
				else
				{
					$query .= " '' AS longname ";
				}

				$query .= "FROM #__thm_organizer_lessons " .
				  "INNER JOIN #__thm_organizer_lesson_times ON #__thm_organizer_lessons.id = #__thm_organizer_lesson_times.lessonID " .
				  "INNER JOIN #__thm_organizer_periods ON #__thm_organizer_lesson_times.periodID = #__thm_organizer_periods.id " .
				  "INNER JOIN #__thm_organizer_rooms ON #__thm_organizer_lesson_times.roomID = #__thm_organizer_rooms.id " .
				  "INNER JOIN #__thm_organizer_lesson_teachers ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " .
				  "INNER JOIN #__thm_organizer_teachers ON #__thm_organizer_lesson_teachers.teacherID = #__thm_organizer_teachers.id " .
				  "INNER JOIN #__thm_organizer_lesson_classes ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_lessons.id " .
				  "INNER JOIN #__thm_organizer_classes ON #__thm_organizer_lesson_classes.classID = #__thm_organizer_classes.id " .
				  "INNER JOIN #__thm_organizer_subjects ON #__thm_organizer_lessons.subjectID = #__thm_organizer_subjects.id " .
	         	  "WHERE #__thm_organizer_lessons.semesterID = '".$this->semID."' AND #__thm_organizer_lessons.gpuntisID IN (";

              if (isset($data))
                  if (is_array($data))
                      foreach ($data as $v) {
                          if (isset($v->id))
                              $query = $query . "'" . $v->id . "',";
                      }

              $query = substr($query, 0, strlen($query) - 1);
              $query = $query . ");";

              $ret = $this->JDA->query($query);

              $lessons = array();

              if (isset($ret))
                  if (is_array($ret))
                      foreach ($ret as $v) {
                          $key = $v->mykey;
                          if (!isset($lessons[$key]))
                              $lessons[$key] = array();
                          $lessons[$key]["category"] = $v->ltype;
                          if (isset($lessons[$key]["clas"])) {
                              $arr = explode(" ", $lessons[$key]["clas"]);
                              if (!in_array($v->cid, $arr))
                                  $lessons[$key]["clas"] = $lessons[$key]["clas"] . " " . $v->cid;
                          } else
                              $lessons[$key]["clas"] = $v->cid;

                          if (isset($lessons[$key]["doz"])) {
                              $arr = explode(" ", $lessons[$key]["doz"]);
                              if (!in_array($v->tid, $arr))
                                  $lessons[$key]["doz"] = $lessons[$key]["doz"] . " " . $v->tid;
                          } else
                              $lessons[$key]["doz"] = $v->tid;

                          if (isset($lessons[$key]["room"])) {
                              $arr = explode(" ", $lessons[$key]["room"]);
                              if (!in_array($v->rid, $arr))
                                  $lessons[$key]["room"] = $lessons[$key]["room"] . " " . $v->rid;
                          } else
                              $lessons[$key]["room"] = $v->rid;

                          $lessons[$key]["dow"] = $v->dow;
                          $lessons[$key]["block"] = $v->block;
                          $lessons[$key]["name"] = $v->name;
                          $lessons[$key]["desc"] = $v->description;
                          $lessons[$key]["cell"] = "";
                          $lessons[$key]["css"] = "";
                          $lessons[$key]["owner"] = "gpuntis";
                          $lessons[$key]["showtime"] = "none";
                          $lessons[$key]["etime"] = null;
                          $lessons[$key]["stime"] = null;
                          $lessons[$key]["key"] = $key;
                          $lessons[$key]["id"] = $v->id;
                          $lessons[$key]["subject"] = $v->subject;
                          $lessons[$key]["type"] = $v->type;
                      }

              $retlesson = array();
              $found = false;

              if (isset($data))
                  if (is_array($data))
                      foreach ($data as $v) {
                          if (isset($v->type))
                              if ($v->type == "cyclic") {
                                  $found = false;
                                  foreach ($lessons as $litem) {
                                      if (isset($v->key))
                                      {
                                          if ($v->key == $litem['key']) {
                                              //Veranstaltung existiert
                                              if ($v->clas != $litem['clas'] || $v->room != $litem['room'] || $v->doz != $litem['doz']) {
                                                  $litem["css"] = " movedtomysched";
                                                  $retlesson[count($retlesson)] = $litem;
                                              } else {
                                                  $v->css = "";
                                                  $retlesson[count($retlesson)] = $v;
                                              }
                                              $found = true;
                                              break;
                                          }
                                      }
                                  }
                                  if ($found == false) {
                                      foreach ($lessons as $litem) {
                                          if ($v->id == $litem["id"])
                                              foreach ($data as $d) {
                                                  if (isset($d->key)) {
                                                      if ($litem["key"] != $d->key) {
                                                          $litem["css"] = "mysched_proposal";
                                                          $retlesson[count($retlesson)] = $litem;
                                                      }
                                                  }
                                              }
                                      }
                                  }
                              } else {
                                  $retlesson[count($retlesson)] = $v;
                              }
                      }
              $retlesson = json_encode($retlesson);

              return array("data"=>$retlesson);
          } else {
              // SESSION FEHLER
              return array("success"=>false, "data"=>array('code' => 'expire', 'errors' => array('reason' => 'Ihre Sitzung ist abgelaufen oder ungültig. Bitte melden Sie sich neu an.')));
          }
      }
}
?>