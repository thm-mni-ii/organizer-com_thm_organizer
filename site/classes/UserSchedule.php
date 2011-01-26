<?php
class UserSchedule
{
	private $jsid = null;
	private $sid = null;
	private $json = null;
	private $username = null;
	private $cfg = null;
	private $JDA = null;
	private $semID = null;
	private $tempusername = null;
	private $classemesterid = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->jsid = $this->JDA->getRequest( "jsid" );
		$this->sid  = $this->JDA->getRequest( "sid" );
		$this->json = $this->JDA->getDBO()->getEscaped( file_get_contents( "php://input" ) );

		if($this->JDA->getRequest( "username" ))
			$this->username = $this->JDA->getRequest( "username" );
		else
			$this->username = $this->JDA->getUserName();
		$this->cfg = $CFG->getCFG();
		$this->semID = $this->JDA->getSemID();
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
          if (isset($this->username) && isset($this->semID)) {
              if ($this->username != "delta" && $this->username != "respChanges") {
                  $this->username = $this->JDA->getUserName();
              } elseif ($this->username == "respChanges") {
                  $data = $this->JDA->query("SELECT orgunit, semester FROM " . $this->cfg['jdb_table_semester'] . " WHERE sid ='" . $this->semID . "'");
                  $data = $data[0];
                  $this->username = $data->orgunit . "-" . $data->semester;
              } else {
                  $this->username = $this->username;
              }

              $data = $this->JDA->query("SELECT data FROM " . $this->cfg['db_table'] . " WHERE username='".$this->username."'");
              if (count($data) == 1) {
                  $data = $data[0];
                  $data = $data->data;
              } else
                  $data = array();
              if ($this->username == "delta" || $this->username == "respChanges") {
              	return array("success"=>true, "data"=>$data);
              }

              $data = json_decode($data);
              $query = "SELECT CONCAT(CONCAT(jos_giessen_scheduler_lessons.lid, ' '),jos_giessen_scheduler_lessonperiods.tpid) AS mykey, cid, rid, tid, ltype, jos_giessen_scheduler_timeperiods.day AS dow, period AS block, oname AS name, jos_giessen_scheduler_objects.oalias AS description, jos_giessen_scheduler_objects.oid AS id, (SELECT 'cyclic') AS type
				        FROM jos_giessen_scheduler_lessons
				        INNER JOIN jos_giessen_scheduler_lessonperiods
				        ON jos_giessen_scheduler_lessons.lid = jos_giessen_scheduler_lessonperiods.lid
				        INNER JOIN jos_giessen_scheduler_timeperiods
				        ON jos_giessen_scheduler_lessonperiods.tpid = jos_giessen_scheduler_timeperiods.tpid
				        INNER JOIN jos_giessen_scheduler_objects
				        ON jos_giessen_scheduler_lessonperiods.lid = jos_giessen_scheduler_objects.oid
				        WHERE otype = 'lesson' AND jos_giessen_scheduler_lessons.sid = '".$this->semID."' AND jos_giessen_scheduler_lessons.lid IN (";

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
                          $lessons[$key]["subject"] = $v->id;
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
                                          if ($v->key == $litem['key']) {
                                              //Veranstaltung existiert
                                              if ($v->clas != $litem['clas'] || $v->room != $litem['room'] || $v->doz != $litem['doz']) {
                                                  $litem["css"] = " movedtomysched";
                                                  $retlesson[count($retlesson)] = $litem;
                                                  /*            if(!isset($retlesson[count($retlesson)-1]["changes"]))
                                                   $retlesson[count($retlesson)-1]["changes"] = array();
                                                   if($v->clas != $litem['clas'])
                                                   {
                                                   if(!isset($retlesson[count($retlesson)-1]["changes"]["classes"]))
                                                   $retlesson[count($retlesson)-1]["changes"]["classes"] = array();

                                                   $arr = explode(' ', $v->clas);
                                                   $arrnew = explode(' ', $litem['clas']);

                                                   foreach($arrnew as $ci)
                                                   {
                                                   if(strpos($ci, $v->clas) === false)
                                                   {
                                                   $retlesson[count($retlesson)-1]["changes"]["classes"][$ci] = "removed";
                                                   }
                                                   }
                                                   foreach($arr as $ci)
                                                   {
                                                   if(strpos($ci, $litem['clas']) === false)
                                                   {
                                                   $retlesson[count($retlesson)-1]["changes"]["classes"][$ci] = "new";
                                                   }
                                                   }
                                                   }
                                                   if($v->room != $litem['room'])
                                                   {
                                                   if(!isset($retlesson[count($retlesson)-1]["changes"]["rooms"]))
                                                   $retlesson[count($retlesson)-1]["changes"]["rooms"] = array();

                                                   $arr = explode(' ', $v->room);
                                                   $arrnew = explode(' ', $litem['room']);

                                                   foreach($arrnew as $ci)
                                                   {
                                                   if(strpos($ci, $v->room) === false)
                                                   {
                                                   $retlesson[count($retlesson)-1]["changes"]["rooms"][$ci] = "removed";
                                                   }
                                                   }
                                                   foreach($arr as $ci)
                                                   {
                                                   if(strpos($ci, $litem['room']) === false)
                                                   {
                                                   $retlesson[count($retlesson)-1]["changes"]["rooms"][$ci] = "new";
                                                   }
                                                   }
                                                   }
                                                   if($v->doz != $litem['doz'])
                                                   {
                                                   if(!isset($retlesson[count($retlesson)-1]["changes"]["teachers"]))
                                                   $retlesson[count($retlesson)-1]["changes"]["teachers"] = array();

                                                   $arr = explode(' ', $v->doz);
                                                   $arrnew = explode(' ', $litem['doz']);

                                                   foreach($arrnew as $ci)
                                                   {
                                                   if(strpos($ci, $v->doz) === false)
                                                   {
                                                   $retlesson[count($retlesson)-1]["changes"]["teachers"][$ci] = "removed";
                                                   }
                                                   }
                                                   foreach($arr as $ci)
                                                   {
                                                   if(strpos($ci, $litem['doz']) === false)
                                                   {
                                                   $retlesson[count($retlesson)-1]["changes"]["teachers"][$ci] = "new";
                                                   }
                                                   }
                                                   }*/
                                              } else {
                                                  $v->css = "";
                                                  $retlesson[count($retlesson)] = $v;
                                              }
                                              $found = true;
                                              break;
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