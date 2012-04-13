<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


class Download
{
	private $cfg = null;
	private $username = null;
	private $title = null;
	private $what = null;
	private $save = null;
	private $doc = null;

	function __construct($JDA, $CFG)
	{
		$this->username = $JDA->getRequest( "username" );
		$this->title    = $JDA->getRequest( "title" );
		$this->what     = $JDA->getRequest( "what" );
		$this->save     = $JDA->getRequest( "save" );
		$this->cfg      = $CFG->getCFG();
		$this->doc		= $JDA->getDoc();
	}

	public function schedule()
	{
		if ( isset( $this->username ) && isset( $this->title ) && isset( $this->what ) &&isset( $this->save ) ) {
		    $path  = "/";
		    $this->title = urldecode( $this->title );


		    if ( $this->title == JText::_("COM_THM_ORGANIZER_SCHEDULER_MYSCHEDULE") && $this->username != "undefined" )
			{
		        $this->title = $this->username . " - " . $this->title;
		    }

		    $tmpFile = JPATH_COMPONENT.$this->cfg[ 'pdf_downloadFolder' ] . $path .'stundenplan.' . $this->what;
		    $file    = JPATH_COMPONENT.$this->cfg[ 'pdf_downloadFolder' ] . $path . $this->title . '.'. $this->what;

		    if ( empty( $this->title ) || $this->title == 'undefined' ) {
		        if ( !file_exists( $tmpFile ) ) {
		            die( JText::_('COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_NO_FILE') );
		        } else {
		            $file  = $tmpFile;
		            $this->title = 'stundenplan';
		        }
		    }

		    if ( !file_exists( $file ) )
		        die( JText::_('COM_THM_ORGANIZER_SCHEDULER_DOWNLOAD_NO_FILE') );


		    if ( $this->save == "true" ) {
		        @copy( $file, $this->cfg[ 'pdf_downloadFolder' ] . $path .$this->username . '.' . $this->what );
		    } elseif ( $this->what == "pdf" ) {
		        $this->doc->setMimeEncoding('application/pdf');
		    } elseif ( $this->what == "xls" ) {
		        $this->doc->setMimeEncoding('application/vnd.ms-excel');
		    } else {
		        // ics
		        $this->doc->setMimeEncoding('text/calendar');
		    }
		    header( "Content-Length: " . filesize( $file ) );
		    header( "Content-Disposition: attachment; filename=\"" . $this->title. "." . $this->what . "\"" );

		    //Datei senden
		    @readfile( $file );
		    //Datei loeschen
		    @unlink( $file );
		}
	}
}
?>
