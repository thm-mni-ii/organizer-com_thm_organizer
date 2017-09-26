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

class THMOrganizerTemplatePC_By_Department_Export extends THM_OrganizerTemplatePC_Export
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
	 * Get data for department list
	 *
	 * @return array Course data
	 */
	private function getDepartmentsOfCourse()
	{
		$departments = [];

		foreach ($this->courseData["participants"] as $data)
		{
			if ($data["departmentName"] === null)
			{
				$data["departmentName"] = $this->lang->_("JNONE");
			}

			array_push($departments, $data["departmentName"]);
		}

		$departments = array_unique($departments);

		return $departments;
	}

	/**
	 * Get data for course of study
	 *
	 * @param int $dp Department id
	 *
	 * @return array Course data
	 */
	private function evaluateCoursesOfStudy($dp)
	{
		$departmentData = [];
		$departments    = [];

		foreach ($this->courseData["participants"] as $data)
		{
			if ($data["programName"] === null)
			{
				$data["programName"] = $this->lang->_("JNONE");
			}

			if ($data["departmentName"] === null)
			{
				$data["departmentName"] = $this->lang->_("JNONE");
			}

			array_push($departments, $data["departmentName"]);
			array_push($departmentData, [$data["departmentName"], $data["programName"]]);
		}

		$courseOfStudy = array_count_values(
			array_map(
				function ($item) {
					return $item['1'];
				}, $departmentData
			)
		);

		$results = [];
		foreach ($departmentData as $fb)
		{
			array_push($results, [$fb[0], $fb[1], $courseOfStudy[$fb[1]]]);
		}

		$last = [];
		foreach ($results as $erg)
		{
			if ($erg[0] === $dp)
			{
				$last[$erg[1]] = [$erg[1], $erg[2]];
			}
		}

		return $last;
	}

	/**
	 * Add Table with departments and number of participants for the departments to TCPDF
	 *
	 * @return void
	 */
	private function createDepartmentTable()
	{
		$header = [
			$this->lang->_("COM_THM_ORGANIZER_DEPARTMENT") . ' - ' . $this->lang->_("COM_THM_ORGANIZER_PROGRAM"),
			$this->lang->_("COM_THM_ORGANIZER_PARTICIPANTS")
		];
		$widths = [155, 25];

		$departments = $this->getDepartmentsOfCourse();

		$this->document->SetFillColor(210);
		$this->document->SetFont('', 'B');

		$num_headers = count($header);
		for ($i = 0; $i < $num_headers; ++$i)
		{
			$this->document->Cell($widths[$i], 7, $header[$i], 1, 0, 'L', 1);
		}

		$this->document->Ln();

		$this->document->SetFillColor(235, 252, 238);
		$this->document->SetTextColor(0);
		$this->document->SetFont('');

		foreach ($departments as $id => $dep)
		{
			$coursesOfStudy = $this->evaluateCoursesOfStudy($dep);
			$count          = 0;
			foreach ($coursesOfStudy as $cos)
			{
				$count += $cos[1];
			}

			$id++;
			$maxNoCells   = 0;
			$maxCellCount = 0;
			$maxArray     = [];

			$startX = $this->document->GetX();
			$startY = $this->document->GetY();

			$cells = [$dep, $count];
			for ($i = 0; $i < count($cells); ++$i)
			{
				$cellCount = $this->document->MultiCell($widths[$i], 5, $cells[$i], 'T', 'L', 1, 0);
				if ($cellCount > $maxNoCells)
				{
					$maxNoCells = $cellCount;
				}

				array_push($maxArray, $maxCellCount);
			}

			$this->document->SetXY($startX, $startY);

			$this->document->MultiCell($widths[0], $maxNoCells * 5, '', 'LR', 'L', 0, 0);
			$this->document->MultiCell($widths[1], $maxNoCells * 5, '', 'LR', 'L', 0, 0);
			$this->document->Ln();

			if ($this->document->getY() + $maxCellCount > 260)
			{
				$this->document->addPage();
				$this->document->setY(34);
			}

			foreach ($coursesOfStudy as $cos)
			{
				$id++;
				$maxNoCells   = 0;
				$maxCellCount = 0;
				$maxArray     = [];

				$startX = $this->document->GetX();
				$startY = $this->document->GetY();

				$cells = ['     ' . $cos[0], $cos[1]];
				for ($i = 0; $i < count($cells); ++$i)
				{
					$cellCount = $this->document->MultiCell($widths[$i], 5, $cells[$i], 'T', 'L', 0, 0);
					if ($cellCount > $maxNoCells)
					{
						$maxNoCells = $cellCount;
					}

					array_push($maxArray, $maxCellCount);
				}

				$this->document->SetXY($startX, $startY);

				$this->document->MultiCell($widths[0], $maxNoCells * 5, '', 'LR', 'L', 0, 0);
				$this->document->MultiCell($widths[1], $maxNoCells * 5, '', 'LR', 'L', 0, 0);
				$this->document->Ln();

				if ($this->document->getY() + $maxCellCount > 260)
				{
					$this->document->addPage();
					$this->document->setY(34);
				}
			}

			$startX = $this->document->GetX();
			$startY = $this->document->GetY();

			foreach ($widths as $w)
			{
				$this->document->MultiCell($w, 5, '', 'LRTB', 'L', 0, 0);
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
	 * Output PDF containing information about how many participants from different departments
	 * are signed in to the lesson
	 *
	 * @return void
	 */
	protected function render()
	{
		$this->document->SetTitle(
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_PDF_DEPARTMENTS") . ' - ' .
			$this->courseData["name"] . ' - ' . $this->courseData["c_start"]
		);
		$this->setHeader();

		$this->document->AddPage();

		$this->createDepartmentTable();

		$filename = urlencode($this->courseData["name"] . '_' . $this->courseData["c_start"])
			. '_' .
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_PDF_DEPARTMENTS") .
			'.pdf';
		$this->document->Output($filename, 'I');

		ob_flush();
	}
}
