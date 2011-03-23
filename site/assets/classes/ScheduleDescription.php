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
		$query = "SELECT description, startdate, enddate, creationdate FROM #__thm_organizer_schedules WHERE active != 'null' && sid = " . $this->semID;

		$obj = $this->JDA->query( $query );
		if ( count( $obj ) == 0 || $obj == false )
			return array("success"=>false,"data"=>"" );
		else {

			$startdate = explode("-", $obj[ 0 ]->startdate);
			$startdate =  $startdate[2].".".$startdate[1].".".$startdate[0];

			$enddate = explode("-", $obj[ 0 ]->enddate);
			$enddate =  $enddate[2].".".$enddate[1].".".$enddate[0];

			$creationdate = explode("-", $obj[ 0 ]->creationdate);
			$creationdate =  $creationdate[2].".".$creationdate[1].".".$creationdate[0];

			return array("success"=>true,"data"=>array(
				$obj[ 0 ]->description,
				$startdate,
				$enddate,
				$creationdate
			) );
		}
	}
}
?>