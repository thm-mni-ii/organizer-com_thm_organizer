<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Grid
{
	private $JDA = null;
	private $semID = null;

	function __construct($JDA, $CFG)
	{
		$this->JDA = $JDA;
		$this->semID = $JDA->getSemID();
	}

	public function load()
	{
		if ( isset( $this->semID ) ) {
			$query = "SELECT gpuntisID AS tpid, day, period, starttime, endtime
		        FROM #__thm_organizer_periods
		        ORDER BY CAST(SUBSTRING(tpid, 4) AS SIGNED INTEGER)";
			$ret   = $this->JDA->query( $query );

			if($ret !== false)
				return array("success"=>true,"data"=>$ret );
			return array("success"=>false,"data"=>"Could not load the data grid!" );
		}
		else
			return array("success"=>false,"data"=>"Could not load the data grid!" );
	}
}


?>