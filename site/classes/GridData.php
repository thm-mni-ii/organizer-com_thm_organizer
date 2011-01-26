<?php
// Wenn die Anfragen nicht durch Ajax von MySched kommt
if ( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) ) {
	if ( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] != 'XMLHttpRequest' )
		die( 'Permission Denied!' );
} else
	die( 'Permission Denied!' );

class GridData
{
	private $JDA = null;
	private $semID = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->semID = (int)$JDA->getRequest( "class_semester_id" );
	}

	public function load()
	{
		if ( isset( $this->semID ) ) {
			if(is_int($this->semID))
			{
				$query = "SELECT tpid, day, period, starttime, endtime
			        FROM jos_giessen_scheduler_timeperiods
			        WHERE sid = '".$this->semID."'
			        ORDER BY CAST(SUBSTRING(tpid, 4) AS SIGNED INTEGER)";
				$ret   = $this->JDA->query( $query );
				return array("success"=>true,"data"=>$ret );
			}
			else
				return array("success"=>false,"data"=>"Could not load the data grid!" );
		}
		else
			return array("success"=>false,"data"=>"Could not load the data grid!" );
	}
}


?>