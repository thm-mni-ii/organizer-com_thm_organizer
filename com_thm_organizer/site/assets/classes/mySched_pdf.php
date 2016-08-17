<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        MySchedPdf
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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
	private $_startDate = null;

	/**
	 * Schedule Enddate
	 *
	 * @var    String
	 */
	private $_endDate = null;

	/**
	 * Constructor which performs initial tasks
	 *
	 * @param   string $title     the title for the pdf
	 * @param   string $startDate the start date
	 * @param   string $endDate   the end date
	 */
	public function __construct($title, $startDate, $endDate)
	{
		$this->_title = $title;

		$this->_endDate = DateTime::createFromFormat('Y-m-d', $endDate);
		$this->_endDate = $this->_endDate->sub(new DateInterval('P2D'));
		$this->_endDate = $this->_endDate->format('d.m.y');

		$this->_startDate = DateTime::createFromFormat('Y-m-d', $startDate);
		$this->_startDate = $this->_startDate->format('d.m.y');
		parent::FPDF('L');
		$this->AliasNbPages();
	}

	/**
	 * Method to set the header of the pdf document
	 *
	 * @return array An array with information about the status of the creation
	 */
	public function Header()
	{
		$this->SetFont('Arial', 'B', 15);
		$this->Cell(124);
		$this->Cell(30, 10, '' . $this->_title . ' - Woche ' . $this->_startDate . ' bis ' . $this->_endDate, 0, 0, 'C');
		$this->Ln(15);
	}

	/**
	 * Method to set the footer of the pdf document
	 *
	 * @return array An array with information about the status of the creation
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
