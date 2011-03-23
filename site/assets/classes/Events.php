<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT."/views/scheduler/tmpl/wsapi/class.mySchedImport.php" );

class Events
{
	private $JDA = null;
	private $CFG = null;
	private $cfg = null;
	private $jsid = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->CFG = $CFG;
		$this->cfg = $CFG->getCFG();
		$this->jsid = $JDA->getUserSessionID();
	}

	public function load()
	{
		$eventmodel = JModel::getInstance('event_list', 'thm_organizerModel', array('ignore_request' => false, 'display_type'=>4));

		$events = $eventmodel->events;

		$arr = array( );

		if(is_array( $events ))
		for ( $i = 0; $i < count( $events ); $i++ ) {
			$temp = $events[$i];
			if ( !isset( $arr[ $temp["id"] ] ) )
				$arr[ $temp["id"] ] = array( );
			$arr[ $temp["id"] ][ "eid" ]       = $temp["id"];
			$arr[ $temp["id"] ][ "title" ]     = $temp["title"];
			$arr[ $temp["id"] ][ "startdate" ] = $temp["startdate"];
			if ( $temp["enddate"] == "0000-00-00" || $temp["enddate"] == null || $temp["enddate"] == "" )
				$arr[ $temp["id"] ][ "enddate" ] = $temp["startdate"];
			else
				$arr[ $temp["id"] ][ "enddate" ] = $temp["enddate"];
			$arr[ $temp["id"] ][ "starttime" ]    = $temp["starttime"];
			$arr[ $temp["id"] ][ "endtime" ]      = $temp["endtime"];
			$arr[ $temp["id"] ][ "edescription" ] = $temp["description"];
			$arr[ $temp["id"] ][ "facultative" ]  = "";
			$arr[ $temp["id"] ][ "source" ]       = "joomla";
			$arr[ $temp["id"] ][ "recurrence_type" ] = $temp["rec_type"];

			if ( !isset( $arr[ $temp["id"] ][ "objects" ] ) )
				$arr[ $temp["id"] ][ "objects" ] = array( );

			$dbo = $this->JDA->getDBO();

			foreach($temp["resourceArray"] as $k=>$v)
			{
				$query	= $dbo->getQuery(true);
				$query->select('gpuntisID');
				if($v["type"] === "teacher")
					$query->from('#__thm_organizer_teachers');
				else if($v["type"] === "room")
					$query->from('#__thm_organizer_rooms');
				else
					continue;
				$query->where('`id` = '.$v["id"]);
				$dbo->setQuery($query);

				$result = $dbo->loadObject();

				$arr[ $temp["id"] ][ "objects" ][ $result->gpuntisID ] = $result->gpuntisID;
			}

		}

		$username = $this->JDA->getUserName();

		$pregres = preg_match( "/[^[:alnum:]]/", $this->jsid );
		if ( $pregres == 0 && strlen( $this->jsid ) > 0 && $username != "" ) {
			try {
				$SI           = new mySchedImport( $username, $this->jsid, $this->CFG );
				$estudycalres = $SI->getCalendar();

				if ( $estudycalres != null ) {
					$temp = array( );
					if ( is_array( $estudycalres ) ) {
						foreach ( $estudycalres as $v ) {
							$temp[ "eid" ]          = "";
							$temp[ "title" ]        = $v->summary;
							$temp[ "startdate" ]    = date( "Y-m-d", strtotime( $v->start ) );
							$temp[ "enddate" ]      = date( "Y-m-d", strtotime( $v->end ) );
							$temp[ "starttime" ]    = date( "H:i:s", strtotime( $v->start ) );
							$temp[ "endtime" ]      = date( "H:i:s", strtotime( $v->end ) );
							$temp[ "edescription" ] = $v->description;
							$temp[ "source" ]       = "estudy";
							$temp[ "recurrence_type" ] = 0;
							$temp[ "facultative" ]  = $v->isFacultative;
							$temp[ "objects" ]      = array( );
							array_push( $arr, $temp );
							$temp = array( );
						}
					}
				}
				return array("success"=>true,"data"=>$arr );
			}
			catch ( Exception $e ) {
				return array("success"=>true,"data"=>$arr );
			}
		} else {
			return array("success"=>true,"data"=>$arr );
		}
	}
}
?>