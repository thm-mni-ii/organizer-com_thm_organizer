<?php

require_once( dirname( __FILE__ ) . "/pdf.php" );
require_once( dirname( __FILE__ ) . "/ics.php" );
require_once( dirname( __FILE__ ) . "/ical.php" );

class StundenplanDirektor
{
	private $builder = NULL;
	public function __construct( abstrakterBauer $builder )
	{
		$this->builder = $builder;
	}

	public function erstelleStundenplan( $arr, $username, $title )
	{
		return $this->builder->erstelleStundenplan( $arr, $username, $title );
	}
}

class Schedule
{
	private $builder = NULL;
	private $arr = null;
	private $username = null;
	private $title = null;
	private $what = null;
	private $JDA = null;
	private $cfg = null;

	function __construct($JDA, $CFG)
	{
		$this->arr      = json_decode( file_get_contents( "php://input" ) );
		$this->username = $JDA->getRequest( "username" );
		$this->title    = $JDA->getRequest( "title" );
		$this->what     = $JDA->getRequest( "what" );
		$this->cfg = $CFG->getCFG();
		$this->JDA = $JDA;
	}

	public function export()
	{
		if ( $this->what == "pdf" ) {
			$this->builder = new PDFBauer($this->JDA, $this->cfg);
		} else if ( $this->what == "ics" ) {
			$this->builder = new ICSBauer($this->JDA, $this->cfg);
		} else if ( $this->what == "ical" ) {
			$this->builder = new ICALBauer($this->JDA, $this->cfg);
		}

		$direktor = new StundenplanDirektor( $this->builder );
		return $direktor->erstelleStundenplan( $this->arr, $this->username, $this->title );
	}
}
?>
