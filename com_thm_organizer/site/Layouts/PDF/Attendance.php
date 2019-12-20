<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Layouts\PDF;

use Organizer\Helpers;

/**
 * Displays a list of participant entries.
 */
class Attendance extends BaseLayout
{
	use CourseContext;

	private $columnHeaders;

	private $widths;

	/**
	 * Performs initial construction of the TCPDF Object.
	 *
	 * @param   int  $courseID  the id of the course for which the badges are valid
	 */
	public function __construct($courseID)
	{
		parent::__construct();

		$this->setCourse($courseID);

		$exportType = Helpers\Languages::_('THM_ORGANIZER_PARTICIPANTS');
		$this->setNames($exportType);

		$this->setHeader();

		$this->columnHeaders = [
			'index'      => '#',
			'name'       => 'Name',
			'department' => Helpers\Languages::_('THM_ORGANIZER_DEPARTMENT'),
			'program'    => Helpers\Languages::_('THM_ORGANIZER_PROGRAM'),
			'room'       => Helpers\Languages::_('THM_ORGANIZER_ROOM')
		];

		$this->widths = [
			'index'      => 8,
			'name'       => 60,
			'department' => 28,
			'program'    => 59,
			'room'       => 25
		];

		if ($this->fee)
		{
			$this->columnHeaders['paid'] = Helpers\Languages::_('THM_ORGANIZER_PAID');
			$this->widths['name']        = 40;
			$this->widths['paid']        = 20;
		}
	}

	/**
	 * Adds a new page to the document and creates the column headers for the table
	 *
	 * @return void
	 */
	private function addAttendancePage()
	{
		$this->AddPage();

		// create the column headers for the page
		$this->SetFillColor(210);
		$this->SetFont('', 'B');
		foreach (array_keys($this->columnHeaders) as $columnName)
		{
			$this->Cell($this->widths[$columnName], 7, $this->columnHeaders[$columnName], 1, 0, 'L', 1);
		}
		$this->Ln();

		// reset styles
		$this->SetFillColor(235, 252, 238);
		$this->SetFont('');
	}

	/**
	 * Add Table with participant information and additional places for students to TCPDF
	 *
	 * @return void
	 */
	private function createParticipantTable()
	{

		// Create empty cells for 25% more participants and round to a multiple of 6 due to the passports nature
		$bufferSize = ceil(count($participants) * 1.25) + 1;
		for ($itemNo; $itemNo < $bufferSize; $itemNo++)
		{
			foreach (array_keys($this->columnHeaders) as $columnName)
			{
				$value = $columnName == 'index' ? $itemNo : '';
				$this->MultiCell($this->widths[$columnName], 5, $value, 'LRB', 'L', 0, 0);
			}

			$this->Ln();

			if ($this->getY() > 260)
			{
				$this->Cell(array_sum($this->widths), 0, '', 'T');
				$this->addPage();
			}
		}
	}

	/**
	 * Renders the document.
	 *
	 * @param   array  $participants  the course participants
	 *
	 * @return void renders the document and closes the application
	 */
	public function fill($participants)
	{
		$this->addAttendancePage();

		$itemNo = 1;

		foreach ($participants as $participant)
		{
			// Get the starting coordinates for later use with borders
			$startX = $this->GetX();
			$startY = $this->GetY();

			$maxLength = 0;

			foreach (array_keys($this->columnHeaders) as $columnName)
			{
				switch ($columnName)
				{
					case 'index':
						$value = $itemNo;
						break;
					case 'name':
						$value = empty($participant['forename']) ?
							$participant['surname'] : "{$participant['surname']},  {$participant['forename']}";
						break;
					case 'department':
						$value = $participant['departmentName'];
						break;
					case 'program':
						$value = $participant['programName'];
						break;
					default:
						$value = '';
						break;
				}

				$length = $this->MultiCell($this->widths[$columnName], 5, $value, '', 'L', 0, 0);
				if ($length > $maxLength)
				{
					$maxLength = $length;
				}
			}

			// Reset for borders
			$this->SetXY($startX, $startY);

			foreach ($this->widths as $index => $width)
			{
				if ($index == 'index')
				{
					$this->MultiCell($width, $maxLength * 5, '', 'LRB', 'L', 0, 0);
				}
				else
				{
					$this->MultiCell($width, $maxLength * 5, '', 'RB', 'L', 0, 0);
				}
			}

			$this->Ln();

			if ($this->getY() > 260)
			{
				$this->addPage();
			}

			$itemNo++;
		}
	}
}
