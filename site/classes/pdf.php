<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( dirname( __FILE__ ) . "/abstrakterBauer.php" );
require_once( dirname( __FILE__ ) . "/mySched_pdf.php" );

class PDFBauer extends abstrakterBauer
{
	private $JDA = null;
	private $cfg = null;

	function __construct($JDA, $cfg)
	{
		$this->JDA = $JDA;
		$this->cfg = $cfg;
	}

	public function erstelleStundenplan( $arr, $username, $title )
	{
		// Defaultangaben fuer Header, Zellen und Tabelle definieren
		$table_default_header_type = array(
			 'WIDTH' => 6,
			'T_COLOR' => array(
				 80,
				80,
				80
			),
			'T_SIZE' => 14,
			'T_FONT' => 'Arial',
			'T_ALIGN' => 'C',
			'V_ALIGN' => 'T',
			'T_TYPE' => 'B',
			'LN_SIZE' => 7,
			'BG_COLOR' => array(
				 255,
				255,
				255
			),
			'BRD_COLOR' => array(
				 150,
				150,
				150
			),
			'BRD_SIZE' => 0.1,
			'BRD_TYPE' => '1',
			'BRD_TYPE_NEW_PAGE' => '',
			'TEXT' => ''
		);
		$table_default_data_type   = array(
			 'T_COLOR' => array(
				 0,
				0,
				0
			),
			'T_SIZE' => 11,
			'T_FONT' => 'Arial',
			'T_ALIGN' => 'C',
			'V_ALIGN' => 'T',
			'T_TYPE' => '',
			'LN_SIZE' => 4,
			'BG_COLOR' => array(
				 255,
				255,
				255
			),
			'BRD_COLOR' => array(
				 150,
				150,
				150
			),
			'BRD_SIZE' => 0.1,
			'BRD_TYPE' => '1',
			'BRD_TYPE_NEW_PAGE' => ''
		);
		$table_default_table_type  = array(
			 'TB_ALIGN' => 'C',
			'BRD_COLOR' => array(
				 150,
				150,
				150
			),
			'BRD_SIZE' => 0.7
		);


		if ( isset( $username ) && isset( $title ) ) {
			if ( $this->cfg[ 'sync_files' ] == 1 ) {
				$res = $JDA->query( "SELECT registerDate FROM " . $this->cfg[ 'jdb_table_user' ] . " WHERE username='" . $username . "'" );

				if ( count( $res ) > 0 && trim( $username ) != "" && trim( $username ) != "undefined" ) {
					$path = $username . strtotime( $res[ 0 ]->registerDate ) . "/";
				} else {
					$path = "";
				}
			} else
				$path = "";

			if ( !$title ) {
				$title = 'stundenplan';
			}

			if ( $title == "Mein Stundenplan" && $username != "" ) {
				$title = $username . " - " . $title;
			}

			if ( $username != "" && $this->cfg[ 'sync_files' ] == 1 )
				if ( !is_dir( $this->cfg[ 'pdf_downloadFolder' ] . $path ) ) {
					//Ordner erstellen
					@mkdir( $this->cfg[ 'pdf_downloadFolder' ] . $path, 0700 );
				}

			//$pdfLink = $this->cfg[ 'pdf_downloadFolder' ] . $path . $title . '.pdf';
			$pdfLink = JPATH_COMPONENT . $this->cfg[ 'pdf_downloadFolder' ]. $path . $title . '.pdf';
			// Array um Wochentage in spalten zu mappen
			$assign = array(
				 'monday' => 1,
				'tuesday' => 2,
				'wednesday' => 3,
				'thursday' => 4,
				'friday' => 5
			);

			// Erstellt Blanko Tabelle als Vorlage (sonst sind rahmen ungleich dick)
			$dummy = array_fill( 0, 7, array( ) );
			$sched = array_fill( 0, 7, $dummy );

			// Zeitspalte definieren
			$sched[ 0 ][ 0 ][ "TEXT" ] = "8:00\n-\n9:30";
			$sched[ 1 ][ 0 ][ "TEXT" ] = "9:50\n-\n11:20";
			$sched[ 2 ][ 0 ][ "TEXT" ] = "11:30\n-\n13:00";
			$sched[ 3 ][ 0 ][ "TEXT" ] = " ";
			$sched[ 4 ][ 0 ][ "TEXT" ] = "14:00\n-\n15:30";
			$sched[ 5 ][ 0 ][ "TEXT" ] = "15:45\n-\n17:15";
			$sched[ 6 ][ 0 ][ "TEXT" ] = "17:30\n-\n19:00";

			// Daten in Tabellenformat umformattieren
			foreach ( $arr as $l ) {
				if ( isset( $l->cell ) ) {
					$l->cell = str_replace( '<br/>', "\n", $l->cell );
					$l->cell = str_replace( '<br>', "\n", $l->cell );
					$l->cell = strip_tags( $l->cell, "<b><i><small>" );
					$l->cell = preg_replace( "/class=\"lecturename_dis\s*\"/", "", $l->cell );
					$l->cell = preg_replace( "/class=\"lecturename\s*\"/", "", $l->cell );
					$l->cell = preg_replace( "/class=\"\"\s*/", "", $l->cell );
					$l->cell = preg_replace( "/class=\"roomshortname\s*\"/", "", $l->cell );
					$l->cell = preg_replace( "/class=\"oldroom\s*\"/", "", $l->cell );

					if ( $l->type == 'sporadic' ) {
						$sporadic[ ] = strip_tags( $l->cell );
					} else {
						if ( ( $l->block ) > 3 )
							$sched[ $l->block ][ $l->dow ][ ] = $l->cell;
						else
							$sched[ $l->block - 1 ][ $l->dow ][ ] = $l->cell;
					}
				}
			}

			// PDF Anlegen
			$pdf = new MySchedPdf();
			$pdf->SetAutoPageBreak( true, 13 );
			$pdf->SetTopMargin( 13 );
			$pdf->AddPage( 'L' );
			$columns = 6;

			// Styles fuer die Formatierung-Tags setzten
			$pdf->SetStyle( "b", "arial", "b", 10, "0, 0, 0" );
			$pdf->SetStyle( "i", "arial", "I", 10, "0, 0, 0" );
			$pdf->SetStyle( "small", "arial", "", 8, "0, 0, 0" );

			// Tabelle initialisieren mit 6 Spalten
			$pdf->Table_Init( $columns, true, true );

			// Formatierung fuer die Tabelle setzen
			$pdf->Set_Table_Type( $table_default_table_type );

			// Default-Formatierung fuer den Header setzen
			$header_subtype = $table_default_header_type;
			for ( $i = 0; $i < $columns; $i++ )
				$header_type[ $i ] = $table_default_header_type;

			// Breite und Text des Headers setzten
			$header_type[ 0 ][ 'WIDTH' ] = 20;
			$header_type[ 1 ][ 'WIDTH' ] = $header_type[ 2 ][ 'WIDTH' ] = $header_type[ 3 ][ 'WIDTH' ] = $header_type[ 4 ][ 'WIDTH' ] = $header_type[ 5 ][ 'WIDTH' ] = 50;
			$header_type[ 0 ][ 'TEXT' ]  = "Zeit";
			$header_type[ 1 ][ 'TEXT' ]  = "Montag";
			$header_type[ 2 ][ 'TEXT' ]  = "Dienstag";
			$header_type[ 3 ][ 'TEXT' ]  = "Mittwoch";
			$header_type[ 4 ][ 'TEXT' ]  = "Donnerstag";
			$header_type[ 5 ][ 'TEXT' ]  = "Freitag";
			$pdf->Set_Header_Type( $header_type );
			$pdf->Draw_Header();

			// Default-Formatierung fuer die Daten Zellen setzen
			$data_subtype = $table_default_data_type;
			$data_type    = Array( ); //reset the array
			for ( $i = 0; $i < $columns; $i++ )
				$data_type[ $i ] = $data_subtype;

			// Spezielle eigenschaften fuer die Zeitspalte setzen
			$data_type[ 0 ][ 'V_ALIGN' ]  = 'M';
			$data_type[ 0 ][ 'T_ALIGN' ]  = 'C';
			$data_type[ 0 ][ 'T_SIZE' ]   = '11';
			$data_type[ 0 ][ 'LN_SIZE' ]  = '5';
			$data_type[ 0 ][ 'BRD_TYPE' ] = "LR";
			$pdf->Set_Data_Type( $data_type );


			// Definition einer leeren Zeile mit dickerem Rand zum Blocktrennen
			$blankLine = array_fill( 0, 6, array(
				 'LN_SIZE' => 0.1,
				'TEXT' => ' ',
				'BRD_SIZE' => 0.7,
				'BRD_TYPE' => 'T'
			) );
			$counter   = 0;
			// Daten in Tabelle einfuegen
			ksort( $sched );
			foreach ( $sched as $line ) {
				$counter++;
				// Maximale Eintraege pro Zeile ermitteln
				$max = 1;
				foreach ( $line as $col ) {
					if ( isset($col[ 'TEXT' ]) )
						continue;
					if ( count( $col ) > $max )
						$max = count( $col );
				}

				// Zeichnet abstandslinie
				$pdf->Draw_Data( $blankLine );

				// Zellen definieren und fuellen
				for ( $i = 0; $i < $max; $i++ ) {
					$data = array( );
					foreach ( $line as $k => $col ) {

						if ( $counter == 4 && $k == 1 ) {
							$data[ $k ][ 'TEXT' ]    = 'Mittagspause';
							$data[ $k ][ 'COLSPAN' ] = 7;
						} else {
							// Textfeld in der Zeitspalte wird besonders behandelt
							if ( $i == 0 && $k == 0 ) {
								$data[ $k ]               = $col;
								$data[ $k ][ 'BRD_TYPE' ] = "LR";
								// Standardbelegung mit einer Lecture
							} else if ( isset($col[ $i ]) ) {
								$data[ $k ][ 'TEXT' ] = $col[ $i ];
								if ( $i == 0 && !isset($col[ $i + 1 ]) ) // Wenn nur ein eintrag existiert hat er weder oben noch unten rand
									$data[ $k ][ 'BRD_TYPE' ] = "LR";
								elseif ( $i == 0 ) // Der erste Eintrag eines Blocks hat oben keinen Rand
									$data[ $k ][ 'BRD_TYPE' ] = "BLR";
								elseif ( !isset($col[ $i + 1 ]) ) // Die letze Lecture eines Blocks hat keinen Rand unten
									$data[ $k ][ 'BRD_TYPE' ] = "TLR";
								// Leeres feld - Simuliertes RowSpanning
							} else {
								$data[ $k ][ 'TEXT' ]     = ' ';
								$data[ $k ][ 'BRD_TYPE' ] = 'LR';
							}
						}
					}

					$pdf->Draw_Data( $data );
				}
			}

			$pdf->Draw_Table_Border();

			$sporadic = array();

			// Sporadische Veranstaltungen werden
			// als Liste darunter angezeigt
			if ( count( $sporadic ) > 0 ) {
				$pdf->Ln( 10 );
				$pdf->SetFont( 'Arial', 'B', 12 );
				$pdf->Cell( 70, 6, 'unregelm&auml;&szlig;ge Veranstaltungen:', 'B', 2 );
				$pdf->SetFont( 'Arial', '', 10 );
				$pdf->Ln( 3 );
				foreach ( $sporadic as $l )
					$pdf->Cell( 0, 5, $l, 0, 2 );
			}
			// Dokument wird lokal gespeichern
			@$pdf->Output( $pdfLink, 'F' );

			if(is_file($pdfLink))
			{
				return array("success"=>true, "data"=>"File created!");
			}
			else
			{
				return array("success"=>false, "data"=>"No file was created!");
			}
		}
	}
}

?>