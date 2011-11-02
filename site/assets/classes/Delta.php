<?php
/*
 * Created on 31.10.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

 class Delta {
 	private $JDA = null;
	private $CFG = null;
	private $semesterID = null;
	private $plantypeID = null;

 	public function __construct($JDA, $CFG) {
		$this->JDA = $JDA;
		$this->CFG = $CFG;
		$this->semesterID = $this->JDA->getRequest( "sid" );
		$this->plantypeID = $this->JDA->getRequest( "plantypeID" );
		$this->plantypeID = 1;
 	}

 	public function load() {
		$deltas = $this->JDA->query("SELECT delta FROM #__thm_organizer_deltas WHERE semesterID ='" . $this->semesterID . "' AND  plantypeID ='" . $this->plantypeID . "'");

		$lessons = array();
		$lessoncounter = 0;

		if(count($deltas) == 1) {
			$data = json_decode($deltas[0]->delta);

			foreach($data as $dataKey=>$dataValue) {
				$classIDList = implode(", ", $dataValue->classIDs);
				$teacherIDList = implode(", ", $dataValue->teacherIDs);

				$classMainList = $this->getGPUntisID($classIDList, "class");
				$teacherMainList = $this->getGPUntisID($teacherIDList, "teacher");

				$lessonInfo = $this->JDA->query("SELECT subjects.name, " .
														"subjects.alias AS description, " .
														"subjects.name AS subject, " .
														"subjects.moduleID AS moduleID " .
												"FROM #__thm_organizer_subjects AS subjects " .
												"WHERE subjects.id = '".$dataValue->subjectID."'");

				if(!isset($lessonInfo[0]))
					return array("data"=>json_encode($lessons));

				$lessonInfo = $lessonInfo[0];

				$periodIDList = implode(", ", array_keys((array)$dataValue->periods));

				$periodList = $this->JDA->query("SELECT id, gpuntisID, day, period FROM #__thm_organizer_periods WHERE id IN(".$periodIDList.")");

				foreach($periodList as $periodKey=>$periodValue)
				{
					$periods = $dataValue->periods;
					$roomIDList = implode(", ", $periods->{$periodValue->id}->roomIDs);

					$roomMainList = $this->getGPUntisID($roomIDList, "room");

					$key = $this->semesterID.".1.".$dataKey." ".$periodValue->gpuntisID;

					$lessons[$lessoncounter]["room"] = implode(" ", $roomMainList);
					$lessons[$lessoncounter]["clas"] = implode(" ", $classMainList);
					$lessons[$lessoncounter]["doz"] = implode(" ", $teacherMainList);

                  	$lessons[$lessoncounter]["dow"] = $periodValue->day;
                  	$lessons[$lessoncounter]["block"] = $periodValue->period;
                  	$lessons[$lessoncounter]["name"] = $lessonInfo->name;
                  	$lessons[$lessoncounter]["desc"] = $lessonInfo->description;
                  	$lessons[$lessoncounter]["cell"] = "";
                  	$lessons[$lessoncounter]["css"] = "";
                  	$lessons[$lessoncounter]["owner"] = "gpuntis";
                  	$lessons[$lessoncounter]["showtime"] = "none";
                  	$lessons[$lessoncounter]["etime"] = null;
                  	$lessons[$lessoncounter]["stime"] = null;
                  	$lessons[$lessoncounter]["key"] = $key;
                  	$lessons[$lessoncounter]["id"] = $dataKey;
                  	$lessons[$lessoncounter]["subject"] = $lessonInfo->subject;
                  	$lessons[$lessoncounter]["type"] = "cyclic";
                  	$lessons[$lessoncounter]["category"] = $dataValue->type;
                  	$lessons[$lessoncounter]["moduleID"] = $lessonInfo->moduleID;
                  	$lessons[$lessoncounter]["comment"] = $dataValue->comment;

					if(isset($dataValue->status))
						$lessons[$lessoncounter]["lessonChanges"]["status"] = $dataValue->status;

					if(isset($dataValue->changes)) {
						if(isset($dataValue->changes->teacherIDs)) {
							$teacherList = array();
							foreach($dataValue->changes->teacherIDs as $teacherKey=>$teacherValue)
							{
								$teacherGPUntisID = $this->getGPUntisID($teacherKey, "teacher");
								$teacherList[$teacherGPUntisID[0]] = $teacherValue;
							}
							$lessons[$lessoncounter]["lessonChanges"]["teacherIDs"] = $teacherList;
						}
						if(isset($dataValue->changes->classIDs)) {
							$classList = array();
							foreach($dataValue->changes->classIDs as $classKey=>$classValue)
							{
								$classGPUntisID = $this->getGPUntisID($classKey, "class");
								$classList[$classGPUntisID[0]] = $classValue;
							}
							$lessons[$lessoncounter]["lesssonChanges"]["classIDs"] = $classList;
						}
					}

					$period = $dataValue->periods->{$periodValue->id};

					if(isset($period->status))
                  		$lessons[$lessoncounter]["periodChanges"]["status"] = $period->status;

					if(isset($period->changes)) {
						if(isset($period->changes->roomIDs)) {
							$roomList = array();
							foreach($period->changes->roomIDs as $roomKey=>$roomValue)
							{
								$roomGPUntisID = $this->getGPUntisID($roomKey, "room");
								$roomList[$roomGPUntisID[0]] = $roomValue;
							}
							$lessons[$lessoncounter]["periodChanges"]["roomIDs"] = $roomList;
						}
					}
					$lessoncounter++;
				}
			}
		}

		return array("data"=>json_encode($lessons));
 	}
 	private function getGPUntisID($ids, $type)
 	{
 		$query = "SELECT gpuntisID FROM ";
 		if($type == "teacher")
 			$query .= "#__thm_organizer_teachers";
 		if($type == "class")
 			$query .= "#__thm_organizer_classes";
 		if($type == "room")
 			$query .= "#__thm_organizer_rooms";

 		$query .= " WHERE id IN(".$ids.")";

		$result = $this->JDA->query($query);

		$resultReturn = array();

		foreach($result as $k=>$v) {
			$resultReturn[] = $v->gpuntisID;
		}

 		return $resultReturn;
 	}
 }
?>
