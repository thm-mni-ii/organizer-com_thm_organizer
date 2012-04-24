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
                    $semesterID,
                    $nodeKey)
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

	$expanded = false;

	if($this->publicDefault != null)
	{
		$publicDefaultArray = $this->publicDefault;
		$firstValue = each($publicDefaultArray);

      	if(strpos($firstValue["key"], $id) === 0)
		{
			$expanded = true;
		}
		if($leaf === true)
		{
			if(isset($this->publicDefault[$id]))
	      	{
	      		$publicDefault = $this->publicDefault[$id];
	      	}
	      	else
	      		$publicDefault = "notdefault";
		}
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
            $publicDefault,
            $nodeKey,
            $expanded
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
            $publicDefault,
            $nodeKey,
            $expanded
          );

    if($publicDefault === "default")
    {
    	if($treeNode != null)
    		$this->publicDefaultNode = $treeNode;
    	else
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
							            $publicDefault,
							            $nodeKey,
							            $expanded
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
          $value->id,
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

    $this->expandSingleNode($semesterJahrNode);

    $semesterJahrNode = $this->treeCorrect($semesterJahrNode);

	//echo "<pre>".print_r($semesterJahrNode, true)."</pre>";

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
        $this->expandSingleNode($v);
      else if(is_array($v->children))
      {
        if(count($arr) > 1)
          return;

        $v->expanded = true;

        $this->expandSingleNode($v->children);
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
      $nodeKey = $key.".".$v->id;
      $temp = $this->createTreeNode(
          $nodeKey,							// id - autom. generated
          $plantype,					// text	for the node
          "plantype" . '-root',			// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          $v->id,
          $v->id,
          null,
          null,
          $semesterID,
          $v->id
        );
        if($v->id == 1)
      		$children = $this->StundenplanView($nodeKey, $v->id, $semesterID);
      	else if($v->id == 2)
      		$children = $this->LehrplanView($nodeKey, $v->id, $semesterID);
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_TEACHERS"),						// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "doz",
          $planid,
          null,
          null,
          $semesterID,
          $key.".doz"
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_ROOMS"),							// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "room",
          $planid,
          null,
          null,
          $semesterID,
          $key.".room"
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_SEMESTER"),						// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "clas",
          $planid,
          null,
          null,
          $semesterID,
          $key.".clas"
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_SUBJECTS"),						// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "subject",
          $planid,
          null,
          null,
          $semesterID,
          $key.".subject"
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_DELTA_CENTRAL"),			// text	for the node
          'delta' . '-node',				// iconCls
          true,							// leaf
          false,							// draggable
          false,							// singleClickExpand
          "delta",
          $planid,
          "delta",
          null,
          $semesterID,
          $key.".delta"
        );

    if($temp != null && !empty($temp))
    {
      	$viewNode[] = $temp;
    }

    $temp = $this->createTreeNode(
          $key.".respChanges",					// id - autom. generated
          JText::_("COM_THM_ORGANIZER_SCHEDULER_DELTA_OWN"),			// text	for the node
          'respChanges' . '-node',		// iconCls
          true,							// leaf
          false,							// draggable
          false,							// singleClickExpand
          "respChanges",
          $planid,
          "respChanges",
          null,
          $semesterID,
          $key.".respChanges"
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_TEACHERS"),						// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "doz",
          $planid,
          null,
          null,
          $semesterID,
          $key.".doz"
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
          JText::_("COM_THM_ORGANIZER_SCHEDULER_SEMESTER"),						// text	for the node
          'view' . '-root',				// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          "clas",
          $planid,
          null,
          $this->getStundenplan($key.".clas", $planid, $semesterID, "clas"),
          $semesterID,
          $key.".clas"
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
    $virtualSchedules = array();

    if($type == "doz")
    {
    	$datas = $this->getStundenplanDozData($planid, $semesterID);
    	$virtualSchedules = $this->getVirtualSchedules($type, $semesterID);
    }
    else if($type == "room")
    {
    	$datas = $this->getStundenplanRoomData($planid, $semesterID);
    	$virtualSchedules = $this->getVirtualSchedules($type, $semesterID);
    }
    else if($type == "clas")
    {
    	$datas = $this->getStundenplanClassData($planid, $semesterID);
    	$virtualSchedules = $this->getVirtualSchedules("class", $semesterID);
    }
    else
    	$datas = $this->getStundenplanSubjectData($planid, $semesterID);
        
    if(is_array( $datas ) === true)
    if ( count( $datas ) != 0 ) {
	  $this->treeData[$type] = array_merge_recursive( $this->treeData[$type], $datas);
      for ( $i = 0; $i < count( $datas ); $i++ ) {
        $data = $datas[ $i ];
        $id = trim($data->id);
        $parent = trim($data->parentID);
        if ( !isset( $dataArray[ $parent ] ) ) {
          $dataArray[ $parent ] = array( );
        }

		$dataArray[ $parent ][ $id ]                   = array( );
		$dataArray[ $parent ][ $id ][ "id" ]           = trim($id);
		$dataArray[ $parent ][ $id ][ "department" ]   = trim($data->parentName);
		$dataArray[ $parent ][ $id ][ "shortname" ]    = trim($data->shortname);
		$dataArray[ $parent ][ $id ][ "departmentID" ]    = trim($data->departmentID);
		$dataArray[ $parent ][ $id ][ "type" ]        = trim($data->type);
		$dataArray[ $parent ][ $id ][ "name" ]         = trim($data->name);
		$dataArray[ $parent ][ $id ][ "lessonamount" ] = trim($data->lessonamount);
		$dataArray[ $parent ][ $id ][ "gpuntisID" ] = trim($data->gpuntisID);
		$dataArray[ $parent ][ $id ][ "semesterID" ] = trim($semesterID);
		$dataArray[ $parent ][ $id ][ "plantypeID" ] = trim($planid);
				
		foreach($virtualSchedules as $k=>$v) {
			if($v->department === trim($data->parentName))
			{
				$v->departmentID = $parent;
			}
		}

		if(in_array($key, $this->inTree))
			$dataArray[ $parent ][ $id ][ "treeLoaded" ] = true;
		else
			$dataArray[ $parent ][ $id ][ "treeLoaded" ] = false;
		}
	}
	
	if(!empty($virtualSchedules))
	{
		for ( $i = 0; $i < count( $virtualSchedules ); $i++ ) {
			$data = $virtualSchedules[ $i ];
			$id = trim($data->vid);
			if(!isset($data->departmentID) && $data->department != "none")
				continue;
			
			if($data->department != "none")
				$parent = trim($data->departmentID);
			else
				$parent = trim($data->department);	
			if ( !isset( $dataArray[ $parent ] ) ) {
				$dataArray[ $parent ] = array( );
			}
		
			$dataArray[ $parent ][ $id ]                   = array( );
			$dataArray[ $parent ][ $id ][ "id" ]           = trim($id);
			$dataArray[ $parent ][ $id ][ "department" ]   = trim($data->department);
			$dataArray[ $parent ][ $id ][ "shortname" ]    = trim($data->vname);
			$dataArray[ $parent ][ $id ][ "departmentID" ]    = trim($parent);
			$dataArray[ $parent ][ $id ][ "type" ]        = trim($data->vtype);
			$dataArray[ $parent ][ $id ][ "name" ]         = trim($data->vname);
			$dataArray[ $parent ][ $id ][ "lessonamount" ] = 1;
			$dataArray[ $parent ][ $id ][ "gpuntisID" ] = null;
			
			if(!isset($dataArray[ $parent ][ $id ][ "elements" ]))
				$dataArray[ $parent ][ $id ][ "elements" ] = array();
			$dataArray[ $parent ][ $id ][ "elements" ][] = trim($data->eid);
			
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
      $parentName = "";
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
                      $semesterID,
                      trim($childvalue["id"]));

        if(!empty($temp))
        	if($nodeKey == "none")
          		$childNodes = $temp;
          	else
          		$childNodes[] = $temp;

        $parentName = $childvalue["department"];
      }

      if($nodeKey != null && $nodeKey != "none" && !empty($childNodes))
      {
	      $parentKey = str_replace(" ", "", trim($key).".".trim($nodeKey));
	      $parentKey = str_replace("(", "", $parentKey);
	      $parentKey = str_replace(")", "", $parentKey);
        $temp = $this->createTreeNode(
          $parentKey,							// id - autom. generated
          trim($parentName),							// text	for the node
          'studiengang-root',			// iconCls
          false,							// leaf
          false,							// draggable
          true,							// singleClickExpand
          $dataKey,
          $planid,
          null,
          $childNodes,
          $semesterID,
          trim($nodeKey)
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
            "classes.major AS parentName, " .
            "REPLACE(REPLACE(REPLACE(classes.major, ' ', ''), '(', ''), ')', '') AS parentID, " .
            "classes.id AS departmentID, " .
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
           "ORDER BY parentName, name";

    $classesarray = array( );
    $res          = $this->JDA->query( $classesquery );

    return $res;
  }

  private function getStundenplanRoomData($planid, $semesterID, $checkTreeData = false)
  {
    $roomquery = "SELECT DISTINCT rooms.id AS id, " .
    		"rooms.gpuntisID AS gpuntisID, " .
           "CONCAT(descriptions.category, ' (', descriptions.description, ')') as parentName, " .
           "descriptions.id AS parentID, " .
           "descriptions.id AS departmentID, " .
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
           "ORDER BY parentName, name";

    $roomarray = array( );
    $res       = $this->JDA->query( $roomquery );

    return $res;
  }

  private function getStundenplanDozData($planid, $semesterID, $checkTreeData = false)
  {
    $teacherquery = "SELECT DISTINCT teachers.id AS id, " .
    		"teachers.gpuntisID AS gpuntisID, " .
    		"departments.id AS parentID, " .
            "departments.id AS departmentID, " .
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
			$resValue->parentName = $resValue->subdepartment;
		else
			$resValue->parentName = $resValue->department;
		$return[$resKey] = $resValue;
	}

	return $return;
  }

  private function getStundenplanSubjectData($planid, $semesterID, $checkTreeData = false)
  {
    $subjectQuery = "SELECT DISTINCT subjects.id AS id, " .
    		"subjects.gpuntisID AS gpuntisID, " .
            "subjects.alias as name, " .
            "subjects.name AS shortname, " .
            "SUBSTRING(subjects.moduleID, 1, 2) AS parentName, " .
            "SUBSTRING(subjects.moduleID, 1, 2) AS parentID, " .
            "subjects.id AS departmentID, " .
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