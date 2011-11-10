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
  private $publicDefault = null;
  private $hideCheckBox = null;
  private $inTree = array();
  private $treeData = array();
  private $publicDefaultNode = null;

  public function __construct($JDA, $CFG, $options = array())
  {
    $this->JDA = $JDA;
    $this->cfg = $CFG->getCFG();
    if(isset($options["path"]))
    {
      $this->checked = (array)$options["path"];
    }
    else
    {
      $this->checked = null;
    }
    if(isset($options["publicDefault"]))
    {
      $this->publicDefault = (array)$options["publicDefault"];
    }
    else
    {
      $this->publicDefault = null;
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
    $publicDefault = null;
    $treeNode = null;

    if($this->hideCheckBox == true)
    {
      $checked = null;
    }
    else
    {
      if($this->checked != null)
      {

        if(isset($this->checked[$id]))
          $checked = $this->checked[$id];
        else
          $checked = "unchecked";
      }
      else
      {
        $checked = "unchecked";
      }
    }

    if($this->publicDefault != null && $leaf === true)
      {
      	if(isset($this->publicDefault[$id]))
      		$publicDefault = $this->publicDefault[$id];
      	else
      		$publicDefault = "notdefault";
      }
      	else if($leaf === true)
      		$publicDefault = "notdefault";

    if($this->hideCheckBox == true)
    {
      if($this->nodeStatus($id))
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
            $checked,
            $publicDefault
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
            $checked,
            $publicDefault
          );

    if($publicDefault === "default")
    {
    	$this->publicDefaultNode = new TreeNode(
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
							            $checked,
							            $publicDefault
							        );
    }

    if($treeNode == null)
      return $children;
    return $treeNode;
  }

  private function nodeStatus($id)
  {
  	if(isset($this->checked[$id]))
  	{
  		if($this->checked[$id] === "checked" || $this->checked[$id] === "intermediate")
			return true;
		else
			return false;
  	}
  	else
	  	foreach($this->checked as $checkedKey=>$checkedValue)
	  	{
			if(strpos($id, $checkedKey) !== false)
			{
				if($checkedValue === "selected" || $checkedValue === "intermediate")
				{
					return true;
				}
			}
	  	}
  	return false;
  }

  public function load()
  {
    $semesterJahrNode = array();
    $semesterarray = array();

    $semesterarray = $this->getSemester();


    $this->treeData["clas"] = array();
    $this->treeData["room"] = array();
    $this->treeData["doz"] = array();
    $this->treeData["subject"] = array();

    foreach($semesterarray as $key=>$value)
    {
      $temp = $this->createTreeNode(
          $value->id,							// id - autom. generated
          $value->semesterDesc,							// text	for the node
          'semesterjahr' . '-root',			// iconCls
          false,								// leaf
          false,								// draggable
          true,								// singleClickExpand
          $value->id,							// key
          null,								// plantype
          null,								// type
          null,
          $value->id
        );
	  $children = $this->plantype($value->id, $value->id);

      if($temp != null && !empty($temp))
      {
      	$temp->setChildren($children);
		$semesterJahrNode[] = $temp;
      }
	  else if(!empty($children))
	  {
	  	$semesterJahrNode[] = $children;
	  }
    }

    $this->expandSingleNode(& $semesterJahrNode);

    $semesterJahrNode = $this->treeCorrect($semesterJahrNode);

		echo "<pre>".print_r($this->treeData, true)."</pre>";
    return array("success"=>true,"data"=>array("tree"=>$semesterJahrNode,"treeData"=>$this->treeData, "treePublicDefault"=>$this->publicDefaultNode));
  }

  private function treeCorrect($node)
  {
  	$newNode = array();

  	foreach($node as $nodeElement)
  	{
  		if(is_array($nodeElement))
  		{
  			$newNode = $this->treeCorrect($nodeElement);
  		}
  		else
  		{
  			if(isset($nodeElement->children))
  				if(is_array($nodeElement->children))
  				{
  					$nodeElement->children = $this->treeCorrect($nodeElement->children);
  					$newNode[] = $nodeElement;
  				}
  				else
  					$newNode[] = $nodeElement;
  			else
  				$newNode[] = $nodeElement;
  		}
  	}

	return $newNode;
  }

  private function expandSingleNode(& $arr)
  {
    if(gettype($arr) !== "object" && gettype($arr) !== "array" )
    {
      return;
    }

    foreach($arr as $k=>$v)
    {
      if(!isset($v->children))
        $this->expandSingleNode(& $v);
      else if(is_array($v->children))
      {
        if(count($arr) > 1)
          return;

        $v->expanded = true;

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
    $plantypequery = "SELECT #__thm_organizer_plantypes.id ," .
            "#__thm_organizer_plantypes.plantype " .
            "FROM #__thm_organizer_plantypes";

    $plantypes       = $this->JDA->query( $plantypequery );

    foreach($plantypes as $k=>$v)
    {
      $plantype = JText::_($v->plantype);
      $key = $key.".".$v->id;
      $temp = $this->createTreeNode(
          $key,							// id - autom. generated
          $plantype,					// text	for the node
          "plantype" . '-root',			// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          $v->id,
          $v->id,
          null,
          null,
          $semesterID
        );
        if($v->id == 1)
      		$children = $this->StundenplanView($key, $v->id, $semesterID);
      	else if($v->id == 2)
      		$children = $this->LehrplanView($key, $v->id, $semesterID);
      if($temp != null && !empty($temp))
      {
      	$temp->setChildren($children);
        $plantypeNode[] = $temp;
      }
      else if(!empty($children))
      	$plantypeNode[] = $children;

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
          null,
          $semesterID
        );
    $children = $this->getStundenplan($key.".doz", $planid, $semesterID, "doz");
    if($temp != null && !empty($temp))
    {
    	$temp->setChildren($children);
      	$viewNode[] = $temp;
    }
    else if(!empty($children))
    	$viewNode[] = $children;

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
          null,
          $semesterID
        );

    $children = $this->getStundenplan($key.".room", $planid, $semesterID, "room");
    if($temp != null && !empty($temp))
    {
    	$temp->setChildren($children);
      	$viewNode[] = $temp;
    }
    else if(!empty($children))
    	$viewNode[] = $children;

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
          null,
          $semesterID
        );

    $children = $this->getStundenplan($key.".clas", $planid, $semesterID, "clas");
    if($temp != null && !empty($temp))
    {
    	$temp->setChildren($children);
      	$viewNode[] = $temp;
    }
    else if(!empty($children))
    	$viewNode[] = $children;

    $temp = $this->createTreeNode(
          $key.".subject",							// id - autom. generated
          "Fächer",						// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "subject",
          $planid,
          null,
          null,
          $semesterID
        );

    $children = $this->getStundenplan($key.".subject", $planid, $semesterID, "subject");
    if($temp != null && !empty($temp))
    {
    	$temp->setChildren($children);
      	$viewNode[] = $temp;
    }
    else if(!empty($children))
    	$viewNode[] = $children;

    $temp = $this->createTreeNode(
          $key.".delta",					// id - autom. generated
          "Änderungen (zentral)",			// text	for the node
          'delta' . '-node',				// iconCls
          true,							// leaf
          false,							// draggable
          false,							// singleClickExpand
          "delta",
          $planid,
          "delta",
          null,
          $semesterID
        );

    if($temp != null && !empty($temp))
    {
      	$viewNode[] = $temp;
    }

    $temp = $this->createTreeNode(
          $key.".respChanges",					// id - autom. generated
          "Änderungen (eigene)",			// text	for the node
          'respChanges' . '-node',		// iconCls
          true,							// leaf
          false,							// draggable
          false,							// singleClickExpand
          "respChanges",
          $planid,
          "respChanges",
          null,
          $semesterID
        );

    if($temp != null && !empty($temp))
    {
      	$viewNode[] = $temp;
    }

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
          null,
          $semesterID
        );
    $children = $this->getStundenplan($key.".doz", $planid, $semesterID, "doz");

    if($temp != null && !empty($temp))
    {
    	$temp->setChildren($children);
     	$viewNode[] = $temp;
    }
    else if(!empty($children))
    	$viewNode[] = $children;

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

    $children = $this->getStundenplan($key.".clas", $planid, $semesterID, "clas");

    if($temp != null && !empty($temp))
    {
    	$temp->setChildren($children);
     	$viewNode[] = $temp;
    }
    else if(!empty($children))
    	$viewNode[] = $children;
    return $viewNode;
  }

	private function getStundenplan($key, $planid, $semesterID, $type)
  {
    $treeNode = array();
    $childNodes = array();
    $datas = array();
    $dataArray = array();

    if($type == "doz")
      $datas = $this->getStundenplanDozData($planid, $semesterID);
    else if($type == "room")
      $datas = $this->getStundenplanRoomData($planid, $semesterID);
    else if($type == "clas")
      $datas = $this->getStundenplanClassData($planid, $semesterID);
    else
    	$datas = $this->getStundenplanSubjectData($planid, $semesterID);

     if(is_array( $datas ) === true)
    if ( count( $datas ) != 0 ) {
		$this->treeData[$type] = array_merge_recursive( $this->treeData[$type], $datas);
      for ( $i = 0; $i < count( $datas ); $i++ ) {
        $data = $datas[ $i ];
        $id = trim($data->id);
        $parent = trim($data->parent);
        if ( !isset( $dataArray[ $parent ] ) ) {
          $dataArray[ $parent ] = array( );
        }

		$dataArray[ $parent ][ $id ]                   = array( );
		$dataArray[ $parent ][ $id ][ "id" ]           = trim($id);
		$dataArray[ $parent ][ $id ][ "department" ]   = trim($parent);
		$dataArray[ $parent ][ $id ][ "shortname" ]    = trim($data->shortname);
		$dataArray[ $parent ][ $id ][ "type" ]        = trim($data->type);
		$dataArray[ $parent ][ $id ][ "name" ]         = trim($data->name);
		$dataArray[ $parent ][ $id ][ "lessonamount" ] = trim($data->lessonamount);
		$dataArray[ $parent ][ $id ][ "gpuntisID" ] = trim($data->gpuntisID);
		$dataArray[ $parent ][ $id ][ "semesterID" ] = trim($semesterID);
		$dataArray[ $parent ][ $id ][ "plantypeID" ] = trim($planid);

		if(in_array($key, $this->inTree))
			$dataArray[ $parent ][ $id ][ "treeLoaded" ] = true;
		else
			$dataArray[ $parent ][ $id ][ "treeLoaded" ] = false;
		}
	}

    foreach($dataArray as $dataKey=>$dataValue)
    {
      $childNodes = array();

      $nodeKey = str_replace(" ", "", $dataKey);
      $nodeKey = str_replace("(", "", $nodeKey);
      $nodeKey = str_replace(")", "", $nodeKey);

      foreach($dataValue as $childkey=>$childvalue)
      {
        if($childvalue["lessonamount"] == "0")
          continue;
        if(!isset($childvalue["gpuntisID"]))
        {
          $childvalue["gpuntisID"] = $childvalue["id"];
        }

		if($nodeKey == "none")
			$nodeID = trim($key).".".trim($childvalue["id"]);
		else
			$nodeID = trim($key).".".trim($nodeKey).".".trim($childvalue["id"]);

        $temp = $this->createTreeNode($nodeID,
                      trim($childvalue["name"]),
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

        if(!empty($temp))
        	if($nodeKey == "none")
          		$childNodes = $temp;
          	else
          		$childNodes[] = $temp;
      }

      if($nodeKey != null && $nodeKey != "none" && !empty($childNodes))
      {

	      $parentKey = str_replace(" ", "", trim($key).".".trim($nodeKey));
	      $parentKey = str_replace("(", "", $parentKey);
	      $parentKey = str_replace(")", "", $parentKey);
        $temp = $this->createTreeNode(
          $parentKey,							// id - autom. generated
          trim($dataKey),							// text	for the node
          'studiengang-root',			// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          $dataKey,
          $planid,
          null,
          $childNodes,
          $semesterID
        );
          if($temp != null && !empty($temp))
            $treeNode[] = $temp;
      }
      else
      {
      	if(!empty($childNodes))
      		if($dataKey == "none")
      		{
				$nodeID = $key.".".$childvalue["id"];
      			$treeNode[] = $childNodes;
      		}
      }

    }
    return $treeNode;
  }

  private function getStundenplanClassData($planid, $semesterID, $checkTreeData = false)
  {
    $classesquery = "SELECT DISTINCT classes.id AS id, " .
    		"classes.gpuntisID AS gpuntisID, " .
            "classes.semester AS name, " .
            "classes.major AS parent, " .
            "classes.name AS shortname, " .
            "'lesson' AS type, " .
            "count(lesson_classes.lessonID) AS lessonamount " .
            "FROM #__thm_organizer_classes AS classes " .
            "LEFT JOIN #__thm_organizer_lesson_classes AS lesson_classes " .
             "ON classes.id = lesson_classes.classID " .
             "LEFT JOIN #__thm_organizer_lessons " .
             "ON lesson_classes.lessonID = #__thm_organizer_lessons.id " .
             "WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
             "AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
             "OR (#__thm_organizer_lessons.plantypeID is null " .
             "AND #__thm_organizer_lessons.semesterID is null) " .
            "GROUP BY classes.id " .
           "ORDER BY parent, name";

    $classesarray = array( );
    $res          = $this->JDA->query( $classesquery );

    return $res;

    if($planid == 1) //unschön, da direkt auf id von Stundenplan geprüft wird.
      $res = $this->getVirtualSchedules("class", $semesterID);
    else
      $res = array();

    if(is_array($res))
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
        $classesarray[ $data->department ][ $data->vid ][ "plantypeID" ] = trim($planid);
      }
    }

    return $classesarray;
  }

  private function getStundenplanRoomData($planid, $semesterID, $checkTreeData = false)
  {
    $roomquery = "SELECT DISTINCT rooms.id AS id, " .
    		"rooms.gpuntisID AS gpuntisID, " .
           "CONCAT(descriptions.category, ' (', descriptions.description, ')') as parent," .
           "rooms.alias AS name, " .
           "rooms.name AS shortname, " .
           "'room' AS type, " .
           "count(lesson_times.lessonID) AS lessonamount " .
           "FROM #__thm_organizer_rooms AS rooms " .
           "LEFT JOIN #__thm_organizer_lesson_times AS lesson_times " .
           "ON rooms.id = lesson_times.roomID " .
           "LEFT JOIN #__thm_organizer_lessons " .
           "ON lesson_times.lessonID = #__thm_organizer_lessons.id " .
           "LEFT JOIN #__thm_organizer_descriptions AS descriptions " .
           "ON descriptions.id = rooms.descriptionID " .
           "WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
           "AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
           "OR (#__thm_organizer_lessons.plantypeID is null " .
           "AND #__thm_organizer_lessons.semesterID is null) " .
           "GROUP BY rooms.id " .
           "ORDER BY parent, name";

    $roomarray = array( );
    $res       = $this->JDA->query( $roomquery );

    return $res;

    if($planid == 1) //unschön, da direkt auf id von Stundenplan geprüft wird.
      $res = $this->getVirtualSchedules("room", $semesterID);
    else
      $res = array();

    if(is_array($res))
    if ( count( $res ) != 0 ) {
      for ( $i = 0; $i < count( $res ); $i++ ) {
        $data = $res[ $i ];

		if(!isset($data->description))
          $key  = $data->department;
        else
          $key  = $data->rtype." (".$data->description.")";

        if ( !isset( $roomarray[ $key ] ) ) {
          $roomarray[ $key ] = array( );
        }
        if ( !isset( $roomarray[ $key ][ $data->vid ] ) ) {
          $roomarray[ $key ][ $data->vid ] = array( );
        }
        $roomarray[ $key ][ $data->vid ][ "id" ]         = trim($data->vid);
        $roomarray[ $key ][ $data->vid ][ "name" ]       = trim($data->vname);
        $roomarray[ $key ][ $data->vid ][ "otype" ]      = trim($data->vtype);
        $roomarray[ $key ][ $data->vid ][ "rtype" ]      =  trim($data->department);

        $roomarray[ $key ][ $data->vid ][ "manager" ]    = trim($data->vresponsible);
        if ( !isset( $roomarray[ $key ][ $data->vid ][ "elements" ] ) )
          $roomarray[ $key ][ $data->vid ][ "elements" ] = array( );
        $roomarray[ $key ][ $data->vid ][ "elements" ][ $data->eid ] = trim($data->eid);
        if ( !isset( $roomarray[ $key ][ $data->vid ][ "lessonamount" ] ) )
          $roomarray[ $key ][ $data->vid ][ "lessonamount" ] = 0;
        $roomarray[ $key ][ $data->vid ][ "lessonamount" ] = $roomarray[ $key ][ $data->vid ][ "lessonamount" ] + $this->getCountRoomLessons( $data->eid, $semesterID );
        $roomarray[ $key ][ $data->vid ][ "plantypeID" ] = trim($planid);
      }
    }

    return $roomarray;
  }

  private function getStundenplanDozData($planid, $semesterID, $checkTreeData = false)
  {
    $teacherquery = "SELECT DISTINCT teachers.id AS id, " .
    		"teachers.gpuntisID AS gpuntisID, " .
            "departments.department AS department, " .
            "departments.subdepartment AS subdepartment, " .
            "teachers.name AS name, " .
            "'' AS shortname, " .
            "'teacher' AS type, " .
            "count(lesson_teacher.lessonID) AS lessonamount " .
            "FROM #__thm_organizer_teachers AS teachers " .
            "LEFT JOIN #__thm_organizer_departments AS departments " .
            "ON teachers.departmentID = departments.id " .
            "LEFT JOIN #__thm_organizer_lesson_teachers AS lesson_teacher " .
            "ON teachers.id = lesson_teacher.teacherID " .
            "LEFT JOIN #__thm_organizer_lessons " .
            "ON lesson_teacher.lessonID = #__thm_organizer_lessons.id " .
            "WHERE (#__thm_organizer_lessons.plantypeID = " .$planid." " .
             "AND #__thm_organizer_lessons.semesterID = " .$semesterID.") " .
             "OR (#__thm_organizer_lessons.plantypeID is null " .
             "AND #__thm_organizer_lessons.semesterID is null) " .
            "GROUP BY teachers.id " .
            "ORDER BY departments.name, teachers.name";

    $teacherarray = array( );
    $res          = $this->JDA->query( $teacherquery );
	$return = array();

	if(is_array($res))
	foreach($res as $resKey=>$resValue)
	{
		if($resValue->subdepartment != "")
			$resValue->parent = $resValue->subdepartment;
		else
			$resValue->parent = $resValue->department;
		$return[$resKey] = $resValue;
	}

	return $return;

    if($planid == 1)
      $res = $this->getVirtualSchedules("teacher", $semesterID);
    else
      $res = array();

    if(is_array($res))
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
        $teacherarray[ $data->department ][ $data->vid ][ "plantypeID" ] = trim($planid);
      }
    }

    return $teacherarray;
  }

  private function getStundenplanSubjectData($planid, $semesterID, $checkTreeData = false)
  {
    $subjectQuery = "SELECT DISTINCT subjects.id AS id, " .
    		"subjects.gpuntisID AS gpuntisID, " .
            "subjects.alias as name, " .
            "subjects.name AS shortname, " .
            "SUBSTRING(subjects.moduleID, 1, 2) AS parent, " .
            "'subject' AS type, " .
            "count(lessons.subjectID) AS lessonamount " .
            "FROM #__thm_organizer_subjects AS subjects " .
            "LEFT JOIN #__thm_organizer_lessons AS lessons " .
            "ON lessons.subjectID = subjects.id " .
            "WHERE (lessons.plantypeID = " .$planid." " .
            "AND lessons.semesterID = " .$semesterID.") " .
            "GROUP BY subjects.gpuntisID " .
            "ORDER BY moduleID, name";

    $subjectArray = array( );
    $res          = $this->JDA->query( $subjectQuery );

	return $res;
  }

  private function getVirtualSchedules($type, $semesterID)
  {
    $vsquery = "SELECT DISTINCT vs.vid, vname, vtype, department, vresponsible, eid
           FROM #__thm_organizer_virtual_schedules as vs
           INNER JOIN #__thm_organizer_virtual_schedules_elements as vse
           ON vs.vid = vse.vid AND vs.sid = vse.sid
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