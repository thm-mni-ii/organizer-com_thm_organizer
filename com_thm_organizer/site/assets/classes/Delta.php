<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		Delta
 * @description Delta file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

/**
 * Class Delta for component com_thm_organizer
 *
 * Class provides methods to load the delta schedule
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class Delta
{
	/**
	 * Joomla data abstraction
	 *
	 * @var    DataAbstraction
	 * @since  1.0
	 */
	private $_JDA = null;

	/**
	 * Configuration
	 *
	 * @var    MySchedConfig
	 * @since  1.0
	 */
	private $_CFG = null;

	/**
	 * Semester id
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $_semesterID = null;

	/**
	 * Plantype id
	 *
	 * @var    object
	 * @since  1.0
	 */
	private $_plantypeID = null;

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   DataAbstraction  $JDA  A object to abstract the joomla methods
	 * @param   MySchedConfig	 $CFG  A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function __construct($JDA, $CFG)
	{
		$this->_JDA = $JDA;
		$this->_CFG = $CFG;
		$this->_semesterID = $this->_JDA->getRequest("semesterID");
		$this->_plantypeID = $this->_JDA->getRequest("plantypeID");
		$this->_plantypeID = 1;
	}

	/**
	 * Method to get the delta data
	 *
	 * @return Array The delta data
	 */
	public function load()
	{
		$deltas = $this->_JDA->query(
				  	"SELECT delta FROM #__thm_organizer_deltas WHERE semesterID ='" . $this->_semesterID . "'" .
				    "AND plantypeID ='" . $this->_plantypeID . "'"
				  );

		$lessons = array();
		$lessoncounter = 0;

		if (count($deltas) == 1)
		{
			$data = json_decode($deltas[0]->delta);

			foreach ($data as $dataKey => $dataValue)
			{
				$classIDList = implode(", ", $dataValue->classIDs);
				$teacherIDList = implode(", ", $dataValue->teacherIDs);

				$classMainList = $this->getID($classIDList, "class");
				$teacherMainList = $this->getID($teacherIDList, "teacher");

				$lessonInfo = $this->_JDA->query("SELECT subjects.name, " .
						"subjects.alias AS description, " .
						"subjects.name AS subject, " .
						"subjects.moduleID AS moduleID " .
						"FROM #__thm_organizer_subjects AS subjects " .
						"WHERE subjects.id = '" . $dataValue->subjectID . "'");

				if (!isset($lessonInfo[0]))
				{
					return array("data" => json_encode($lessons));
				}

				$lessonInfo = $lessonInfo[0];

				$periodIDList = implode(", ", array_keys((array) $dataValue->periods));

				$periodList = $this->_JDA->query("SELECT id, gpuntisID, day, period FROM #__thm_organizer_periods WHERE id IN(" . $periodIDList . ")");

				foreach ($periodList as $periodValue)
				{
					$periods = $dataValue->periods;
					$roomIDList = implode(", ", $periods->{$periodValue->id}->roomIDs);

					$roomMainList = $this->getID($roomIDList, "room");

					$key = $this->_semesterID . ".1." . $dataKey . " " . $periodValue->gpuntisID;

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
					$lessons[$lessoncounter]["plantypeID"] = $this->_plantypeID;
					$lessons[$lessoncounter]["semesterID"] = $this->_semesterID;

					if (isset($dataValue->status))
					{
						$lessons[$lessoncounter]["lessonChanges"]["status"] = $dataValue->status;
					}

					if (isset($dataValue->changes))
					{
						if (isset($dataValue->changes->teacherIDs))
						{
							$teacherList = array();
							foreach ($dataValue->changes->teacherIDs as $teacherKey => $teacherValue)
							{
								$teacherGPUntisID = $this->getID($teacherKey, "teacher");
								$teacherList[$teacherGPUntisID[0]] = $teacherValue;
							}
							$lessons[$lessoncounter]["lessonChanges"]["teacherIDs"] = $teacherList;
						}
						if (isset($dataValue->changes->classIDs))
						{
							$classList = array();
							foreach ($dataValue->changes->classIDs as $classKey => $classValue)
							{
								$classGPUntisID = $this->getID($classKey, "class");
								$classList[$classGPUntisID[0]] = $classValue;
							}
							$lessons[$lessoncounter]["lessonChanges"]["classIDs"] = $classList;
						}
					}

					$period = $dataValue->periods->{$periodValue->id};

					if (isset($period->status))
					{
						$lessons[$lessoncounter]["periodChanges"]["status"] = $period->status;
					}

					if (isset($period->changes))
					{
						if (isset($period->changes->roomIDs))
						{
							$roomList = array();
							foreach ($period->changes->roomIDs as $roomKey => $roomValue)
							{
								$roomGPUntisID = $this->getID($roomKey, "room");
								if (isset($roomGPUntisID[0]))
								{
									$roomList[$roomGPUntisID[0]] = $roomValue;
								}
							}
							$lessons[$lessoncounter]["periodChanges"]["roomIDs"] = $roomList;
						}
					}
					$lessoncounter++;
				}
			}
		}

		return array("data" => json_encode($lessons));
	}

	/**
	 * Method to get the id of a teacher, class or room
	 *
	 * @param   String  $ids   ids
	 * @param   String  $type  The type (room, teacher, class)
	 *
	 * @return Array The ids
	 */
	private function getID($ids, $type)
	{
		$query = "SELECT id FROM ";
		if ($type == "teacher")
		{
			$query .= "#__thm_organizer_teachers";
		}
		if ($type == "class")
		{
			$query .= "#__thm_organizer_classes";
		}
		if ($type == "room")
		{
			$query .= "#__thm_organizer_rooms";
		}

		$query .= " WHERE id IN(" . $ids . ")";

		$result = $this->_JDA->query($query);

		if ($result === false)
		{
			return array();
		}

		$resultReturn = array();

		foreach ($result as $v)
		{
			$resultReturn[] = $v->id;
		}

		return $resultReturn;
	}
}
