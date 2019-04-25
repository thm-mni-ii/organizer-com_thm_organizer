<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
require_once 'course_export.php';

/**
 * Class generates sheets of participant badges based on the registered participants.
 */
class THM_OrganizerTemplateBadges extends THM_OrganizerTemplateCourse_Export
{
    private $rectangleStyle;

    /**
     * THMOrganizerTemplatePC_Badges_Export constructor.
     *
     * @param int $lessonID the lessonID of the exported course
     *
     * @throws \Exception => invalid request / unauthorized access / not found
     */
    public function __construct($lessonID)
    {
        parent::__construct($lessonID);

        $exportType = $this->lang->_('THM_ORGANIZER_BADGE_SHEETS');
        $this->setNames($exportType);

        $this->document->setPrintHeader(false);
        $this->document->setPrintFooter(false);
        $this->document->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->document->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->document->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $this->document->SetFont('', 'BI', 15);

        $this->rectangleStyle = [
            'width' => 0.1,
            'cap'   => 'butt',
            'join'  => 'miter',
            'dash'  => 0,
            'color' => [0, 0, 0]
        ];

        $this->createBadges();

        $this->document->Output($this->filename, 'I');

        ob_flush();
    }

    /**
     * Output passports for all participants of a lesson
     *
     * @return void
     */
    public function createBadges()
    {
        $participantCount = count($this->course['participants']);
        $bufferCount      = $participantCount * 1.25;
        $sheetCount       = intval($bufferCount / 6) + 1;
        $badgeCount       = $sheetCount * 6;

        $this->document->AddPage('L', '', false, false);
        $xOffset = 10;
        $yOffset = 0;

        for ($badgeNumber = 0; $badgeNumber < $badgeCount; $badgeNumber++) {

            $this->addBadge($badgeNumber, $xOffset, $yOffset);

            // Adds one for use in modulo calculations
            $modNumber = $badgeNumber + 1;

            // End of the sheet
            if ($modNumber % 6 == 0) {
                $xOffset = 10;
                $yOffset = 0;
                $this->addSheetBack();
                if ($badgeNumber + 1 < $badgeCount) {
                    $this->document->AddPage('L', '', false, false);
                }
            } // End of the first row on a sheet
            elseif ($modNumber % 3 == 0) {
                $xOffset = 10;
                $yOffset = 82;
            } // Next item
            else {
                $xOffset += 92;
            }
        }
    }

    /**
     * Adds a badge position to the sheet
     *
     * @param int $participantIndex the index of the participant in the participants list
     * @param int $xOffset          the reference value for x
     * @param int $yOffset          the reference value for y
     *
     * @return void modifies the pdf document
     */
    private function addBadge($participantIndex, $xOffset, $yOffset)
    {
        $center = $xOffset + 5;
        $this->document->SetLineStyle($this->rectangleStyle);
        $this->document->Rect($xOffset, $yOffset + 10, 90, 80);
        $this->document->Image(K_PATH_IMAGES . 'thm_logo.png', $xOffset + 30, $yOffset + 15, 30, 0);
        $this->document->SetFont('', '', 10);

        $this->document->SetFont('', 'B', 11);
        $this->document->SETXY($xOffset + 75, $yOffset + 15);
        $this->document->Cell(10, 5, $participantIndex + 1, 1, 0, 'C');

        if ($participantIndex >= count($this->course['participants'])) {
            $participant = ['userName' => '', 'zip_code' => '', 'city' => '', 'address' => ''];
        } else {
            $participant = $this->course['participants'][$participantIndex];
        }

        $this->document->SETXY($center, $yOffset + 28);
        $headerLine   = "{$this->course['name']} {$this->course['planningPeriodName']}";
        $headerLength = strlen($headerLine);
        if ($headerLength > 35) {
            $this->document->MultiCell(80, 5, $headerLine, 0, 'C', false, 2);
            $titleOffset = 10;
        } else {
            $this->document->Cell(80, 5, $headerLine, 0, 0, 'C');
            $titleOffset = 0;
        }

        $this->document->SetFont('', '', 10);
        $this->document->SETXY($center, $yOffset + $titleOffset + 33);
        $dateLine = $this->course['start'] . ' - ' . $this->course['end'];

        if (!empty($this->course['place'])) {
            $dateLine .= strlen($this->course['place']) > 10 ? '\n' : '';
            $dateLine .= ' in ' . $this->course['place'];
            $this->document->MultiCell(80, 5, $dateLine, 0, 'C', false, 2);
        } else {
            $this->document->Cell(80, 5, $dateLine, 0, 0, 'C');
        }

        $halfTitleOffset = $titleOffset / 2;
        $this->document->Ln();
        $this->document->SetFont('', 'B', 20);
        $this->document->SETXY($center, $yOffset + $halfTitleOffset + 45);
        $this->document->Cell(80, 5, $this->lang->_('THM_ORGANIZER_BADGE'), 0, 0, 'C');
        $this->document->SETXY($center, $yOffset + 45);

        $this->document->Ln();
        $this->document->SetFont('', '', 10);
        $this->document->SETXY($center, $yOffset + 63);
        $this->document->Cell(20, 5, 'Name: ', 0, 0, 'L');
        $this->document->SetFont('', 'B', 10);
        $this->document->Cell(65, 5, $participant['userName'], 0, 0, 'L');

        $this->document->Ln();
        $this->document->SetFont('', '', 10);
        $this->document->SETXY($center, $yOffset + 68);
        $this->document->Cell(20, 5, $this->lang->_('THM_ORGANIZER_ADDRESS') . ': ', 0, 0, 'L');
        $this->document->Cell(65, 5, $participant['address'], 0, 0, 'L');

        $this->document->Ln();
        $this->document->SETXY($center, $yOffset + 73);
        $this->document->Cell(20, 5, $this->lang->_('THM_ORGANIZER_RESIDENCE') . ': ', 0, 0, 'L');
        $this->document->Cell(65, 5, "{$participant['zip_code']} {$participant['city']}", 0, 0, 'L');
    }

    /**
     * Adds a badge reverse to the sheet reverse
     *
     * @param int $xOffset the reference x offset for the box
     * @param int $yOffset the reference y offset for the box
     *
     * @return void modifies the pdf document
     */
    private function addBadgeBack($xOffset, $yOffset)
    {
        $params = THM_OrganizerHelperComponent::getParams();

        $badgeCenter = $xOffset + 5;

        $this->document->SetLineStyle($this->rectangleStyle);
        $this->document->Rect($xOffset, 10 + $yOffset, 90, 80);

        if (empty($this->course['fee'])) {
            $addressY        = 78 + $yOffset;
            $contactY        = 74 + $yOffset;
            $nameY           = 32 + $yOffset;
            $receiptY        = 20 + $yOffset;
            $repNameY        = 63 + $yOffset;
            $representativeY = 43 + $yOffset;
            $signatureY      = 48 + $yOffset;
        } else {
            $addressY        = 83 + $yOffset;
            $contactY        = 86 + $yOffset;
            $nameY           = 27 + $yOffset;
            $receiptY        = 15 + $yOffset;
            $repNameY        = 79 + $yOffset;
            $representativeY = 58 + $yOffset;
            $signatureY      = 64 + $yOffset;

            $this->document->SetFont('', 'B', 11);
            $this->document->SETXY($badgeCenter, 37 + $yOffset);
            $this->document->MultiCell(80, 5, $this->lang->_('THM_ORGANIZER_BADGE_PAYMENT_TEXT'), 0, 'C');

            $this->document->SetFont('', '', 8);
            $this->document->SETXY($badgeCenter, 50 + $yOffset);
            $this->document->MultiCell(80, 5, $this->lang->_('THM_ORGANIZER_BADGE_TAX_TEXT'), 0, 'C');
        }

        $this->document->SetFont('', 'BU', 20);
        $this->document->SETXY($badgeCenter, $receiptY);
        $this->document->Cell(80, 5, $this->lang->_('THM_ORGANIZER_RECEIPT'), 0, 0, 'C');

        $this->document->SetFont('', 'B', 10);
        $titleLength = strlen($this->course['name']);
        if ($titleLength > 35) {
            $this->document->SETXY($badgeCenter, $nameY - 2);
            $this->document->MultiCell(80, 5, $this->course['name'], 0, 'C', false, 2);
        } else {
            $this->document->SETXY($badgeCenter, $nameY);
            $this->document->Cell(80, 5, $this->course['name'], 0, 0, 'C');
        }

        $this->document->SetFont('', '', 6);
        $this->document->SETXY($badgeCenter, $representativeY);
        $this->document->Cell(80, 5, $this->lang->_('THM_ORGANIZER_REPRESENTATIVE'), 0, 0, 'C');

        if (!empty($params->get('signatureFile'))) {
            $signaturePath = K_PATH_IMAGES . $params->get('signatureFile');
            $this->document->Image($signaturePath, $xOffset + 35, $signatureY, 20, 0);
        }

        $this->document->SetFont('', '', 7);
        $this->document->SETXY($badgeCenter, $repNameY);
        $this->document->Cell(80, 5, $params->get('representativeName', ''), 0, 0, 'C');

        $this->document->SetFont('', '', 6);
        $this->document->SETXY($badgeCenter, $addressY);
        $this->document->Cell(80, 5, $params->get('address'), 0, 0, 'C');

        $this->document->SETXY($badgeCenter, $contactY);
        $this->document->Cell(80, 5, $params->get('contact'), 0, 0, 'C');
    }

    /**
     * Adds the reverse to a badge sheet
     *
     * @return void modifies the pdf document
     */
    private function addSheetBack()
    {
        $this->document->AddPage('L', '', false, false);

        $xOffset = 14;

        for ($boxNo = 0; $boxNo < 3; $boxNo++) {
            for ($level = 0; $level < 2; $level++) {
                // The next item should be 82 to the right
                $yOffset = $level * 82;

                $this->addBadgeBack($xOffset, $yOffset);
            }

            // The next row should be 92 lower
            $xOffset += 92;
        }
    }
}
