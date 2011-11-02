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

      $this->treeData["clas"] = array_merge_recursive( $this->treeData["clas"], $this->getStundenplanClassData(1, $value->id, true));
      $this->treeData["room"] = array_merge_recursive( $this->treeData["room"], $this->getStundenplanRoomData(1, $value->id, true));
      $this->treeData["doz"] = array_merge_recursive( $this->treeData["doz"], $this->getStundenplanDozData(1, $value->id, true));
    }

    $this->expandSingleNode(& $semesterJahrNode);

    $semesterJahrNode = $this->treeCorrect($semesterJahrNode);

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

      $nodeKey = str_replace(" ", "", $k);
      $nodeKey = str_replace("(", "", $nodeKey);
      $nodeKey = str_replace(")", "", $nodeKey);

      foreach($value as $childkey=>$childvalue)
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
        	if($k == "none")
          		$childNodes = $temp;
          	else
          		$childNodes[] = $temp;
      }

      if($k != null && $k != "none" && !empty($childNodes))
      {

	      $parentKey = str_replace(" ", "", trim($key).".".trim($k));
	      $parentKey = str_replace("(", "", $parentKey);
	      $parentKey = str_replace(")", "", $parentKey);
        $temp = $this->createTreeNode(
          $parentKey,							// id - autom. generated
          trim($k),							// text	for the node
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
          if($temp != null && !empty($temp))
            $treeNode[] = $temp;
      }
      else
      {
      	if(!empty($childNodes))
      		if($k == "none")
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
    $classesquery = "SELECT DISTINCT classes.gpuntisID AS cid, " .
            "classes.semester AS semester, " .
            "classes.major AS department, " .
            "classes.name AS oname, " .
            "'lesson' AS otype, " .
            "classes.manager AS manager, " .
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

        if($checkTreeData === true)
          if(isset($this->treeData["clas"][$data->department][$key]))
            continue;

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
    $roomquery = "SELECT DISTINCT rooms.gpuntisID AS rid, " .
           "rooms.capacity, " .
           "#__thm_organizer_descriptions.category as rtype," .
           "#__thm_organizer_descriptions.description as description, " .
           "rooms.name AS oname, " .
           "#__thm_organizer_descriptions.gpuntisID AS descriptionID, " .
           "'room' AS otype, " .
           "rooms.manager, " .
           "count(lesson_times.lessonID) AS lessonamount " .
           "FROM #__thm_organizer_rooms AS rooms " .
           "LEFT JOIN #__thm_organizer_lesson_times AS lesson_times " .
           "ON rooms.id = lesson_times.roomID " .
           "LEFT JOIN #__thm_organizer_lessons " .
           "ON lesson_times.lessonID = #__thm_organizer_lessons.id " .
           "LEFT JOIN #__thm_organizer_descriptions " .
           "ON #__thm_organizer_descriptions.id = rooms.descriptionID " .
           "WHERE #__thm_organizer_lessons.plantypeID = " .$planid." " .
           "AND #__thm_organizer_lessons.semesterID = " .$semesterID." " .
            "OR (#__thm_organizer_lessons.plantypeID is null " .
            "AND #__thm_organizer_lessons.semesterID is null) " .
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

        if($checkTreeData === true)
          if(isset($this->treeData["room"][$key][$roomid]))
            continue;

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
    $teacherquery = "SELECT DISTINCT teachers.gpuntisID AS tid, " .
            "departments.name AS department, " .
            "departments.gpuntisID AS departmentID, " .
            "teachers.name, " .
            "'teacher' AS otype, " .
            "teachers.username as manager, " .
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

        if($checkTreeData === true)
          if(isset($this->treeData["doz"][$data->department][$key]))
            continue;

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