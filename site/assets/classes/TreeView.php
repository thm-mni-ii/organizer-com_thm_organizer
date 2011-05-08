<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/TreeNode.php");

class TreeView
{
	private $JDA = null;
	private $cfg = null;
	private $type = null;
	private $checked = null;
	private $hideCheckBox = null;
	private $inTree = array();

	function __construct($JDA, $CFG, $options = array())
	{
		$this->JDA = $JDA;
		$this->cfg = $CFG->getCFG();
		if(isset($options["path"]))
		{
			$this->checked = $options["path"];
		}
		else
		{
			$this->checked = null;
		}
		if(isset($options["hide"]))
		{
			$this->hideCheckBox = $options["hide"];
		}
		else
		{
			$this->hideCheckBox = false;
		}
	}

	private function createTreeNode($id,
		    						$text,
		    						$iconCls,
		    						$leaf,
		    						$draggable,
		    						$singleClickExpand,
		    						$gpuntisID,
		    						$plantype,
		    						$type,
		    						$children,
		    						$semesterID)
	{

		$checked = null;
		$treeNode = null;

		if($this->hideCheckBox == true)
		{
			$checked = null;
		}
		else
		{
			if($this->checked != null)
			{
				if(array_search($id, $this->checked) !== false)
					$checked = true;
				else
					$checked = false;
			}
			else
			{
				$checked = false;
			}
		}

		if($this->hideCheckBox == true)
		{
			if(array_search($id, $this->checked) !== false)
			{
				$treeNode = new TreeNode(
						$id,							// id - autom. generated
						$text,							// text	for the node
						$iconCls,			// iconCls
						$leaf,								// leaf
						$draggable,								// draggable
						$singleClickExpand,								// singleClickExpand
						$gpuntisID,							// key
						$plantype,								// plantype
						$type,								// type
						$children,
						$semesterID,
						$checked
					);
					$this->inTree[] = $gpuntisID;
			}
		}
		else
			$treeNode = new TreeNode(
						$id,							// id - autom. generated
						$text,							// text	for the node
						$iconCls,			// iconCls
						$leaf,								// leaf
						$draggable,								// draggable
						$singleClickExpand,								// singleClickExpand
						$gpuntisID,							// key
						$plantype,								// plantype
						$type,								// type
						$children,
						$semesterID,
						$checked
					);

		if($treeNode == null)
			return $children;
		return $treeNode;
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
			$temp = $this->createTreeNode(
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
			if($temp != null)
				$semesterJahrNode[] = $temp;

			$treeData["clas"] = array_merge( $treeData["clas"], $this->getStundenplanClassData(1, $value->id) );
			$treeData["room"] = array_merge( $treeData["room"], $this->getStundenplanRoomData(1, $value->id) );
			$treeData["doz"] = array_merge( $treeData["doz"], $this->getStundenplanDozData(1, $value->id) );
		}

		$this->expandSingleNode(& $semesterJahrNode);

		return array("success"=>true,"data"=>array("tree"=>$semesterJahrNode,"treeData"=>$treeData));
	}

	private function expandSingleNode(& $arr)
	{
		foreach($arr as $k=>$v)
		{
			if(!isset($v->children))
				$this->expandSingleNode(& $v);
			else if(is_array($v->children))
			{
				$v->expanded = true;

				if(count($v->children) > 1 )
					return;

				$this->expandSingleNode(& $v->children);
			}
		}
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
			$temp = $this->createTreeNode(
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
			if($temp != null)
				$plantypeNode[] = $temp;
		}
		return $plantypeNode;
	}

	private function StundenplanView($key, $planid, $semesterID)
	{
		$viewNode = array();
		$temp = $this->createTreeNode(
					$key.".doz",							// id - autom. generated
					"Dozent",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"doz",
					$planid,
					null,
					$this->getStundenplan($key.".doz", $planid, $semesterID, "doz"),
					$semesterID
				);
		if($temp != null)
			$viewNode[] = $temp;
		$temp = $this->createTreeNode(
					$key.".room",							// id - autom. generated
					"Raum",							// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"room",
					$planid,
					null,
					$this->getStundenplan($key.".room", $planid, $semesterID, "room"),
					$semesterID
				);
		if($temp != null)
			$viewNode[] = $temp;
		$temp = $this->createTreeNode(
					$key.".clas",							// id - autom. generated
					"Semester",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"clas",
					$planid,
					null,
					$this->getStundenplan($key.".clas", $planid, $semesterID, "clas"),
					$semesterID
				);
		if($temp != null)
			$viewNode[] = $temp;
		$temp = $this->createTreeNode(
					$key.".delta",					// id - autom. generated
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
		if($temp != null)
			$viewNode[] = $temp;
		$temp = $this->createTreeNode(
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
		if($temp != null)
			$viewNode[] = $temp;
		return $viewNode;
	}

	private function LehrplanView($key, $planid, $semesterID)
	{
		$viewNode = array();

		$temp = $this->createTreeNode(
					$key.".doz",					// id - autom. generated
					"Dozent",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"doz",
					$planid,
					null,
					$this->getStundenplan($key.".doz", $planid, $semesterID, "doz")
,					$semesterID
				);
		if($temp != null)
			$viewNode[] = $temp;
		$temp = $this->createTreeNode(
					$key.".clas",					// id - autom. generated
					"Semester",						// text	for the node
					'view' . '-root',				// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					"clas",
					$planid,
					null,
					$this->getStundenplan($key.".clas", $planid, $semesterID, "clas"),
					$semesterID
				);
		if($temp != null)
			$viewNode[] = $temp;
		return $viewNode;
	}

	private function getStundenplan($key, $planid, $semesterID, $type)
	{
		$treeNode = array();
		$childNodes = array();
		$arr = array();

		if($type == "doz")
			$arr = $this->getStundenplanDozData($planid, $semesterID);
		else if($type == "room")
			$arr = $this->getStundenplanRoomData($planid, $semesterID);
		else
			$arr = $this->getStundenplanClassData($planid, $semesterID);

		foreach($arr as $k=>$value)
		{
			$childNodes = array();

			foreach($value as $childkey=>$childvalue)
			{
				if(!isset($childvalue["gpuntisID"]))
				{
					$childvalue["gpuntisID"] = $childvalue["id"];
				}

				if($k == null)
				{
					$temp = $this->createTreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											$type,
											NULL,
											$semesterID
											);
					if($temp != null)
						$treeNode[] = $temp;
				}
				else
				{
					$temp = $this->createTreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											$type,
											NULL,
											$semesterID
											);
					if($temp != null)
						$childNodes[] = $temp;
				}
			}
			if($k != null)
			{
				$temp = $this->createTreeNode(
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
					if($temp != null)
						$treeNode[] = $temp;
			}
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
				if(!isset($childvalue["gpuntisID"]))
				{
					$childvalue["gpuntisID"] = $childvalue["id"];
				}

				if($k == null)
					$treeNode[] = $this->createTreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"room",
											NULL,
											$semesterID
											);
				else
					$childNodes[] = $this->createTreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"room",
											NULL,
											$semesterID
					);
			}
			if($k != null)
				$treeNode[] = $this->createTreeNode(
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
				if(!isset($childvalue["gpuntisID"]))
				{
					$childvalue["gpuntisID"] = $childvalue["id"];
				}

				if($k == null)
					$treeNode[] = $this->createTreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"clas",
											NULL,
											$semesterID
											);
				else
					$childNodes[] = $this->createTreeNode($key.".".$k.".".$childvalue["id"],
											$childvalue["name"],
											"leaf" . "-node",
											true,
											true,
											false,
											$childvalue["gpuntisID"],
											$planid,
											"clas",
											NULL,
											$semesterID
											);
			}
			if($k != null)
				$treeNode[] = $this->createTreeNode(
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
				$classesarray[ $data->department ][ $key ][ "id" ]           = trim($key);
				$classesarray[ $data->department ][ $key ][ "department" ]   = trim($data->department);
				$classesarray[ $data->department ][ $key ][ "shortname" ]    = trim($data->oname);
				$classesarray[ $data->department ][ $key ][ "otype" ]        = trim($data->otype);
				$classesarray[ $data->department ][ $key ][ "name" ]         = trim($data->semester);
				$classesarray[ $data->department ][ $key ][ "manager" ]      = trim($data->manager);
				$classesarray[ $data->department ][ $key ][ "lessonamount" ] = trim($data->lessonamount);
				$classesarray[ $data->department ][ $key ][ "gpuntisID" ] = trim($data->cid);
				$classesarray[ $data->department ][ $key ][ "semesterID" ] = trim($semesterID);
				$classesarray[ $data->department ][ $key ][ "plantypeID" ] = trim($planid);
				if(in_array($key, $this->inTree))
					$classesarray[ $data->department ][ $key ][ "treeLoaded" ] = true;
				else
					$classesarray[ $data->department ][ $key ][ "treeLoaded" ] = false;
			}
		}

		$res = $this->getVirtualSchedules("class", $semesterID);

		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $classesarray[ $data->department ] ) ) {
					$classesarray[ $data->department ] = array( );
				}
				if ( !isset( $classesarray[ $data->department ][ $data->vid ] ) ) {
					$classesarray[ $data->department ][ $data->vid ] = array( );
				}
				$classesarray[ $data->department ][ $data->vid ][ "id" ]         = trim($data->vid);
				$classesarray[ $data->department ][ $data->vid ][ "department" ] = trim($data->department);
				$classesarray[ $data->department ][ $data->vid ][ "shortname" ]  = trim($data->vname);
				$classesarray[ $data->department ][ $data->vid ][ "otype" ]      = trim($data->vtype);
				$classesarray[ $data->department ][ $data->vid ][ "name" ]       = trim($data->vname);
				$classesarray[ $data->department ][ $data->vid ][ "manager" ]    = trim($data->vresponsible);
				if ( !isset( $classesarray[ $data->department ][ $data->vid ][ "elements" ] ) )
				  $classesarray[ $data->department ][ $data->vid ][ "elements" ] = array( );
				$classesarray[ $data->department ][ $data->vid ][ "elements" ][ $data->eid ] = trim($data->eid);
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
					 "#__thm_organizer_descriptions.category as rtype," .
					 "#__thm_organizer_descriptions.description as description, " .
					 "rooms.name AS oname, " .
					 "'room' AS otype, " .
					 "rooms.manager, " .
					 "count(lesson_times.lessonID) AS lessonamount " .
					 "FROM #__thm_organizer_rooms AS rooms " .
					 "INNER JOIN #__thm_organizer_lesson_times AS lesson_times " .
					 "ON rooms.id = lesson_times.roomID " .
					 "INNER JOIN #__thm_organizer_lessons " .
					 "ON lesson_times.lessonID = #__thm_organizer_lessons.id " .
					 "INNER JOIN #__thm_organizer_descriptions " .
					 "ON #__thm_organizer_descriptions.id = rooms.descriptionID " .
					 "WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
					 "AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
					 "GROUP BY rooms.id";

		$roomarray = array( );
		$res       = $this->JDA->query( $roomquery );
		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];

				if(strlen($data->description) == 0 || $data->description == null)
					$key  = $data->rtype;
				else
					$key  = $data->rtype." (".$data->description.")";
				$roomid = $data->rid;
				if ( !isset( $roomarray[ $key ] ) ) {
					$roomarray[ $key ] = array( );
				}
				$roomarray[ $key ][ $roomid ]                   = array( );
				$roomarray[ $key ][ $roomid ][ "id" ]           = trim($roomid);
				$roomarray[ $key ][ $roomid ][ "description" ]   = trim($data->description);
				$roomarray[ $key ][ $roomid ][ "name" ]         = trim($data->oname);
				$roomarray[ $key ][ $roomid ][ "otype" ]        = trim($data->otype);
				$roomarray[ $key ][ $roomid ][ "rtype" ]        = trim($data->rtype);
				$roomarray[ $key ][ $roomid ][ "capacity" ]     = trim($data->capacity);
				$roomarray[ $key ][ $roomid ][ "manager" ]      = trim($data->manager);
				$roomarray[ $key ][ $roomid ][ "lessonamount" ] = trim($data->lessonamount);
				$roomarray[ $key ][ $roomid ][ "gpuntisID" ] = trim($data->rid);
				$roomarray[ $key ][ $roomid ][ "semesterID" ] = trim($semesterID);
				$roomarray[ $key ][ $roomid ][ "plantypeID" ] = trim($planid);

				if(in_array($roomid, $this->inTree))
					$roomarray[ $key ][ $roomid ][ "treeLoaded" ] = true;
				else
					$roomarray[ $key ][ $roomid ][ "treeLoaded" ] = false;
			}
		}

		$res = $this->getVirtualSchedules("room", $semesterID);

		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];

				if(strlen($data->description) == 0 || $data->description == null)
					$key  = $data->rtype;
				else
					$key  = $data->rtype." (".$data->description.")";

				if ( !isset( $roomarray[ $key ] ) ) {
					$roomarray[ $key ] = array( );
				}
				if ( !isset( $roomarray[ $key ][ $data->vid ] ) ) {
					$roomarray[ $key ][ $data->vid ] = array( );
				}
				$roomarray[ $key ][ $data->vid ][ "id" ]         = trim($data->vid);
				$roomarray[ $key ][ $data->vid ][ "description" ] = trim($data->description);
				$roomarray[ $key ][ $data->vid ][ "name" ]       = trim($data->vname);
				$roomarray[ $key ][ $data->vid ][ "otype" ]      = trim($data->vtype);
				$roomarray[ $key ][ $data->vid ][ "rtype" ]      =  trim($data->rtype);

				$roomarray[ $key ][ $data->vid ][ "manager" ]    = trim($data->vresponsible);
				if ( !isset( $roomarray[ $key ][ $data->vid ][ "elements" ] ) )
					$roomarray[ $key ][ $data->vid ][ "elements" ] = array( );
				$roomarray[ $key ][ $data->vid ][ "elements" ][ $data->eid ] = trim($data->eid);
				if ( !isset( $roomarray[ $key ][ $data->vid ][ "lessonamount" ] ) )
					$roomarray[ $keyt ][ $data->vid ][ "lessonamount" ] = 0;
				$roomarray[ $key ][ $data->vid ][ "lessonamount" ] = $roomarray[ $key ][ $data->vid ][ "lessonamount" ] + $this->getCountRoomLessons( $data->eid, $semesterID );
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
						"teachers.username as manager, " .
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
				$teacherarray[ $data->department ][ $key ][ "id" ]           = trim($key);
				$teacherarray[ $data->department ][ $key ][ "department" ]   = trim($data->department);
				$teacherarray[ $data->department ][ $key ][ "name" ]         = trim($data->name);
				$teacherarray[ $data->department ][ $key ][ "otype" ]        = trim($data->otype);
				$teacherarray[ $data->department ][ $key ][ "manager" ]      = trim($data->manager);
				$teacherarray[ $data->department ][ $key ][ "lessonamount" ] = trim($data->lessonamount);
				$teacherarray[ $data->department ][ $key ][ "gpuntisID" ] = trim($data->tid);
				$teacherarray[ $data->department ][ $key ][ "semesterID" ] = trim($semesterID);
				$teacherarray[ $data->department ][ $key ][ "plantypeID" ] = trim($planid);

				if(in_array($key, $this->inTree))
					$teacherarray[ $data->department ][ $key ][ "treeLoaded" ] = true;
				else
					$teacherarray[ $data->department ][ $key ][ "treeLoaded" ] = false;
			}
		}

		$res = $this->getVirtualSchedules("teacher", $semesterID);

		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $teacherarray[ $data->department ] ) ) {
					$teacherarray[ $data->department ] = array( );
				}
				if ( !isset( $teacherarray[ $data->department ][ $data->vid ] ) ) {
					$teacherarray[ $data->department ][ $data->vid ] = array( );
				}
				$teacherarray[ $data->department ][ $data->vid ][ "id" ]         = trim($data->vid);
				$teacherarray[ $data->department ][ $data->vid ][ "department" ] = trim($data->department);
				$teacherarray[ $data->department ][ $data->vid ][ "name" ]       = trim($data->vname);
				$teacherarray[ $data->department ][ $data->vid ][ "otype" ]      = trim($data->vtype);
				$teacherarray[ $data->department ][ $data->vid ][ "manager" ]    = trim($data->vresponsible);
				if ( !isset( $teacherarray[ $data->department ][ $data->vid ][ "elements" ] ) )
					$teacherarray[ $data->department ][ $data->vid ][ "elements" ] = array( );
				$teacherarray[ $data->department ][ $data->vid ][ "elements" ][ $data->eid ] = trim($data->eid);

				if ( !isset( $teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] ) )
					$teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] = 0;
				$teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountTeacherLessons( $data->eid, $semesterID );
			}
		}

		return $teacherarray;
	}

	private function getVirtualSchedules($type, $semesterID)
	{
		$vsquery = "SELECT DISTINCT vs.vid, vname, vtype, IF(CHAR_LENGTH(#__thm_organizer_departments.subdepartment) = 0,#__thm_organizer_departments.department,CONCAT(#__thm_organizer_departments.department, '-', #__thm_organizer_departments.subdepartment)) as department, vresponsible, eid
	         FROM #__thm_organizer_virtual_schedules as vs
	         INNER JOIN #__thm_organizer_virtual_schedules_elements as vse
	         ON vs.vid = vse.vid AND vs.sid = vse.sid
	         LEFT JOIN #__thm_organizer_departments
	         ON #__thm_organizer_departments.id = vs.department
	         WHERE vtype = '" . $type . "' AND vs.sid = " . $semesterID ;

		$res     = $this->JDA->query( $vsquery );

		return $res;
	}

	private function getCountTeacherLessons( $resourcename, $fachsemester )
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lesson_teachers INNER JOIN #__thm_organizer_teachers ON teacherID = #__thm_organizer_teachers.id INNER JOIN #__thm_organizer_lessons ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " . " WHERE #__thm_organizer_teachers.gpuntisID = '" . $resourcename . "' AND semesterID = '" . $fachsemester . "' AND plantypeID = 1";
		$hits  = $this->JDA->query( $query );
		return count( $hits );
	}

	private function getCountRoomLessons( $resourcename, $fachsemester )
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lesson_times INNER JOIN #__thm_organizer_rooms ON roomID = #__thm_organizer_rooms.id INNER JOIN #__thm_organizer_lessons ON #__thm_organizer_lesson_times.lessonID = #__thm_organizer_lessons.id " . " WHERE #__thm_organizer_rooms.gpuntisID = '" . $resourcename . "' AND semesterID = '" . $fachsemester . "' AND plantypeID = 1";
		$hits  = $this->JDA->query( $query );
		return count( $hits );
	}

	private function getCountClassLessons( $resourcename, $fachsemester )
	{
		$query = "SELECT * " . " FROM #__thm_organizer_lesson_classes INNER JOIN #__thm_organizer_classes ON classID = #__thm_organizer_classes.id INNER JOIN #__thm_organizer_lessons ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_lessons.id " . " WHERE #__thm_organizer_classes.gpuntisID = '" . $resourcename . "' AND semesterID = '" . $fachsemester . "' AND plantypeID = 1";
		$hits  = $this->JDA->query( $query );
		return count( $hits );
	}

}
?>