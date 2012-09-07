<?php
/**
 * @version	    v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		MySchedPdf
 * @description MySchedPdf file from com_thm_organizer
 * @author	    Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

define('FPDF_FONTPATH', B . '/fpdf/font/');
require_once B . "/fpdf/table/class.fpdf_table.php";

/**
 * Class MySchedPdf for component com_thm_organizer
 *
 * Class that extends the FPDF_TABLE
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class MySchedPdf extends FPDF_TABLE
{
	/**
	 * Constructor which perfom initial tasks
	 *
	 * @since  1.5
	 *
	 */
	public function __construct()
	{
		parent::FPDF('L');
		$this->AliasNbPages();
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
		$this->SetFont('Courier', '', 8);
		$this->Cell(90, 10, 'MySched-Generator ver 0.1', 0, 0, 'L');
		$this->SetFont('Arial', 'B', 8);
		$this->Cell(90, 10, 'Erstellt: ' . $date . '   -   Seite ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
		$this->SetFont('Courier', '', 8);
		$this->Cell(90, 10, 'http://www.fh-giessen.de', 0, 0, 'R');
	}
}
