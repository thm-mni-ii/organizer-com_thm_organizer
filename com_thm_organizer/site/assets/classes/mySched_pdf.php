<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        MySchedPdf
 * @description MySchedPdf file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

jimport('thm_core.fpdf.fpdf');

/**
 * Class MySchedPdf for component com_thm_organizer
 * Class that extends the FPDF_TABLE
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class MySchedPdf extends FPDF_TABLE
{
    /**
     * Schedule Title
     *
     * @var    String
     */
    private $_title = null;

    /**
     * Schedule Startdate
     *
     * @var    String
     */
    private $_startdate = null;

    /**
     * Schedule Enddate
     *
     * @var    String
     */
    private $_enddate = null;
    
    /**
     * Constructor which performs initial tasks
     *
     * @param   string  $title  the title for the pdf
     */
    public function __construct($title, $startdate, $enddate)
    {
        $this->_title = $title;

        $this->_enddate = DateTime::createFromFormat('Y-m-d', $enddate);
        $this->_enddate = $this->_enddate->sub(new DateInterval('P2D'));
        $this->_enddate = $this->_enddate->format('d.m.y');

        $this->_startdate = DateTime::createFromFormat('Y-m-d', $startdate);
        $this->_startdate = $this->_startdate->format('d.m.y');
        parent::FPDF('L');
        $this->AliasNbPages();
    }

    /**
     * Method to set the header of the pdf document
     *
     * @return Array An array with information about the status of the creation
     */
    public function Header()
    {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(124);
        $this->Cell(30,10, '' . $this->_title . ' - Woche '. $this->_startdate . ' bis ' . $this->_enddate, 0, 0, 'C');
        $this->Ln(15);
    }

    /**
     * Method to set the footer of the pdf document
     *
     * @return Array An array with information about the status of the creation
     */
    public function footer()
    {
        $date = date('d.m.Y');

        $this->SetY(-15);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(124);
        $this->Cell(30, 10, 'Erstellt: ' . $date . '   -   Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}
