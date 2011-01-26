<?php

//Wenn die Anfragen nicht durch Ajax von MySched kommt
if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ) {
	if ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] != 'XMLHttpRequest' )
		die( 'Permission Denied!' );
} else
	die( 'Permission Denied!' );

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
		$obj = $this->JDA->query( "SELECT description, startdate, enddate, creationdate FROM " . $this->cfg[ 'db_scheduletable' ] . " WHERE active != 'null' && sid = " . $this->semID );

		if ( count( $obj ) == 0 || $obj == false )
			return array("data"=>"" );
		else {
			return array("data"=>array(
				 $obj[ 0 ]->description,
				$obj[ 0 ]->startdate,
				$obj[ 0 ]->enddate,
				$obj[ 0 ]->creationdate
			) );
		}
	}
}
?>