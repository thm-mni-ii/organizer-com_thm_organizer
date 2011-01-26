<?php
define( 'FPDF_FONTPATH', B . '/fpdf/font/' );
require_once( B . "/fpdf/table/class.fpdf_table.php" );

//extension class
class MySchedPdf extends FPDF_TABLE
{
    public function __construct( )
    {
        parent::FPDF( 'L' );
        $this->AliasNbPages();
    }

    public function footer( )
    {
        $date = date( 'd.m.Y' );

        $this->SetY( -15 );
        $this->SetFont( 'Courier', '', 8 );
        $this->Cell( 90, 10, 'MySched-Generator ver 0.1', 0, 0, 'L' );
        $this->SetFont( 'Arial', 'B', 8 );
        $this->Cell( 90, 10, 'Erstellt: ' . $date . '   -   Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'C' );
        $this->SetFont( 'Courier', '', 8 );
        $this->Cell( 90, 10, 'http://www.fh-giessen.de', 0, 0, 'R' );
    }
}
?>