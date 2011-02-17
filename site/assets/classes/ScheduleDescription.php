<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class ScheduleDescription
{
	private $JDA = null;
	private $semID = null;
	private $cfg = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->cfg = $CFG->getCFG();
		$this->semID = $JDA->getSemID();
	}

	public function load()
	{
		$obj = $this->JDA->query( "SELECT description, startdate, enddate, creationdate FROM #__thm_organizer_schedules WHERE active != 'null' && sid = " . $this->semID );

		if ( count( $obj ) == 0 || $obj == false )
			return array("success"=>true,"data"=>"" );
		else {
			return array("success"=>true,"data"=>array(
				 $obj[ 0 ]->description,
				$obj[ 0 ]->startdate,
				$obj[ 0 ]->enddate,
				$obj[ 0 ]->creationdate
			) );
		}
	}
}
?>