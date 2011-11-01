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
				$classList = $this->getGPUntisID($classIDList, "class");
				$teacherList = $this->getGPUntisID($teacherIDList, "teacher");

				$lessonInfo = $this->JDA->query("SELECT subjects.name, " .
														"subjects.alias AS description, " .
														"subjects.name AS subject, " .
														"subjects.moduleID AS moduleID " .
												"FROM #__thm_organizer_subjects AS subjects " .
												"WHERE subjects.id = '".$dataValue->subjectID."'");

				$lessonInfo = $lessonInfo[0];

				$periodIDList = implode(", ", array_keys((array)$dataValue->periods));

				$periodList = $this->JDA->query("SELECT id, gpuntisID, day, period FROM #__thm_organizer_periods WHERE id IN(".$periodIDList.")");

				foreach($periodList as $periodKey=>$periodValue)
				{
					$periods = $dataValue->periods;
					$roomIDList = implode(", ", $periods->{$periodValue->id}->roomIDs);

					$roomList = $this->getGPUntisID($roomIDList, "room");

					$key = $this->semesterID.".1.".$dataKey." ".$periodValue->gpuntisID;

					$lessons[$lessoncounter]["room"] = implode(",", $roomList);
					$lessons[$lessoncounter]["clas"] = implode(",", $classList);
					$lessons[$lessoncounter]["doz"] = implode(",", $teacherList);

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

					if(isset($dataValue->status))
						$lessons[$lessoncounter]["lessonChanges"]["status"] = $dataValue->status;

					if(isset($dataValue->changes)) {
						if(isset($dataValue->changes->teacherIDs)) {
							$teacherList = array();
							foreach($dataValue->changes->teacherIDs as $teacherKey=>$teacherValue)
							{
								$teacherList[$this->getGPUntisID($teacherKey, "teacher")] = $teacherValue;
							}
							$lessons[$lessoncounter]["lesssonChanges"]["teacherIDs"] = $teacherList;
						}
						if(isset($dataValue->changes->classIDs)) {
							$classList = array();
							foreach($dataValue->changes->classIDs as $classKey=>$classValue)
							{
								$classList[$this->getGPUntisID($classKey, "class")] = $classValue;
							}
							$lessons[$lessoncounter]["lesssonChanges"]["classIDs"] = $classList;
						}
					}

					if(isset($periodValue->status))
                  		$lessons[$lessoncounter]["periodChanges"]["status"] = $periodValue->status;

					if(isset($periodValue->changes)) {
						if(isset($dataValue->changes->roomIDs)) {
							$roomList = array();
							foreach($dataValue->changes->roomIDs as $roomKey=>$roomValue)
							{
								$roomList[$this->getGPUntisID($classKey, "room")] = $roomValue;
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
