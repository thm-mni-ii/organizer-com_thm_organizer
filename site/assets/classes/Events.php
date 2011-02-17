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
		if ( isset( $this->jsid ) ) {
			$timestamp = time();
			$query     = "SELECT id as eid, title, startdate, enddate, starttime, endtime, description as edescription, recurrence_type, resourceid, resource_type " .
						 "FROM #__thm_organizer_events LEFT JOIN #__thm_organizer_event_resources ON id = eventid ORDER BY resource_type";
			$res       = $this->JDA->query( $query );

			$arr = array( );

			if(is_array( $res ))
			for ( $i = 0; $i < count( $res ); $i++ ) {
				$temp = $res[ $i ];
				$temp->objectid = null;
				if($temp->resource_type !== null)
				{
					$query     = "SELECT gpuntisID as objectid " .
							 	 "FROM ".$temp->resource_type." WHERE id = ".$temp->resourceid;
					$res2       = $this->JDA->query( $query );

					if(is_array($res2))
						if(count($res2) > 0)
							$temp->objectid = $res2[0]->objectid;
				}
				if ( !isset( $arr[ $temp->eid ] ) )
					$arr[ $temp->eid ] = array( );
				$arr[ $temp->eid ][ "eid" ]       = $temp->eid;
				$arr[ $temp->eid ][ "title" ]     = $temp->title;
				$arr[ $temp->eid ][ "startdate" ] = $temp->startdate;
				if ( $temp->enddate == "0000-00-00" || $temp->enddate == null || $temp->enddate == "" )
					$arr[ $temp->eid ][ "enddate" ] = $temp->startdate;
				else
					$arr[ $temp->eid ][ "enddate" ] = $temp->enddate;
				$arr[ $temp->eid ][ "starttime" ]    = $temp->starttime;
				$arr[ $temp->eid ][ "endtime" ]      = $temp->endtime;
				$arr[ $temp->eid ][ "edescription" ] = $temp->edescription;
				$arr[ $temp->eid ][ "facultative" ]  = "";
				$arr[ $temp->eid ][ "source" ]       = "joomla";
				$arr[ $temp->eid ][ "recurrence_type" ] = $temp->recurrence_type;
				if ( !isset( $arr[ $temp->eid ][ "objects" ] ) )
					$arr[ $temp->eid ][ "objects" ] = array( );
				if($temp->objectid !== null)
					if(is_string($temp->objectid))
						$arr[ $temp->eid ][ "objects" ][ $temp->objectid ] = $temp->objectid;
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
					return array("success"=>false,"data"=>$arr );
				}
			} else {
				return array("success"=>true,"data"=>$arr );
			}
		} else {
			// DB-FEHLER
			return array("success"=>false,"data"=>array(
				 'code' => '403',
				'errors' => array(
					 'reason' => 'Permission Denied!'
				)
			) );
		}
	}
}
?>