<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2020 TH Mittelhessen
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
		$this->SetLineStyle($this->rectangleStyle);
		$this->Rect($xOffset, $yOffset + 10, 90, 80);

		$left = $xOffset + 4;
		$this->Image(K_PATH_IMAGES . 'thm_logo.png', $left, $yOffset + 15, 30, 0);

		$this->changePosition($xOffset + 70, $yOffset + 15);
		$this->changeFont(self::REGULAR, 10);
		$this->renderCell(16, 5, $participant['id'], self::CENTER, self::ALL);

		$this->changePosition($left, $yOffset + 29);
		$this->changeFont(self::BOLD, 12);
		$headerLine = "$this->name $this->term";
		$this->renderMultiCell(80, 5, $headerLine, self::CENTER);

		$titleOffset = strlen($headerLine) > 35 ? 12 : 2;

		$this->changePosition($left, $yOffset + $titleOffset + 33);
		$this->changeFont(self::REGULAR, 10);
		$dateLine = $this->campus ?
			$this->dates . ' ' . Languages::_('THM_ORGANIZER_CAMPUS') . ' ' . $this->campus : $this->dates;
		$this->renderMultiCell(80, 5, $dateLine, self::CENTER);

		$halfTitleOffset = $titleOffset / 2;
		$this->Ln();
		$this->changeFont(self::BOLD, 20);
		$this->changePosition($left, $yOffset + $halfTitleOffset + 45);
		$this->renderCell(80, 5, Languages::_('THM_ORGANIZER_BADGE'), self::CENTER);

		$this->changePosition($left, $yOffset + 45);
		$this->changeFont(self::REGULAR, 10);

		$participantName = $participant['surname'];
		$participantName .= empty($participant['forename']) ? '' : ",  {$participant['forename']}";

		$this->Ln();
		$this->changePosition($left, $yOffset + 63);
		$this->renderCell(20, 5, Languages::_('THM_ORGANIZER_NAME') . ': ');
		$this->changeFont(self::BOLD);
		$this->renderCell(65, 5, $participantName);
		$this->changeFont(self::REGULAR);

		$this->Ln();
		$this->changePosition($left, $yOffset + 68);
		$this->renderCell(20, 5, Languages::_('THM_ORGANIZER_ADDRESS') . ': ');
		$this->renderCell(65, 5, $participant['address']);

		$this->Ln();
		$this->changePosition($left, $yOffset + 73);
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
		$this->SetLineStyle($this->rectangleStyle);
		$this->Rect($xOffset, 10 + $yOffset, 90, 80);

		$badgeCenter = $xOffset + 5;

		if ($this->fee)
		{
			$headerOffset    = 12 + $yOffset;
			$titleOffset     = 26 + $yOffset;
			$labelOffset     = 55 + $yOffset;
			$signatureOffset = 61 + $yOffset;
			$nameOffset      = 76 + $yOffset;
			$addressOffset   = 80 + $yOffset;
			$contactOffset   = 83 + $yOffset;
		}
		else
		{
			$headerOffset    = 17 + $yOffset;
			$titleOffset     = 31 + $yOffset;
			$labelOffset     = 42 + $yOffset;
			$signatureOffset = 47 + $yOffset;
			$nameOffset      = 62 + $yOffset;
			$addressOffset   = 73 + $yOffset;
			$contactOffset   = 76 + $yOffset;
		}

		$this->changeFont(self::BOLD, 20);
		$this->changePosition($badgeCenter, $headerOffset);
		$this->renderCell(80, 5, Languages::_('THM_ORGANIZER_RECEIPT'), self::CENTER);

		$this->changeFont(self::BOLD, 12);
		$title       = "$this->name $this->term";
		$titleOffset = strlen($title) > 50 ? $titleOffset - 2 : $titleOffset;
		$this->changePosition($badgeCenter, $titleOffset);
		$this->renderMultiCell(80, 5, $title, self::CENTER);

		if ($this->fee)
		{
			$this->changePosition($badgeCenter, 37 + $yOffset);
			$this->changeFont(self::REGULAR, 11);
			$this->renderMultiCell(80, 5, Languages::_('THM_ORGANIZER_BADGE_PAYMENT_TEXT'), self::CENTER);

			$this->changePosition($badgeCenter, 50 + $yOffset);
			$this->changeFont(self::ITALIC, 6);
			$this->renderMultiCell(80, 5, Languages::_('THM_ORGANIZER_BADGE_TAX_TEXT'), self::CENTER);
		}

		$this->changeSize(8);
		$this->changePosition($badgeCenter, $labelOffset);
		$this->renderCell(80, 5, Languages::_('THM_ORGANIZER_REPRESENTATIVE'), self::CENTER);

		if (!empty($this->params->get('signatureFile')))
		{
			$signaturePath = K_PATH_IMAGES . $this->params->get('signatureFile');
			$this->Image($signaturePath, $xOffset + 35, $signatureOffset, 20, 0);
		}

		$this->changeSize(7);
		$this->changePosition($badgeCenter, $nameOffset);
		$this->renderCell(80, 5, $this->params->get('representativeName', ''), self::CENTER);

		$this->changeSize(6);
		$this->changePosition($badgeCenter, $addressOffset);
		$this->renderCell(80, 5, $this->params->get('address'), self::CENTER);

		$this->changePosition($badgeCenter, $contactOffset);
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
