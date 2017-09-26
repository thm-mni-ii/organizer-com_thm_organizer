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

class THMOrganizerTemplatePC_Badges_Export extends THM_OrganizerTemplatePC_Export
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
	 * Output passports for all participants of a lesson
	 *
	 * @return void
	 */
	public function createBadges()
	{
		$emptyCells = (intval((sizeof($this->courseData["participants"]) * 1.25) / 6) + 1) * 6;

		$params = JComponentHelper::getParams('com_thm_organizer');

		$this->document->setPrintHeader(false);
		$this->document->setPrintFooter(false);
		$this->document->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$this->document->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$this->document->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$this->document->SetFont('', 'BI', 15);

		$this->document->AddPage('L', '', false, false);

		$xPos = [10, 15, 35, 57, 70, 68, 65, 40, 78];

		$circle = [
			'width' => 0.1,
			'cap'   => 'butt',
			'join'  => 'miter',
			'dash'  => '5,2.5',
			'phase' => 10,
			'color' => [0, 0, 0]
		];
		$rect   = [
			'width' => 0.1,
			'cap'   => 'butt',
			'join'  => 'miter',
			'dash'  => 0,
			'color' => [0, 0, 0]
		];

		$count = 0;

		for ($i = 0; $i < $emptyCells; $i += 2)
		{
			$this->document->SetLineStyle($rect);
			$this->document->Rect($xPos[0], 10, 90, 80);
			$this->document->Rect($xPos[0], 92, 90, 80);

			// Image
			$imagePath = K_PATH_IMAGES . "thm_logo.png";

			$this->document->Image($imagePath, $xPos[7], 15, 30, 0);
			$this->document->Image($imagePath, $xPos[7], 97, 30, 0);

			$this->document->SetFont('', '', 10);

			for ($l = 0; $l < 2; ++$l)
			{
				$id = $i + $l;
				if ($id >= count($this->courseData["participants"]))
				{
					$participant = ["name"     => "", "zip_code" => "", "city"     => "", "address"  => ""];
				}
				else
				{
					$participant = $this->courseData["participants"][$id];
				}

				$this->document->SETXY($xPos[1], 28 + ($l * 82));
				$headerLine = $this->lang->_("COM_THM_ORGANIZER_PREP_COURSES") . " " .
					$this->courseData["planPeriod"] . " " .
					$this->courseData["start"][0];
				$this->document->Cell(80, 5, $headerLine, 0, 0, 'C');
				$this->document->Ln();
				$this->document->SetFont('', 'B', 20);
				$this->document->SETXY($xPos[1], 33 + ($l * 82));
				$this->document->Cell(80, 5, $this->lang->_("COM_THM_ORGANIZER_BADGE"), 0, 0, 'C');
				$this->document->SETXY($xPos[1], 45 + ($l * 82));

				$this->document->SetFont('', 'B', 10);
				$this->document->Cell(80, 5, $this->courseData["name"], 0, 0, 'C');
				$this->document->Ln();
				$this->document->SetFont('', '', 10);
				$this->document->SETXY($xPos[1], 53 + ($l * 82));
				$dateLine = $this->courseData["c_start"] . " - " .
					$this->courseData["c_end"] . " in " .
					$this->courseData["place"];
				$this->document->Cell(80, 5, $dateLine, 0, 0, 'C');
				$this->document->Ln();
				$this->document->Ln();
				$this->document->SETXY($xPos[1], 63 + ($l * 82));
				$this->document->Cell(20, 5, "Name: ", 0, 0, 'L');
				$this->document->SetFont('', 'B', 10);

				$this->document->Cell(65, 5, $participant["name"], 0, 0, 'L');
				$this->document->SetFont('', '', 10);
				$this->document->Ln();
				$this->document->SETXY($xPos[1], 68 + ($l * 82));
				$this->document->Cell(20, 5, $this->lang->_("COM_THM_ORGANIZER_ADDRESS") . ": ", 0, 0, 'L');

				$this->document->Cell(65, 5, $participant["address"], 0, 0, 'L');
				$this->document->Ln();
				$this->document->SETXY($xPos[1], 73 + ($l * 82));

				$this->document->Cell(20, 5, $this->lang->_("COM_THM_ORGANIZER_RESIDENCE") . ": ", 0, 0, 'L');
				$this->document->Cell(65, 5, "{$participant["zip_code"]} {$participant["city"]}", 0, 0, 'L');

				$this->document->SetFont('', 'B', 11);
				$lnr = $i + 1;
				$this->document->SETXY($xPos[1] + 70, 15 + ($l * 82));
				$this->document->Cell(10, 5, $lnr, 1, 0, 'C');
			}

			for ($x = 0; $x < count($xPos); ++$x)
			{
				$xPos[$x] += 92;
			}

			$count++;

			if (($count == 3) OR ($i >= $emptyCells - 2))
			{
				$this->document->AddPage('L', '', false, false);

				$xPos = [14, 19, 39, 61, 74, 72, 69, 44, 82];

				for ($j = 0; $j < $count; ++$j)
				{
					for ($l = 0; $l < 2; ++$l)
					{
						$this->document->SetLineStyle($rect);
						$this->document->Rect($xPos[0], 10 + ($l * 82), 90, 80);

						$this->document->SetLineStyle($circle);
						$this->document->Circle($xPos[3], 70 + ($l * 82), 9);

						$this->document->SetFont('', '', 6);
						$this->document->Text($xPos[4], 61 + ($l * 82), $this->lang->_("COM_THM_ORGANIZER_REPRESENTATIVE"));
						if (!empty($params->get('signatureFile')))
						{
							$imagePath = K_PATH_IMAGES . $params->get('signatureFile');
							$this->document->Image($imagePath, $xPos[5], 64 + ($l * 82), 20, 0);
						}

						$this->document->Text($xPos[6], 79 + ($l * 82), $params->get('representativeName', ''));

						$this->document->SetFont('', 'BU', 20);
						$this->document->SETXY($xPos[1], 15 + ($l * 82));
						$this->document->Cell(80, 5, $this->lang->_("COM_THM_ORGANIZER_RECEIPT"), 0, 0, 'C');

						$this->document->SetFont('', 'B', 10);
						$this->document->SETXY($xPos[1], 27 + ($l * 82));
						$this->document->Cell(80, 5, $this->courseData["name"], 0, 0, 'C');

						$this->document->SetFont('', 'B', 11);
						$this->document->SETXY($xPos[1], 37 + ($l * 82));
						$this->document->MultiCell(80, 5, $this->lang->_("COM_THM_ORGANIZER_BADGE_PAYMENT_TEXT"), 0, 'C');

						$this->document->SetFont('', '', 8);
						$this->document->SETXY($xPos[1], 50 + ($l * 82));
						$this->document->MultiCell(80, 5, $this->lang->_("COM_THM_ORGANIZER_BADGE_TAX_TEXT"), 0, 'C');

						$this->document->SetFont('', '', 6);
						$this->document->SETXY($xPos[1], 83 + ($l * 82));
						$this->document->Cell(80, 5, $params->get('address'), 0, 0, 'C');

						$this->document->SETXY($xPos[1], 86 + ($l * 82));
						$this->document->Cell(80, 5, $params->get('contact'), 0, 0, 'C');
					}

					for ($x = 0; $x < count($xPos); ++$x)
					{
						$xPos[$x] += 92;
					}
				}
			}

			if (($count == 3) AND ($i < $emptyCells - 2))
			{
				$this->document->AddPage('L', '', false, false);

				$xPos  = [10, 15, 35, 57, 70, 68, 65, 40, 78];
				$count = 0;
			}
		}
	}

	/**
	 * Output pdf with a Table containing all participants and places for additional participants
	 *
	 * @return void
	 */
	protected function render()
	{
		$this->document = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		$this->document->SetTitle(
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_PDF_BADGES") . ' - ' .
			$this->courseData["name"] . ' - ' . $this->courseData["c_start"]
		);

		$this->createBadges();

		$filename = urldecode($this->courseData["name"] . '_' . $this->courseData["c_start"])
			. '_' .
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_PDF_BADGES") .
			'.pdf';
		$this->document->Output($filename, 'I');

		ob_flush();
	}
}
