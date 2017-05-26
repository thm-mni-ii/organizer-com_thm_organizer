<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
require_once 'prep_course_export.php';

class THM_OrganizerTemplatePC_Participant_Export extends THM_OrganizerTemplatePC_Export
{
	/**
	 * THM_OrganizerTemplatePrep_Course_Participant_List_Export_PDF constructor.
	 *
	 * @param int $lessonID the lessonID of the exported course
	 */
	public function __construct($lessonID)
	{
		parent::__construct($lessonID);

		$this->render();
	}

	/**
	 * Add Table with participant information and additional places for students to TCPDF
	 *
	 * @return void
	 */
	private function createParticipantTable()
	{
		$header = array('#', 'Name',
			$this->lang->_("COM_THM_ORGANIZER_DEPARTMENT"),
			$this->lang->_("COM_THM_ORGANIZER_PROGRAM"),
			$this->lang->_("COM_THM_ORGANIZER_ROOM"),
			$this->lang->_("COM_THM_ORGANIZER_PAID"));
		$widths = array(10, 60, 30, 35, 25, 20);
		$participantData = $this->courseData["participants"];

		$this->document->SetFillColor(210);
		$this->document->SetFont('', 'B');
		for ($i = 0; $i < count($header); ++$i)
		{
			$this->document->Cell($widths[$i], 7, $header[$i], 1, 0, 'L', 1);
		}

		$this->document->Ln();

		$this->document->SetFillColor(235, 252, 238);
		$this->document->SetFont('');
		foreach ($participantData as $id => $participant)
		{
			$cells = array($id + 1, $participant["name"], $participant["departmentName"], $participant["programName"], '', '');

			$startX = $this->document->GetX();
			$startY = $this->document->GetY();

			$maxNoCells = 0;
			$maxCellCount = 0;
			$maxArray = array();

			for ($i = 0; $i < count($cells); ++$i)
			{
				$cellCount = $this->document->MultiCell($widths[$i], 5, $cells[$i], 'T', 'L', 0, 0);
				if ($cellCount > $maxNoCells)
				{
					$maxNoCells = $cellCount;
				}
			}

			array_push($maxArray, $maxCellCount);
			$this->document->SetXY($startX, $startY);

			foreach ($widths as $w)
			{
				$this->document->MultiCell($w, $maxNoCells * 5, '', 'LR', 'L', 0, 0);
			}

			$this->document->Ln();

			if ($this->document->getY() + $maxCellCount > 260)
			{
				$this->document->addPage();
				$this->document->setY(34);
			}
		}

		// Create empty cells for 25% more participants and rount to a multiple of 6 due to the passports nature
		$emptyCells = (intval((sizeof($participantData) * 1.25) / 6) + 1) * 6;
		for ($id = sizeof($participantData); $id < $emptyCells; ++$id)
		{
			$cells = array($id + 1, '', '', '', '', '');

			$startX = $this->document->GetX();
			$startY = $this->document->GetY();

			for ($i = 0; $i < count($cells); ++$i)
			{
				$this->document->MultiCell($widths[$i], 5, $cells[$i], 'LRTB', 'L', 0, 0);
			}

			$this->document->SetXY($startX, $startY);
			$this->document->Ln();

			if ($this->document->getY() > 260)
			{
				$this->document->addPage();
				$this->document->setY(34);
			}
		}

		$this->document->Cell(array_sum($widths), 0, '', 'T');
	}


	/**
	 * Output pdf with a Table containing all participants and places for additional participants
	 *
	 * @return void
	 */
	protected function render()
	{
		$this->document->SetTitle(
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_PDF_PARTICIPANTS") . ' - ' .
			$this->courseData["name"] . ' - ' . $this->courseData["c_start"]
		);
		$this->setHeader();

		$this->document->AddPage();

		$this->createParticipantTable();

		$filename = urldecode($this->courseData["name"] . '_' . $this->courseData["c_start"])
			. '_' .
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_PDF_PARTICIPANTS") .
			'.pdf';
		$this->document->Output($filename, 'I');

		ob_flush();
	}
}
