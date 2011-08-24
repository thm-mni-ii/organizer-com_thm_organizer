<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_COMPONENT."/views/scheduler/tmpl/wsapi/class.mySchedImport.php" );

class eStudy
{
	private $jsid = null;
	private $semID = null;
	private $mnr = null;
	private $cfg = null;
	private $JDA = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->jsid  = $JDA->getRequest( "jsid" );
		$this->semID = $JDA->getSemID();
		$this->mnr   = $JDA->getRequest( "mnr" );
		$this->CFG   = $CFG;
		$this->cfg   = $CFG->getCFG();
	}

	public function getCourseLink()
	{
		if ( isset( $this->jsid ) && isset( $this->semID ) && isset( $this->mnr )) {
		    $username = $this->JDA->getUserName();
		    $res      = $this->JDA->query( "SELECT semesterDesc FROM #__thm_organizer_semesters WHERE id ='" . $this->semID . "'");
		    if ( count( $res ) == 1 ) {
		        $data     = $res[ 0 ];
		        $semester = $data->semester;
		    }

		    $json    = file_get_contents( "php://input" );
		    $resdata = json_decode( $json );

		    $username = $this->JDA->getUserName();

		    $SI           = new mySchedImport( $username, $this->jsid, $this->CFG );
		    $estudylink   = $SI->getCourseLink( $resdata, strtolower( $this->mnr), $semester );
		    $estudycourse = $SI->existsCourse( $resdata, strtolower( $this->mnr), $semester );

		    $arr[ "success" ] = true;
		    $arr[ "link" ]    = $estudylink;
		    $arr[ "msg" ]     = $estudycourse;

		    if ( !( $estudycourse === false ) && !( $estudycourse === true) ) {
		        $arr[ "success" ] = false;
		    }
		    return array("data"=>$arr );
		} else {
		    die( 'Permission Denied!' );
		}
	}
}
?>
