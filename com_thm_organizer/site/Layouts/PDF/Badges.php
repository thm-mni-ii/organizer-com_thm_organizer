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

	private $emptyParticipant = [
		'address'  => '',
		'city'     => '',
		'forename' => '',
		'id'       => '',
		'surname'  => '',
		'zipCode'  => ''
	];

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

		$documentName = "$this->name - $this->term - " . Languages::_('THM_ORGANIZER_BADGE_SHEETS');
		$this->setNames($documentName);
		$this->margins();
		$this->showPrintOverhead(false);
	}

	/**
	 * Adds a badge position to the sheet
	 *
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
		$this->changeSize(10);

		$this->changeFont(self::BOLD, 11);
		$this->changePosition($xOffset + 72, $yOffset + 15);
		$this->renderCell(16, 5, $participant['id'], self::CENTER, self::ALL);

		$this->changePosition($center, $yOffset + 28);
		$headerLine   = "$this->name $this->term";
		$headerLength = strlen($headerLine);
		if ($headerLength > 35)
		{
			$this->MultiCell(80, 5, $headerLine);
			$titleOffset = 10;
		}
		else
		{
			$this->renderCell(80, 5, $headerLine, self::CENTER);
			$titleOffset = 0;
		}

		$this->changeSize(10);
		$this->changePosition($center, $yOffset + $titleOffset + 33);
		$dateLine = $this->dates;

		if (!empty($this->campus))
		{
			$dateLine .= strlen($this->campus) > 10 ? '\n' : '';
			$dateLine .= ' in ' . $this->campus;
			$this->MultiCell(80, 5, $dateLine);
		}
		else
		{
			$this->renderCell(80, 5, $dateLine, self::CENTER);
		}

		$halfTitleOffset = $titleOffset / 2;
		$this->Ln();
		$this->changeFont(self::BOLD, 20);
		$this->changePosition($center, $yOffset + $halfTitleOffset + 45);
		$this->renderCell(80, 5, Languages::_('THM_ORGANIZER_BADGE'), self::CENTER);

		$this->changePosition($center, $yOffset + 45);
		$this->changeFont(self::REGULAR, 10);

		$participantName = $participant['surname'];
		$participantName .= empty($participant['forename']) ? '' : ",  {$participant['forename']}";

		$this->Ln();
		$this->changePosition($center, $yOffset + 63);
		$this->renderCell(20, 5, Languages::_('THM_ORGANIZER_NAME') . ': ');
		$this->changeFont(self::BOLD);
		$this->renderCell(65, 5, $participantName);
		$this->changeFont(self::REGULAR);

		$this->Ln();
		$this->changePosition($center, $yOffset + 68);
		$this->renderCell(20, 5, Languages::_('THM_ORGANIZER_ADDRESS') . ': ');
		$this->renderCell(65, 5, $participant['address']);

		$this->Ln();
		$this->changePosition($center, $yOffset + 73);
		$this->renderCell(20, 5, Languages::_('THM_ORGANIZER_RESIDENCE') . ': ');
		$this->renderCell(65, 5, "{$participant['zipCode']} {$participant['city']}");
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

			$this->changePosition($badgeCenter, 37 + $yOffset);
			$this->changeFont(self::BOLD, 11);
			$this->MultiCell(80, 5, Languages::_('THM_ORGANIZER_BADGE_PAYMENT_TEXT'));

			$this->changePosition($badgeCenter, 50 + $yOffset);
			$this->changeFont(self::REGULAR, 8);
			$this->MultiCell(80, 5, Languages::_('THM_ORGANIZER_BADGE_TAX_TEXT'));
		}

		$this->changeFont(self::BOLD_UNDERLINE, 20);
		$this->changePosition($badgeCenter, $receiptY);
		$this->renderCell(80, 5, Languages::_('THM_ORGANIZER_RECEIPT'), self::CENTER);

		$this->changeFont(self::BOLD, 10);
		if (strlen($this->name) > 35)
		{
			$this->changePosition($badgeCenter, $nameY - 2);
			$this->MultiCell(80, 5, $this->name);
		}
		else
		{
			$this->changePosition($badgeCenter, $nameY);
			$this->renderCell(80, 5, $this->name, self::CENTER);
		}

		$this->changeSize(6);
		$this->changePosition($badgeCenter, $representativeY);
		$this->renderCell(80, 5, Languages::_('THM_ORGANIZER_REPRESENTATIVE'), self::CENTER);

		if (!empty($this->params->get('signatureFile')))
		{
			$signaturePath = K_PATH_IMAGES . $this->params->get('signatureFile');
			$this->Image($signaturePath, $xOffset + 35, $signatureY, 20, 0);
		}

		$this->changeSize(7);
		$this->changePosition($badgeCenter, $repNameY);
		$this->renderCell(80, 5, $this->params->get('representativeName', ''), self::CENTER);

		$this->changeSize(6);
		$this->changePosition($badgeCenter, $addressY);
		$this->renderCell(80, 5, $this->params->get('address'), self::CENTER);

		$this->changePosition($badgeCenter, $contactY);
		$this->renderCell(80, 5, $this->params->get('contact'), self::CENTER);
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

		$this->AddPage(self::LANDSCAPE);

		$xOffset = 10;
		$yOffset = 0;

		for ($index = 0; $index < $badgeCount; $index++)
		{
			$badgeNumber = $index + 1;
			$participant = empty($participants[$index]) ? $this->emptyParticipant : $participants[$index];
			$this->addBadge($participant, $xOffset, $yOffset);

			// End of the sheet
			if ($badgeNumber % 6 == 0)
			{
				$xOffset = 10;
				$yOffset = 0;
				$this->addSheetBack();

				if ($badgeNumber < $badgeCount)
				{
					$this->AddPage(self::LANDSCAPE);
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
