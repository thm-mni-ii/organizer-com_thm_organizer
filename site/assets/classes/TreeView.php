<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/TreeNode.php");

class TreeView
{
	private $JDA = null;
	private $cfg = null;
	private $type = null;

	function __construct($JDA, $CFG, $options = array())
	{
		$this->JDA = $JDA;
		$this->cfg = $CFG->getCFG();
	}

	public function load()
	{
		$semesterJahrNode = array();
		$semesterarray = array();

		$semesterarray = $this->getSemester();

		$treeData["clas"] = array();
		$treeData["room"] = array();
		$treeData["doz"] = array();

		foreach($semesterarray as $key=>$value)
		{
			$semesterJahrNode[] = new TreeNode(
					'semesterjahr' . $value->id,							// id - autom. generated
					$value->semesterDesc,							// text	for the node
					'semesterjahr' . '-root',			// iconCls
					false,								// leaf
					false,								// draggable
					true,								// singleClickExpand
					$value->id,							// key
					null,								// plantype
					null,								// type
					$this->plantype('semesterjahr' . $value->id, $value->id),
					$value->id
				);

			$treeData["clas"] = array_merge( $treeData["clas"], $this->getStundenplanClassData(1, $value->id) );
			$treeData["room"] = array_merge( $treeData["room"], $this->getStundenplanRoomData(1, $value->id) );
			$treeData["doz"] = array_merge( $treeData["doz"], $this->getStundenplanDozData(1, $value->id) );
		}

		return array("success"=>true,"data"=>array("tree"=>$semesterJahrNode,"treeData"=>$treeData));
	}

	private function getSemester()
	{
		$semesterquery = "SELECT id, organization, semesterDesc " .
						 "FROM #__thm_organizer_semesters";

		$semesterarray       = $this->JDA->query( $semesterquery );

		return $semesterarray;
	}

	private function plantype($key, $semesterID)
	{
		$plantypeNode = array();
		$plantypequery = "SELECT #__thm_organizer_plantype.id ," .
						"#__thm_organizer_plantype.plantype " .
						"FROM #__thm_organizer_plantype";

		$plantypes       = $this->JDA->query( $plantypequery );

		foreach($plantypes as $k=>$v)
		{
			$function = (string)$v->plantype."View";
			$function = str_replace(" ", "_", $function);
			$function = str_replace("-", "", $function);
			$key = $key.".".$v->plantype.$v->id;
			$plantypeNode[] = new TreeNode(
					$key,							// id - autom. generated
					$v->plantype,					// text	for the node
					$v->plantype . '-root',			// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					$v->id,
					$v->id,
					null,
					$this->$function($key, $v->id, $semesterID),
					$semesterID
				);
		}
		return $plantypeNode;
	}

	private function StundenplanView($key, $planid, $semesterID)
	{
		$viewNode = array();
		$viewNode[] = new TreeNode(
					$key.".doz",							// id - autom. generated
					"Dozent",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"doz",
					$planid,
					null,
					$this->getStundenplanDoz($key.".doz", $planid, $semesterID),
					$semesterID
				);
		$viewNode[] = new TreeNode(
					$key.".room",							// id - autom. generated
					"Raum",							// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"room",
					$planid,
					null,
					$this->getStundenplanRoom($key.".room", $planid, $semesterID),
					$semesterID
				);
		$viewNode[] = new TreeNode(
					$key.".clas",							// id - autom. generated
					"Semester",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"clas",
					$planid,
					null,
					$this->getStundenplanClass($key.".clas", $planid, $semesterID),
					$semesterID
				);
		$viewNode[] = new TreeNode(
					$key.".delta",						// id - autom. generated
					"Änderungen (zentral)",			// text	for the node
					'delta' . '-node',				// iconCls
					true,							// leaf
					false,							// draggable
					false,							// singleClickExpand
					"delta",
					$planid,
					null,
					null,
					$semesterID
				);
		$viewNode[] = new TreeNode(
					$key.".respChanges",					// id - autom. generated
					"Änderungen (eigene)",			// text	for the node
					'respChanges' . '-node',		// iconCls
					true,							// leaf
					false,							// draggable
					false,							// singleClickExpand
					"respChanges",
					$planid,
					null,
					null,
					$semesterID
				);
		return $viewNode;
	}

	private function LehrplanView($key, $planid, $semesterID)
	{
		$viewNode = array();

		$viewNode[] = new TreeNode(
					$key.".doz",					// id - autom. generated
					"Dozent",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"doz",
					$planid,
					null,
					$this->getStundenplanDoz($key.".doz", $planid, $semesterID)
,					$semesterID
				);
		$viewNode[] = new TreeNode(
					$key.".clas",					// id - autom. generated
					"Semester",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"clas",
					$planid,
					null,
					$this->getStundenplanClass($key.".clas", $planid, $semesterID),
					$semesterID
				);
		return $viewNode;
	}

	private function getStundenplanDoz($key, $planid, $semesterID)
	{
		$treeNode = array();
		$childNodes = array();

		$arr = $this->getStundenplanDozData($planid, $semesterID);

		foreach($arr as $k=>$value)
		{
			$childNodes = array();

			foreach($value as $childkey=>$childvalue)
			{
				$childNodes[] = new TreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"doz",
											NULL,
											$semesterID
											);
			}
			$treeNode[] = new TreeNode(
				$key.".".$k,							// id - autom. generated
				$k,							// text	for the node
				'studiengang-root',			// iconCls
				false,							// leaf
				false,							// draggable
				true,							// singleClickExpand
				$k,
				$planid,
				null,
				$childNodes,
				$semesterID
			);
		}

		return $treeNode;
	}

	private function getStundenplanRoom($key, $planid, $semesterID)
	{
		$treeNode = array();
		$childNodes = array();

		$arr = $this->getStundenplanRoomData($planid, $semesterID);

		foreach($arr as $k=>$value)
		{
			$childNodes = array();
			foreach($value as $childkey=>$childvalue)
			{
				$childNodes[] = new TreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"room",
											NULL,
											$semesterID);
			}
			$treeNode[] = new TreeNode(
				$key.".".$k,					// id - autom. generated
				$k,								// text	for the node
				'studiengang-root',			// iconCls
				false,							// leaf
				false,							// draggable
				true,							// singleClickExpand
				$k,
				$planid,
				null,
				$childNodes,
				$semesterID
			);
		}
		return $treeNode;
	}

	private function getStundenplanClass($key, $planid, $semesterID)
	{
		$treeNode = array();
		$childNodes = array();

		$arr = $this->getStundenplanClassData($planid, $semesterID);

		foreach($arr as $k=>$value)
		{
			$childNodes = array();
			foreach($value as $childkey=>$childvalue)
			{
				$childNodes[] = new TreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"clas",
											NULL,
											$semesterID);
			}
			$treeNode[] = new TreeNode(
				$key.".".$k,					// id - autom. generated
				$k,								// text	for the node
				'studiengang-root',			// iconCls
				false,							// leaf
				false,							// draggable
				true,							// singleClickExpand
				$k,
				$planid,
				null,
				$childNodes,
				$semesterID
			);
		}
		return $treeNode;
	}

	private function getStundenplanClassData($planid, $semesterID)
	{
		$classesquery = "SELECT DISTINCT classes.gpuntisID AS cid, " .
						"classes.semester AS semester, " .
						"classes.major AS department, " .
						"classes.name AS oname, " .
						"'lesson' AS otype, " .
						"classes.manager AS manager, " .
						"count(lesson_classes.lessonID) AS lessonamount " .
						"FROM #__thm_organizer_classes AS classes " .
						"INNER JOIN #__thm_organizer_lesson_classes AS lesson_classes " .
					 	"ON classes.id = lesson_classes.classID " .
					 	"INNER JOIN #__thm_organizer_lessons " .
					 	"ON lesson_classes.lessonID = #__thm_organizer_lessons.id " .
					 	"WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
					 	"AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
						"GROUP BY classes.id";

		$classesarray = array( );
		$res          = $this->JDA->query( $classesquery );
		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				$key = $data->cid;
				if ( !isset( $classesarray[ $data->department ] ) ) {
					$classesarray[ $data->department ] = array( );
				}
				$classesarray[ $data->department ][ $key ]                   = array( );
				$classesarray[ $data->department ][ $key ][ "id" ]           = $key;
				$classesarray[ $data->department ][ $key ][ "department" ]   = $data->department;
				$classesarray[ $data->department ][ $key ][ "shortname" ]    = $data->oname;
				$classesarray[ $data->department ][ $key ][ "otype" ]        = $data->otype;
				$classesarray[ $data->department ][ $key ][ "name" ]         = $data->semester;
				$classesarray[ $data->department ][ $key ][ "manager" ]      = $data->manager;
				$classesarray[ $data->department ][ $key ][ "lessonamount" ] = $data->lessonamount;
				$classesarray[ $data->department ][ $key ][ "gpuntisID" ] = $data->cid;
				$classesarray[ $data->department ][ $key ][ "semesterID" ] = $semesterID;
				$classesarray[ $data->department ][ $key ][ "plantypeID" ] = $planid;
			}
		}

		$res = $this->getVirtualSchedules("clas", $semesterID);
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $classesarray[ $data->department ] ) ) {
					$classesarray[ $data->department ] = array( );
				}
				if ( !isset( $classesarray[ $data->department ][ $data->vid ] ) ) {
					$classesarray[ $data->department ][ $data->vid ] = array( );
				}
				$classesarray[ $data->department ][ $data->vid ][ "id" ]         = $data->vid;
				$classesarray[ $data->department ][ $data->vid ][ "department" ] = $data->department;
				$classesarray[ $data->department ][ $data->vid ][ "shortname" ]  = $data->vname;
				$classesarray[ $data->department ][ $data->vid ][ "otype" ]      = $data->vtype;
				$classesarray[ $data->department ][ $data->vid ][ "name" ]       = $data->vname;
				$classesarray[ $data->department ][ $data->vid ][ "manager" ]    = $data->vresponsible;
				if ( !isset( $classesarray[ $data->department ][ $data->vid ][ "elements" ] ) )
				  $classesarray[ $data->department ][ $data->vid ][ "elements" ] = array( );
				$classesarray[ $data->department ][ $data->vid ][ "elements" ][ $data->eid ] = $data->eid;
				if ( !isset( $classesarray[ $data->department ][ $data->vid ][ "lessonamount" ] ) )
			      $classesarray[ $data->department ][ $data->vid ][ "lessonamount" ] = 0;
				$classesarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $classesarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountClassLessons( $data->eid, $semesterID );
			}
		}
		return $classesarray;
	}

	private function getStundenplanRoomData($planid, $semesterID)
	{
		$roomquery = "SELECT DISTINCT rooms.gpuntisID AS rid, " .
					 "rooms.capacity, " .
					 "rooms.type as rtype, " .
					 "#__thm_organizer_departments.name AS department, " .
					 "rooms.name AS oname, " .
					 "'room' AS otype, " .
					 "rooms.manager, " .
					 "count(lesson_times.lessonID) AS lessonamount " .
					 "FROM #__thm_organizer_rooms AS rooms " .
					 "INNER JOIN #__thm_organizer_departments " .
					 "ON #__thm_organizer_departments.id = rooms.departmentID " .
					 "INNER JOIN #__thm_organizer_lesson_times AS lesson_times " .
					 "ON rooms.id = lesson_times.roomID " .
					 "INNER JOIN #__thm_organizer_lessons " .
					 "ON lesson_times.lessonID = #__thm_organizer_lessons.id " .
					 "WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
					 "AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
					 "GROUP BY rooms.id";

		$roomarray = array( );
		$res       = $this->JDA->query( $roomquery );
		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				$key  = $data->department . "-" . $data->rtype;
				$roomid = $data->rid;
				if ( !isset( $roomarray[ $key ] ) ) {
					$roomarray[ $key ] = array( );
				}
				$roomarray[ $key ][ $roomid ]                   = array( );
				$roomarray[ $key ][ $roomid ][ "id" ]           = $roomid;
				$roomarray[ $key ][ $roomid ][ "department" ]   = $data->department;
				$roomarray[ $key ][ $roomid ][ "name" ]         = $data->oname;
				$roomarray[ $key ][ $roomid ][ "otype" ]        = $data->otype;
				$roomarray[ $key ][ $roomid ][ "rtype" ]        = $data->rtype;
				$roomarray[ $key ][ $roomid ][ "capacity" ]     = $data->capacity;
				$roomarray[ $key ][ $roomid ][ "manager" ]      = $data->manager;
				$roomarray[ $key ][ $roomid ][ "lessonamount" ] = $data->lessonamount;
				$roomarray[ $key ][ $roomid ][ "gpuntisID" ] = $data->rid;
				$roomarray[ $key ][ $roomid ][ "semesterID" ] = $semesterID;
				$roomarray[ $key ][ $roomid ][ "plantypeID" ] = $planid;
			}
		}

		$res = $this->getVirtualSchedules("room", $semesterID);

		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $roomarray[ $data->department ] ) ) {
					$roomarray[ $data->department ] = array( );
				}
				if ( !isset( $roomarray[ $data->department ][ $data->vid ] ) ) {
					$roomarray[ $data->department ][ $data->vid ] = array( );
				}
				$roomarray[ $data->department ][ $data->vid ][ "id" ]         = $data->vid;
				$roomarray[ $data->department ][ $data->vid ][ "department" ] = $data->department;
				$roomarray[ $data->department ][ $data->vid ][ "name" ]       = $data->vname;
				$roomarray[ $data->department ][ $data->vid ][ "otype" ]      = $data->vtype;
				$rtype                                                        = explode( '-', $data->department );
				$roomarray[ $data->department ][ $data->vid ][ "rtype" ]      = $rtype[ 1 ];
				$roomarray[ $data->department ][ $data->vid ][ "manager" ]    = $data->vresponsible;
				if ( !isset( $roomarray[ $data->department ][ $data->vid ][ "elements" ] ) )
					$roomarray[ $data->department ][ $data->vid ][ "elements" ] = array( );
				$roomarray[ $data->department ][ $data->vid ][ "elements" ][ $data->eid ] = $data->eid;
				if ( !isset( $roomarray[ $data->department ][ $data->vid ][ "lessonamount" ] ) )
					$roomarray[ $data->department ][ $data->vid ][ "lessonamount" ] = 0;
				$roomarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $roomarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountRoomLessons( $data->eid, $semesterID );
			}
		}

		return $roomarray;
	}

	private function getStundenplanDozData($planid, $semesterID)
	{
		$teacherquery = "SELECT DISTINCT teachers.gpuntisID AS tid, " .
						"departments.name AS department, " .
						"teachers.name, " .
						"'teacher' AS otype, " .
						"teachers.manager, " .
						"count(lesson_teacher.lessonID) AS lessonamount " .
						"FROM #__thm_organizer_teachers AS teachers " .
						"INNER JOIN #__thm_organizer_departments AS departments " .
						"ON teachers.departmentID = departments.id " .
						"INNER JOIN #__thm_organizer_lesson_teachers AS lesson_teacher " .
						"ON teachers.id = lesson_teacher.teacherID " .
						"INNER JOIN #__thm_organizer_lessons " .
						"ON lesson_teacher.lessonID = #__thm_organizer_lessons.id " .
						"WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
					 	"AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
						"GROUP BY teachers.id";

		$teacherarray = array( );
		$res          = $this->JDA->query( $teacherquery );

		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				$key = $data->tid;
				if ( !isset( $teacherarray[ $data->department ] ) ) {
					$teacherarray[ $data->department ] = array( );
				}
				$teacherarray[ $data->department ][ $key ]                   = array( );
				$teacherarray[ $data->department ][ $key ][ "id" ]           = $key;
				$teacherarray[ $data->department ][ $key ][ "department" ]   = $data->department;
				$teacherarray[ $data->department ][ $key ][ "name" ]         = $data->name;
				$teacherarray[ $data->department ][ $key ][ "otype" ]        = $data->otype;
				$teacherarray[ $data->department ][ $key ][ "manager" ]      = $data->manager;
				$teacherarray[ $data->department ][ $key ][ "lessonamount" ] = $data->lessonamount;
				$teacherarray[ $data->department ][ $key ][ "gpuntisID" ] = $data->tid;
				$teacherarray[ $data->department ][ $key ][ "semesterID" ] = $semesterID;
				$teacherarray[ $data->department ][ $key ][ "plantypeID" ] = $planid;
			}
		}

		$res = $this->getVirtualSchedules("doz", $semesterID);

		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $teacherarray[ $data->department ] ) ) {
					$teacherarray[ $data->department ] = array( );
				}
				if ( !isset( $teacherarray[ $data->department ][ $data->vid ] ) ) {
					$teacherarray[ $data->department ][ $data->vid ] = array( );
				}
				$teacherarray[ $data->department ][ $data->vid ][ "id" ]         = $data->vid;
				$teacherarray[ $data->department ][ $data->vid ][ "department" ] = $data->department;
				$teacherarray[ $data->department ][ $data->vid ][ "name" ]       = $data->vname;
				$teacherarray[ $data->department ][ $data->vid ][ "otype" ]      = $data->vtype;
				$teacherarray[ $data->department ][ $data->vid ][ "manager" ]    = $data->vresponsible;
				if ( !isset( $teacherarray[ $data->department ][ $data->vid ][ "elements" ] ) )
					$teacherarray[ $data->department ][ $data->vid ][ "elements" ] = array( );
				$teacherarray[ $data->department ][ $data->vid ][ "elements" ][ $data->eid ] = $data->eid;

				if ( !isset( $teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] ) )
					$teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] = 0;
				$teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountTeacherLessons( $data->eid, $semesterID );
			}
		}

		return $teacherarray;
	}

	private function getVirtualSchedules($type, $semesterID)
	{
		$vsquery = "SELECT DISTINCT vs.vid, vname, vtype, department, vresponsible, eid
	         FROM #__thm_organizer_virtual_schedules as vs
	         INNER JOIN #__thm_organizer_virtual_schedules_elements as vse
	         ON vs.vid = vse.vid AND vs.sid = vse.sid
	         WHERE vtype = '" . $type . "' AND vs.sid = '" . $semesterID . "'";
		$res     = $this->JDA->query( $vsquery );

		return $res;
	}

	private function getCountTeacherLessons( $resourcename, $fachsemester )
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lessonperiods " . " WHERE tid = '" . $resourcename . "' AND sid = '" . $fachsemester . "'";
		$hits  = $this->JDA->query( $query );
		return count( $hits );
	}

	private function getCountRoomLessons( $resourcename, $fachsemester )
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lessonperiods " . " WHERE rid = '" . $resourcename . "' AND sid = '" . $fachsemester . "'";
		$hits  = $this->JDA->query( $query );
		return count( $hits );
	}

	private function getCountClassLessons( $resourcename, $fachsemester )
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lessons " . " WHERE cid = '" . $resourcename . "' AND sid = '" . $fachsemester . "'";
		$hits  = $this->JDA->query( $query );
		return count( $hits );
	}

}
?>