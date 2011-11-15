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

				if(count($data) === 0)
					return array("data"=>$data);

              $data = json_decode($data);

			  if (isset($data))
              if (is_array($data))
              if(count($data) > 1)
              {

              $query = "SELECT " .
              			 "CONCAT('".$this->semID."',CONCAT('.1',CONCAT('.',CONCAT(CONCAT(#__thm_organizer_lessons.gpuntisID, ' '),#__thm_organizer_periods.gpuntisID)))) AS mykey," .
						 "#__thm_organizer_lessons.gpuntisID AS lid, " .
						 "#__thm_organizer_periods.gpuntisID AS tpid, " .
						 "#__thm_organizer_lessons.gpuntisID AS id, " .
						 "#__thm_organizer_subjects.alias AS description, " .
						 "#__thm_organizer_subjects.gpuntisID AS subject," .
						 "#__thm_organizer_subjects.moduleID AS moduleID, " .
						 "#__thm_organizer_lessons.type AS ltype, " .
						 "#__thm_organizer_subjects.name AS name, " .
						 "#__thm_organizer_classes.id AS cid, " .
						 "#__thm_organizer_teachers.id AS tid, " .
						 "#__thm_organizer_rooms.id AS rid, " .
						 "#__thm_organizer_periods.day AS dow, " .
						 "#__thm_organizer_periods.period AS block, " .
						 "(SELECT 'cyclic') AS type, ";

				if ($this->JDA->isComponentavailable("com_thm_lsf"))
				{
					$query .= " modultitel_de AS longname ";
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
				  	"INNER JOIN #__thm_organizer_subjects ON #__thm_organizer_lessons.subjectID = #__thm_organizer_subjects.id ";
					if ($this->JDA->isComponentavailable("com_thm_lsf"))
		  			{
						$query .= "LEFT JOIN #__thm_lsf_modules AS mo ON #__thm_organizer_subjects.moduleID = mo.modulnummer ";
		  			}
	         	  	$query .= "WHERE #__thm_organizer_lessons.semesterID = '".$this->semID."' AND #__thm_organizer_lessons.gpuntisID IN (";

              if (isset($data))
                  if (is_array($data))
                      foreach ($data as $v) {
                          if (isset($v->id))
                              $query = $query . "'" . $v->id . "',";
                      }

              $query = substr($query, 0, strlen($query) - 1);
              $query = $query . ");";

              $ret = $this->JDA->query($query);
              }
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
                          $lessons[$key]["moduleID"] = $v->moduleID;
                          $lessons[$key]["semesterID"] = $this->semID;
                          $lessons[$key]["plantypeID"] = 1;
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