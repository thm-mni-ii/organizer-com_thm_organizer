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

use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;

/**
 * Class generates sheets of participant badges based on the participants.
 */
class Badges extends BaseLayout
{
	use CourseContext;

	private $rectangleStyle = [
		'width' => 0.1,
		'cap'   => 'butt',
		'join'  => 'miter',
		'dash'  => 0,
		'color' => [0, 0, 0]
	];

	private $params;

	/**
	 * Performs initial construction of the TCPDF Object.
	 *
	 * @param   int  $courseID  the id of the course for which the badges are valid
	 */
	public function __construct($courseID)
	{
		parent::__construct();

		$this->params = Input::getParams();
		$this->setCourse($courseID);

		$documentName = "$this->name - $this->term " . Languages::_('THM_ORGANIZER_BADGE_SHEETS');
		$this->setNames($documentName);
		$this->margins();
		$this->setHeader();
		$this->SetPrintHeader(false);
		$this->SetPrintFooter(false);

		$this->SetFont('', 'BI', 15);
	}

	/**
	 * Adds a badge position to the sheet
	 *
	 * @param   int    $badgeNumber  the index of the participant in the participants list
	 * @param   array  $participant  the participant being iterated
	 * @param   int    $xOffset      the reference value for x
	 * @param   int    $yOffset      the reference value for y
	 *
	 * @return void modifies the pdf document
	 */
	private function addBadge($participant, $xOffset, $yOffset)
	{
		$center = $xOffset + 5;
		$this->SetLineStyle($this->rectangleStyle);
		$this->Rect($xOffset, $yOffset + 10, 90, 80);
		$this->Image(K_PATH_IMAGES . 'thm_logo.png', $xOffset + 30, $yOffset + 15, 30, 0);
		$this->SetFont('', '', 10);

		$this->SetFont('', 'B', 11);
		$this->SETXY($xOffset + 72, $yOffset + 15);
		$this->Cell(16, 5, $participant['id'], 1, 0, 'C');

		$this->SETXY($center, $yOffset + 28);
		$headerLine   = "$this->name $this->term";
		$headerLength = strlen($headerLine);
		if ($headerLength > 35)
		{
			$this->MultiCell(80, 5, $headerLine, 0, 'C', false, 2);
			$titleOffset = 10;
		}
		else
		{
			$this->Cell(80, 5, $headerLine, 0, 0, 'C');
			$titleOffset = 0;
		}

		$this->SetFont('', '', 10);
		$this->SETXY($center, $yOffset + $titleOffset + 33);
		$dateLine = $this->dates;

		if (!empty($this->campus))
		{
			$dateLine .= strlen($this->campus) > 10 ? '\n' : '';
			$dateLine .= ' in ' . $this->campus;
			$this->MultiCell(80, 5, $dateLine, 0, 'C', false, 2);
		}
		else
		{
			$this->Cell(80, 5, $dateLine, 0, 0, 'C');
		}

		$halfTitleOffset = $titleOffset / 2;
		$this->Ln();
		$this->SetFont('', 'B', 20);
		$this->SETXY($center, $yOffset + $halfTitleOffset + 45);
		$this->Cell(80, 5, Languages::_('THM_ORGANIZER_BADGE'), 0, 0, 'C');
		$this->SETXY($center, $yOffset + 45);

		$this->Ln();
		$this->SetFont('', '', 10);
		$this->SETXY($center, $yOffset + 63);
		$this->Cell(20, 5, 'Name: ', 0, 0, 'L');
		$this->SetFont('', 'B', 10);
		$participantName = empty($participant['forename']) ?
			$participant['surname'] : "{$participant['surname']},  {$participant['forename']}";
		$this->Cell(65, 5, $participantName, 0, 0, 'L');

		$this->Ln();
		$this->SetFont('', '', 10);
		$this->SETXY($center, $yOffset + 68);
		$this->Cell(20, 5, Languages::_('THM_ORGANIZER_ADDRESS') . ': ', 0, 0, 'L');
		$this->Cell(65, 5, $participant['address'], 0, 0, 'L');

		$this->Ln();
		$this->SETXY($center, $yOffset + 73);
		$this->Cell(20, 5, Languages::_('THM_ORGANIZER_RESIDENCE') . ': ', 0, 0, 'L');
		$this->Cell(65, 5, "{$participant['zipCode']} {$participant['city']}", 0, 0, 'L');
	}

	/**
	 * Adds a badge reverse to the sheet reverse
	 *
	 * @param   int  $xOffset  the reference x offset for the box
	 * @param   int  $yOffset  the reference y offset for the box
	 *
	 * @return void modifies the pdf document
	 */
	private function addBadgeBack($xOffset, $yOffset)
	{
		$badgeCenter = $xOffset + 5;

		$this->SetLineStyle($this->rectangleStyle);
		$this->Rect($xOffset, 10 + $yOffset, 90, 80);

		if (empty($this->fee))
		{
			$addressY        = 78 + $yOffset;
			$contactY        = 74 + $yOffset;
			$nameY           = 32 + $yOffset;
			$receiptY        = 20 + $yOffset;
			$repNameY        = 63 + $yOffset;
			$representativeY = 43 + $yOffset;
			$signatureY      = 48 + $yOffset;
		}
		else
		{
			$addressY        = 83 + $yOffset;
			$contactY        = 86 + $yOffset;
			$nameY           = 27 + $yOffset;
			$receiptY        = 15 + $yOffset;
			$repNameY        = 79 + $yOffset;
			$representativeY = 58 + $yOffset;
			$signatureY      = 64 + $yOffset;

			$this->SetFont('', 'B', 11);
			$this->SETXY($badgeCenter, 37 + $yOffset);
			$this->MultiCell(80, 5, Languages::_('THM_ORGANIZER_BADGE_PAYMENT_TEXT'), 0, 'C');

			$this->SetFont('', '', 8);
			$this->SETXY($badgeCenter, 50 + $yOffset);
			$this->MultiCell(80, 5, Languages::_('THM_ORGANIZER_BADGE_TAX_TEXT'), 0, 'C');
		}

		$this->SetFont('', 'BU', 20);
		$this->SETXY($badgeCenter, $receiptY);
		$this->Cell(80, 5, Languages::_('THM_ORGANIZER_RECEIPT'), 0, 0, 'C');

		$this->SetFont('', 'B', 10);
		if (strlen($this->name) > 35)
		{
			$this->SETXY($badgeCenter, $nameY - 2);
			$this->MultiCell(80, 5, $this->name, 0, 'C', false, 2);
		}
		else
		{
			$this->SETXY($badgeCenter, $nameY);
			$this->Cell(80, 5, $this->name, 0, 0, 'C');
		}

		$this->SetFont('', '', 6);
		$this->SETXY($badgeCenter, $representativeY);
		$this->Cell(80, 5, Languages::_('THM_ORGANIZER_REPRESENTATIVE'), 0, 0, 'C');

		if (!empty($this->params->get('signatureFile')))
		{
			$signaturePath = K_PATH_IMAGES . $this->params->get('signatureFile');
			$this->Image($signaturePath, $xOffset + 35, $signatureY, 20, 0);
		}

		$this->SetFont('', '', 7);
		$this->SETXY($badgeCenter, $repNameY);
		$this->Cell(80, 5, $this->params->get('representativeName', ''), 0, 0, 'C');

		$this->SetFont('', '', 6);
		$this->SETXY($badgeCenter, $addressY);
		$this->Cell(80, 5, $this->params->get('address'), 0, 0, 'C');

		$this->SETXY($badgeCenter, $contactY);
		$this->Cell(80, 5, $this->params->get('contact'), 0, 0, 'C');
	}

	/**
	 * Adds the reverse to a badge sheet
	 *
	 * @return void modifies the pdf document
	 */
	private function addSheetBack()
	{
		$this->AddPage('L', '', false, false);

		$xOffset = 14;

		for ($boxNo = 0; $boxNo < 3; $boxNo++)
		{
			for ($level = 0; $level < 2; $level++)
			{
				// The next item should be 82 to the right
				$yOffset = $level * 82;

				$this->addBadgeBack($xOffset, $yOffset);
			}

			// The next row should be 92 lower
			$xOffset += 92;
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
		$sheetCount = intval(count($participants) / 6);
		$badgeCount = $sheetCount * 6;

		$this->AddPage('l');

		$emptyParticipant = [
			'address'  => '',
			'city'     => '',
			'forename' => '',
			'id'       => '',
			'surname'  => '',
			'zipCode'  => ''
		];

		$xOffset = 10;
		$yOffset = 0;

		for ($index = 0; $index < $badgeCount; $index++)
		{
			$badgeNumber = $index + 1;
			$participant = empty($participants[$index]) ? $emptyParticipant : $participants[$index];
			$this->addBadge($participant, $xOffset, $yOffset);

			// End of the sheet
			if ($badgeNumber % 6 == 0)
			{
				$xOffset = 10;
				$yOffset = 0;
				$this->addSheetBack();

				if ($badgeNumber < $badgeCount)
				{
					$this->AddPage('L', '', false, false);
				}
			} // End of the first row on a sheet
			elseif ($badgeNumber % 3 == 0)
			{
				$xOffset = 10;
				$yOffset = 82;
			} // Next item
			else
			{
				$xOffset += 92;
			}
		}
	}
}
