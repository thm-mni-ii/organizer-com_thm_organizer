<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_COMPONENT."/assets/classes/TreeNode.php");

class TreeView
{
	private $JDA = null;
	private $cfg = null;
	private $type = null;
	private $sid = null;
	private $semDesc = null;


	function __construct($JDA, $CFG, $options = array())
	{
		if(isset($options["type"]))
			$this->type = $options["type"];
		else
			$this->type = $JDA->getRequest( "type" );
		$this->sid  = $JDA->getSemID();
		$this->JDA = $JDA;
		$this->cfg = $CFG->getCFG();
	}

	public function load()
	{
		$arr  = array( );
		if ( isset( $this->type ) ) {
			if ( $this->type == "clas" )
				$arr = $this->getClasses( "class", $this->sid );
			elseif ( $this->type == "room" )
				$arr = $this->getRooms( "room", $this->sid );
			elseif ( $this->type == "doz" )
				$arr = $this->getTeachers( "teacher", $this->sid );

			$treeNode = array();
			$childNodes = array();

			foreach($arr as $key=>$value)
			{
				$childNodes = array();
				foreach($value as $childkey=>$childvalue)
				{
					$childNodes[] = new TreeNode($childvalue["id"],
												$childvalue["name"],
												$this->type . "-node",
												true,
												true,
												false,
												NULL);
				}

				$treeNode[] = new TreeNode(
					$key,							// id - autom. generated
					$key,							// text	for the node
					$this->type . '-root',			// iconCls
					false,							// leaf
					false,							// draggable
					true,							// singleClickExpand
					$childNodes						// children
				);
			}

			$arr[ "type" ] = $this->type;
			return array("success"=>true,"data"=>array("tree"=>$treeNode,"treeData"=>$arr));
		} else {
			return array("success"=>false,"data"=>array());
		}
	}

	public function plantype()
	{

		$arr  = array( );
		$arr2 = array( );
		$arr3 = array( );

	    if ( isset( $this->type )) {
			if ( $this->type == "ptype" )
				$arr = $this->getCuriculumTeachers( "plantype", $this->sid );

			$arr3 = array ();
			$arr3["sid_l"] = $this->sid."_Lehrplan";

			$sem_Desc = $this->getSemDesc();

			$lernplanNode = array();
			$viewNodes = array();
			$semesterNode = array();

			$viewNodes = $this->curiculumTeachers($viewNodes);

			$viewNodes = $this->curiculumClasses($viewNodes);

		    $lehrplanNode[] = new TreeNode(
				$arr3["sid_l"],				// id - autom. generated
				'Lehrplan',							// text	for the node
				'lernplan-root',					// iconCls
				false,								// leaf
				false,								// draggable
				true,								// singleClickExpand
				$viewNodes							// children
			);
			$semesterNode[] = new TreeNode(
				$this->sid,				            // id - autom. generated
				$sem_Desc,							// text	for the node
				'semester-root',					// iconCls
				false,								// leaf
				false,								// draggable
				true,								// singleClickExpand
				$lehrplanNode							// children
			);

		$arr[ "type" ] = $this->type;
			return array("success"=>true,"data"=>array("tree"=>$semesterNode,"treeData"=>$arr));
		} else {
			return array("success"=>false,"data"=>array());
		}
	}

	public function curiculumTeachers($viewNodes)
	{
		if ( isset( $this->type )) {
			$arr = $this->getCuriculumTeachers( $this->sid );

			$arr2 = array ();
			$arr2["sid_l_d"] = $this->sid."_Lehrplan"."_Dozent";
			$arr2["sid_l_s"] = $this->sid."_Lehrplan"."_Semester";

			$treeNode = array();
			$childNodes = array();

					foreach($arr as $key=>$value)
					{

						$childNodes = array();

						foreach($value as $childkey=>$childvalue)
						{
							$childNodes[] = new TreeNode($childvalue["department_name"],
														$childvalue["teachers_name"],
														$this->type . "-node",
														true,
														true,
														false,
														NULL);
						}

						$treeNode[] = new TreeNode(
							$key,							// id - autom. generated
							$key,							// text	for the node
							$this->type . '-root',			// iconCls
							false,							// leaf
							false,							// draggable
							true,							// singleClickExpand
							$childNodes						// children
						);
					}


					{
						$viewNodes[] = new TreeNode(
							$arr2["sid_l_d"],							// id - autom. generated
							'Dozent',							// text	for the node
							'dozent-root',						// iconCls
							false,								// leaf
							false,								// draggable
							true,								// singleClickExpand
							$treeNode							// children
						);
					}

			$arr[ "type" ] = $this->type;
			return $viewNodes;
		} else {
			return array("success"=>false,"data"=>array());
		}
	}

	public function curiculumClasses($viewNodes)
	{
		if ( isset( $this->type )) {
			$arr = $this->getCuriculumClasses( $this->sid );

			$arr2 = array ();
			$arr2["sid_l_d"] = $this->sid."_Lehrplan"."_Dozent";
			$arr2["sid_l_s"] = $this->sid."_Lehrplan"."_Semester";

			$treeNode = array();
			$childNodes = array();

					foreach($arr as $key=>$value)
					{
						$childNodes = array();

						foreach($value as $childkey=>$childvalue)
						{
							$childNodes[] = new TreeNode($childvalue["department_name"],
														$childvalue["classes_name"],
														$this->type . "-node",
														true,
														true,
														false,
														NULL);
						}

						$treeNode[] = new TreeNode(
							$key,							// id - autom. generated
							$key,							// text	for the node
							$this->type . '-root',			// iconCls
							false,							// leaf
							false,							// draggable
							true,							// singleClickExpand
							$childNodes						// children
						);
					}

						$viewNodes[] = new TreeNode(
							$arr2["sid_l_s"],							// id - autom. generated
							'Semester',							// text	for the node
							'semester-root',					// iconCls
							false,								// leaf
							false,								// draggable
							true,								// singleClickExpand
							$treeNode							// children
						);


			$arr[ "type" ] = $this->type;
			return $viewNodes;
		} else {
			return array("success"=>false,"data"=>array());
		}
	}

	public function getSemDesc(){
		$semDescquery = "SELECT #__thm_organizer_semesters.semesterDesc AS sem_semDesc
				      FROM #__thm_organizer_semesters
				      WHERE #__thm_organizer_semesters.id = " . $this->sid;

		$res          = $this->JDA->query( $semDescquery );

		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {


			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];

				if ( !isset( $semDescarray[ $data->sem_semDesc] ) ) {
					$semDescarray[ $data->sem_semDesc] = array( );
				}

				$semDescarray[ $data->sem_semDesc]                       = array( );
            	$semDescarray[ $data->sem_semDesc][ "sem_semDesc" ]    = $data->sem_semDesc;
			}
		}
		$semDesc = $semDescarray[$data->sem_semDesc][ "sem_semDesc" ];

		return $semDesc;
	}

	private function getClasses()
	{
		$classesquery = "SELECT DISTINCT classes.gpuntisID AS cid, " .
						"classes.name AS semester, " .
						"#__thm_organizer_departments.name AS department, " .
						"classes.name AS oname, " .
						"'lesson' AS otype, " .
						"classes.manager AS manager, " .
						"count(lesson_classes.lessonID) AS lessonamount " .
						"FROM #__thm_organizer_classes AS classes " .
						"INNER JOIN #__thm_organizer_departments " .
						"ON #__thm_organizer_departments.id = classes.dptID " .
						"INNER JOIN #__thm_organizer_lesson_classes AS lesson_classes " .
					 	"ON classes.id = lesson_classes.classID " .
						"GROUP BY classes.id";

		$classesarray = array( );
		$res          = $this->JDA->query( $classesquery );
		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $classesarray[ $data->department ] ) ) {
					$classesarray[ $data->department ] = array( );
				}
				$classesarray[ $data->department ][ $data->cid ]                   = array( );
				$classesarray[ $data->department ][ $data->cid ][ "id" ]           = $data->cid;
				$classesarray[ $data->department ][ $data->cid ][ "department" ]   = $data->department;
				$classesarray[ $data->department ][ $data->cid ][ "shortname" ]    = $data->oname;
				$classesarray[ $data->department ][ $data->cid ][ "otype" ]        = $data->otype;
				$classesarray[ $data->department ][ $data->cid ][ "name" ]         = $data->semester;
				$classesarray[ $data->department ][ $data->cid ][ "manager" ]      = $data->manager;
				$classesarray[ $data->department ][ $data->cid ][ "lessonamount" ] = $data->lessonamount;
			}
		}

		$res = $this->getVirtualSchedules();
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
				$classesarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $classesarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountClassLessons( $data->eid, $this->sid );
			}
		}
		return $classesarray;
	}

	private function getRooms()
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
					 "ON #__thm_organizer_departments.id = rooms.dptID " .
					 "INNER JOIN #__thm_organizer_lessons_times AS lesson_times " .
					 "ON rooms.id = lesson_times.roomID " .
					 "GROUP BY rooms.id";

		$roomarray = array( );
		$res       = $this->JDA->query( $roomquery );
		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				$key  = $data->department . "-" . $data->rtype;
				if ( !isset( $roomarray[ $key ] ) ) {
					$roomarray[ $key ] = array( );
				}
				$roomarray[ $key ][ $data->rid ]                   = array( );
				$roomarray[ $key ][ $data->rid ][ "id" ]           = $data->rid;
				$roomarray[ $key ][ $data->rid ][ "department" ]   = $data->department;
				$roomarray[ $key ][ $data->rid ][ "name" ]         = $data->oname;
				$roomarray[ $key ][ $data->rid ][ "otype" ]        = $data->otype;
				$roomarray[ $key ][ $data->rid ][ "rtype" ]        = $data->rtype;
				$roomarray[ $key ][ $data->rid ][ "capacity" ]     = $data->capacity;
				$roomarray[ $key ][ $data->rid ][ "manager" ]      = $data->manager;
				$roomarray[ $key ][ $data->rid ][ "lessonamount" ] = $data->lessonamount;
			}
		}

		$res = $this->getVirtualSchedules();

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
				$roomarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $roomarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountRoomLessons( $data->eid, $this->sid );
			}
		}

		return $roomarray;
	}

	private function getTeachers()
	{
		$teacherquery = "SELECT DISTINCT teachers.gpuntisID AS tid, " .
						"departments.name AS department, " .
						"teachers.name, " .
						"'teacher' AS otype, " .
						"teachers.manager, " .
						"count(lesson_teacher.lessonID) AS lessonamount " .
						"FROM #__thm_organizer_teachers AS teachers " .
						"INNER JOIN #__thm_organizer_departments AS departments " .
						"ON teachers.dptID = departments.id " .
						"INNER JOIN #__thm_organizer_lesson_teachers AS lesson_teacher " .
						"ON teachers.id = lesson_teacher.teacherID " .
						"GROUP BY teachers.id";

		$teacherarray = array( );
		$res          = $this->JDA->query( $teacherquery );

		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];
				if ( !isset( $teacherarray[ $data->department ] ) ) {
					$teacherarray[ $data->department ] = array( );
				}
				$teacherarray[ $data->department ][ $data->tid ]                   = array( );
				$teacherarray[ $data->department ][ $data->tid ][ "id" ]           = $data->tid;
				$teacherarray[ $data->department ][ $data->tid ][ "department" ]   = $data->department;
				$teacherarray[ $data->department ][ $data->tid ][ "name" ]         = $data->name;
				$teacherarray[ $data->department ][ $data->tid ][ "otype" ]        = $data->otype;
				$teacherarray[ $data->department ][ $data->tid ][ "manager" ]      = $data->manager;
				$teacherarray[ $data->department ][ $data->tid ][ "lessonamount" ] = $data->lessonamount;
			}
		}

		$res = $this->getVirtualSchedules();

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
				$teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] = $teacherarray[ $data->department ][ $data->vid ][ "lessonamount" ] + $this->getCountTeacherLessons( $data->eid, $this->sid );
			}
		}

		return $teacherarray;
	}

	private function getVirtualSchedules()
	{
		$vsquery = "SELECT DISTINCT vs.vid, vname, vtype, department, vresponsible, eid
	         FROM #__thm_organizer_virtual_schedules as vs
	         INNER JOIN #__thm_organizer_virtual_schedules_elements as vse
	         ON vs.vid = vse.vid AND vs.sid = vse.sid
	         WHERE vtype = '" . $this->type . "' AND vs.sid = '" . $this->sid . "'";
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

	private function getCuriculumTeachers($fachsemester)
	{

		$curiculumTeachersquery = "SELECT #__thm_organizer_departments.id AS department_id,
						#__thm_organizer_departments.name AS department_name,
				        #__thm_organizer_teachers.id AS teachers_id,
				        #__thm_organizer_teachers.name AS teachers_name

					 	FROM #__thm_organizer_lessons
						INNER JOIN #__thm_organizer_plantype
						ON #__thm_organizer_lessons.plantypeID = #__thm_organizer_plantype.id

						INNER JOIN #__thm_organizer_lesson_teachers
						ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id

						INNER JOIN #__thm_organizer_teachers
						ON #__thm_organizer_teachers.id = #__thm_organizer_lesson_teachers.teacherID

						INNER JOIN #__thm_organizer_departments
						ON #__thm_organizer_departments.id = #__thm_organizer_teachers.dptID

						WHERE #__thm_organizer_lessons.semesterID = ". $fachsemester ."

						GROUP BY #__thm_organizer_teachers.id";

    	$curiculumTeachersarray = array( );

		$res          = $this->JDA->query( $curiculumTeachersquery );

		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {


			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];

				if ( !isset( $curiculumTeachersarray[ $data->department_name] ) ) {
					$curiculumTeachersarray[ $data->department_name] = array( );
				}

				$curiculumTeachersarray[ $data->department_name][ $data->teachers_name ]                       = array( );
				$curiculumTeachersarray[ $data->department_name][ $data->teachers_name ][ "department_id" ]    = $data->department_id;
				$curiculumTeachersarray[ $data->department_name][ $data->teachers_name ][ "department_name" ]  = $data->department_name;
				$curiculumTeachersarray[ $data->department_name][ $data->teachers_name ][ "teacher_id" ]       = $data->teachers_id;
				$curiculumTeachersarray[ $data->department_name][ $data->teachers_name ][ "teachers_name" ]    = $data->teachers_name;
			}
		}
		return $curiculumTeachersarray;
	}

	private function getCuriculumClasses($fachsemester)
	{

		$curiculumClassesquery = "SELECT #__thm_organizer_departments.id AS department_id,
						#__thm_organizer_departments.name AS department_name,
				        #__thm_organizer_classes.id AS classes_id,
				        #__thm_organizer_classes.name AS classes_name

					 	FROM #__thm_organizer_lessons
						INNER JOIN #__thm_organizer_plantype
						ON #__thm_organizer_lessons.plantypeID = #__thm_organizer_plantype.id

						INNER JOIN #__thm_organizer_lesson_classes
						ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_lessons.id

						INNER JOIN #__thm_organizer_classes
						ON #__thm_organizer_classes.id = #__thm_organizer_lesson_classes.classID

						INNER JOIN #__thm_organizer_departments
						ON #__thm_organizer_departments.id = #__thm_organizer_classes.dptID

						WHERE #__thm_organizer_lessons.semesterID = ". $fachsemester ."

						GROUP BY #__thm_organizer_classes.id";

    	$curiculumClassesarray = array( );

		$res          = $this->JDA->query( $curiculumClassesquery );

		if(is_array( $res ) === true)
		if ( count( $res ) != 0 ) {


			for ( $i = 0; $i < count( $res ); $i++ ) {
				$data = $res[ $i ];

				if ( !isset( $curiculumClassesarray[ $data->department_name] ) ) {
					$curiculumClassesarray[ $data->department_name] = array( );
				}

				$curiculumClassesarray[ $data->department_name][ $data->classes_name ]                       = array( );
				$curiculumClassesarray[ $data->department_name][ $data->classes_name ][ "department_id" ]    = $data->department_id;
				$curiculumClassesarray[ $data->department_name][ $data->classes_name ][ "department_name" ]  = $data->department_name;
				$curiculumClassesarray[ $data->department_name][ $data->classes_name ][ "classes_id" ]       = $data->classes_id;
				$curiculumClassesarray[ $data->department_name][ $data->classes_name ][ "classes_name" ]    = $data->classes_name;
			}
		}
		return $curiculumClassesarray;
	}
}
?>