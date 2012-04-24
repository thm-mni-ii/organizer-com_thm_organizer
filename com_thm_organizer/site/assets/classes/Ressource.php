<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Ressource
{
	private $JDA = null;
	private $CFG = null;
	private $gpuntisID = null;
	private $nodeID = null;
	private $nodeKey = null;
	private $semID = null;
	private $type = null;
	private $plantypeID = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->nodeID = $JDA->getRequest( "nodeID" );
		$this->gpuntisID = $JDA->getRequest( "gpuntisID" );
		$this->nodeKey = $JDA->getRequest( "nodeKey" );
		$this->plantypeID = $JDA->getRequest( "plantypeID" );
		$this->type = $JDA->getRequest( "type" );
		$this->semID = $JDA->getSemID();
	}

	public function load()
	{
		if ( isset( $this->gpuntisID ) && isset( $this->semID ) && isset( $this->nodeID ) && isset( $this->nodeKey ) ) {
			$elements   = null;
			$lessons    = array( );
			$retlessons = array( );

			if ( stripos( $this->gpuntisID, "VS_" ) === 0 ) {
				$elements                 = $this->getElements( $this->nodeKey, $this->semID, $this->type );
				$retlessons[ "elements" ] = "";
				
				foreach ( $elements as $k => $v ) {
					$lessons = array_merge( $lessons, $this->getResourcePlan( $v->gpuntisID, $this->semID, $this->type ) );
					$elementIDs = $this->idToGpuntisID($v->gpuntisID, $this->type);
					$elementID = $elementIDs[0]->id;
					if ( $retlessons[ "elements" ] == "" )
						$retlessons[ "elements" ] .= $elementID;
					else
						$retlessons[ "elements" ] .= ";" . $elementID;
				}
			} else {
				$lessons = $this->getResourcePlan( $this->nodeKey, $this->semID, $this->type );
			}

			if(is_array($lessons))
			foreach ( $lessons as $item ) {
				$key = $item->lid . " " . $item->tpid;
				if ( !isset( $retlessons[ $key ] ) )
					$retlessons[ $key ] = array( );
				$retlessons[ $key ][ "type" ]    = $item->type;
				$retlessons[ $key ][ "id" ]      = $item->id;
				$retlessons[ $key ][ "subject" ] = $item->subject;
				$retlessons[ $key ][ "dow" ]     = $item->dow;
				$retlessons[ $key ][ "block" ]   = $item->block;

				if ( isset( $retlessons[ $key ][ "clas" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "clas" ] );
					if ( !in_array( $item->clas, $arr ) )
						$retlessons[ $key ][ "clas" ] = $retlessons[ $key ][ "clas" ] . " " . $item->clas;
				} else
					$retlessons[ $key ][ "clas" ] = $item->clas;

				if ( isset( $retlessons[ $key ][ "doz" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "doz" ] );
					if ( !in_array( $item->doz, $arr ) )
						$retlessons[ $key ][ "doz" ] = $retlessons[ $key ][ "doz" ] . " " . $item->doz;
				} else
					$retlessons[ $key ][ "doz" ] = $item->doz;

				if ( isset( $retlessons[ $key ][ "room" ] ) ) {
					$arr = explode( " ", $retlessons[ $key ][ "room" ] );
					if ( !in_array( $item->room, $arr ) )
						$retlessons[ $key ][ "room" ] = $retlessons[ $key ][ "room" ] . " " . $item->room;
				} else
					$retlessons[ $key ][ "room" ] = $item->room;

				$retlessons[ $key ][ "category" ] = $item->category;
				$retlessons[ $key ][ "key" ]      = $this->semID.".".$this->plantypeID.".".$key;
				$retlessons[ $key ][ "owner" ]    = "gpuntis";
				$retlessons[ $key ][ "showtime" ] = "none";
				$retlessons[ $key ][ "etime" ]    = null;
				$retlessons[ $key ][ "stime" ]    = null;
				$retlessons[ $key ][ "name" ]     = $item->name;
				$retlessons[ $key ][ "desc" ]     = $item->description;
				$retlessons[ $key ][ "cell" ]     = "";
				$retlessons[ $key ][ "css" ]      = "";
				$retlessons[ $key ][ "longname" ] = $item->longname;
				$retlessons[ $key ][ "plantypeID" ] = $this->plantypeID;
				$retlessons[ $key ][ "semesterID" ] = $this->semID;
				$retlessons[ $key ][ "moduleID" ] = $item->moduleID;
				$retlessons[ $key ][ "comment" ] = $item->comment;
				$retlessons[ $key ][ "ecollaborationLink" ] = $this->getEcollaborationLink($this->nodeKey, $item->moduleID);
			}

			return array("success"=>true,"data"=>$retlessons );
		}
	}

	private function getEcollaborationLink($res, $moduleID)
	{
		if ($this->JDA->isComponentavailable("com_thm_curriculum"))
		{
			$organizer_major = "";
			$query = "SELECT major " .
					"FROM #__thm_organizer_classes " .
					"WHERE gpuntisID = '".$res."'";
			$ret   = $this->JDA->query( $query );
									
			if(isset($ret[0]))
				$organizer_major = $ret[0]->major;
			else
				return null;
			
			$query = "SELECT ecollaboration_link as ecolLink " .
					"FROM #__thm_curriculum_assets_tree " .
					"INNER JOIN #__thm_curriculum_assets ON #__thm_curriculum_assets.id = #__thm_curriculum_assets_tree.asset " .
					"INNER JOIN #__thm_curriculum_assets_semesters ON #__thm_curriculum_assets_tree.id = #__thm_curriculum_assets_semesters.assets_tree_id " .
					"INNER JOIN #__thm_curriculum_semesters_majors ON #__thm_curriculum_assets_semesters.semesters_majors_id = #__thm_curriculum_semesters_majors.id " .
					"INNER JOIN #__thm_curriculum_majors ON #__thm_curriculum_majors.id = #__thm_curriculum_semesters_majors.major_id " .
					"WHERE #__thm_curriculum_majors.organizer_major = '".$organizer_major."' AND LOWER(#__thm_curriculum_assets.lsf_course_code) = LOWER('".$moduleID."')";
			
			$ret   = $this->JDA->query( $query );
			
			if(isset($ret[0]))
				if(!empty($ret[0]->ecolLink))
					return $ret[0]->ecolLink;
			return null;
		}
		return null;
	}

	private function getElements( $id, $sid, $type )
	{
		$query = "SELECT eid as gpuntisID " .
				 "FROM #__thm_organizer_virtual_schedules_elements ";
				 $query .= "WHERE vid = '" . $id . "' " . "AND sid = '" . $sid . "'";
		$ret   = $this->JDA->query( $query );
		return $ret;
	}
	
	private function idToGpuntisID($gpuntisID, $type)
	{
		$query = "SELECT id ";
		if($type == "room")
			$query .= "FROM #__thm_organizer_rooms ";
		else if($type == "clas")
			$query .= "FROM #__thm_organizer_classes ";
		else if($type == "doz")
			$query .= "FROM #__thm_organizer_teachers ";
		$query .= "WHERE gpuntisID = '" . $gpuntisID . "'";
		$ret   = $this->JDA->query( $query );
		return $ret;
	}

	private function getResourcePlan( $ressourcename, $fachsemester, $type )
	{
		$query = "SELECT " .
				 "#__thm_organizer_lessons.gpuntisID AS lid, " .
				 "#__thm_organizer_periods.gpuntisID AS tpid, " .
				 "#__thm_organizer_lessons.gpuntisID AS id, " .
				 "#__thm_organizer_subjects.alias AS description, " .
				 "#__thm_organizer_subjects.gpuntisID AS subject, " .
				 "#__thm_organizer_lessons.type AS category, " .
				 "#__thm_organizer_subjects.name AS name, " .
				 "#__thm_organizer_classes.id AS clas, " .
				 "#__thm_organizer_teachers.id AS doz, " .
				 "#__thm_organizer_rooms.id AS room, " .
				 "#__thm_organizer_periods.day AS dow, " .
				 "#__thm_organizer_periods.period AS block, " .
				 "#__thm_organizer_subjects.moduleID as moduleID, " .
				 "(SELECT 'cyclic') AS type, " .
				 "#__thm_organizer_lessons.comment AS comment, ";

		if ($this->JDA->isComponentavailable("com_thm_curriculum"))
		{
			$query .= " IF(#__thm_organizer_subjects.moduleID='','',mo.title_de) AS longname ";
		}
		else
		{
			$query .= " '' AS longname ";
		}
		$query .= "FROM #__thm_organizer_lessons " .
			"LEFT JOIN #__thm_organizer_lesson_times ON #__thm_organizer_lessons.id = #__thm_organizer_lesson_times.lessonID " .
			"LEFT JOIN #__thm_organizer_periods ON #__thm_organizer_lesson_times.periodID = #__thm_organizer_periods.id " .
			"LEFT JOIN #__thm_organizer_rooms ON #__thm_organizer_lesson_times.roomID = #__thm_organizer_rooms.id " .
		 	"LEFT JOIN #__thm_organizer_lesson_teachers ON #__thm_organizer_lesson_teachers.lessonID = #__thm_organizer_lessons.id " .
		  	"LEFT JOIN #__thm_organizer_teachers ON #__thm_organizer_lesson_teachers.teacherID = #__thm_organizer_teachers.id " .
		  	"LEFT JOIN #__thm_organizer_lesson_classes ON #__thm_organizer_lesson_classes.lessonID = #__thm_organizer_lessons.id " .
		  	"LEFT JOIN #__thm_organizer_classes ON #__thm_organizer_lesson_classes.classID = #__thm_organizer_classes.id " .
		  	"LEFT JOIN #__thm_organizer_subjects ON #__thm_organizer_lessons.subjectID = #__thm_organizer_subjects.id ";
		  	if ($this->JDA->isComponentavailable("com_thm_curriculum"))
		  	{
				$query .= "LEFT JOIN #__thm_curriculum_assets AS mo ON LOWER(#__thm_organizer_subjects.moduleID) = LOWER(mo.lsf_course_code) ";
		  	}
     	  	$query .= "WHERE #__thm_organizer_lessons.semesterID = ".$fachsemester." " .
     	  	"AND #__thm_organizer_lessons.plantypeID = ".$this->plantypeID." ".
          	"AND ";
	    if($type === "clas")
	    	$query .= "( #__thm_organizer_classes.id like '".$ressourcename."') OR ( #__thm_organizer_classes.gpuntisID like '".$ressourcename."')";
	    else if($type === "room")
	    	$query .= "( #__thm_organizer_rooms.id like '".$ressourcename."') OR ( #__thm_organizer_rooms.gpuntisID like '".$ressourcename."')";
	    else if($type === "doz")
	    	$query .= "( #__thm_organizer_teachers.id like '".$ressourcename."') OR ( #__thm_organizer_teachers.gpuntisID like '".$ressourcename."')";
	    else if($type === "subject")
	    	$query .= "( #__thm_organizer_subjects.id like '".$ressourcename."') OR ( #__thm_organizer_subjects.gpuntisID like '".$ressourcename."')";
	    	    
		$hits  = $this->JDA->query( $query );
		return $hits;
	}
}
?>