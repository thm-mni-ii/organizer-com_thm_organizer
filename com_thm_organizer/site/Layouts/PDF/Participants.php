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

/**
 * Class generates a list of participants based on the registered participants.
 */
class Participants extends CourseExport
{
    private $columnHeaders;

    private $widths;

    /**
     * THM_OrganizerTemplatePrep_Course_Participant_List_Export_PDF constructor.
     *
     * @param int $lessonID the lessonID of the exported course
     *
     * @throws Exception => invalid request / unauthorized access / not found
     */
    public function __construct($lessonID)
    {
        parent::__construct($lessonID);

        $exportType = Languages::_('THM_ORGANIZER_PARTICIPANTS');
        $this->setNames($exportType);

        $this->setHeader();

        $feeApplies = !empty($this->course['fee']);

        $this->columnHeaders = [
            'index'      => '#',
            'name'       => 'Name',
            'department' => Languages::_('THM_ORGANIZER_DEPARTMENT'),
            'program'    => Languages::_('THM_ORGANIZER_PROGRAM'),
            'room'       => Languages::_('THM_ORGANIZER_ROOM')
        ];

        $this->widths = [
            'index'      => 8,
            'name'       => 79,
            'department' => 28,
            'program'    => 40,
            'room'       => 25
        ];

        if ($feeApplies) {
            $this->columnHeaders['paid'] = Languages::_('THM_ORGANIZER_PAID');
            $this->widths['name']        = 59;
            $this->widths['paid']        = 20;
        }

        $this->createParticipantTable();

        $this->document->Output($this->filename, 'I');

        ob_flush();
    }

    /**
     * Add Table with participant information and additional places for students to TCPDF
     *
     * @return void
     */
    private function createParticipantTable()
    {
        $this->addPage();

        $itemNo       = 1;
        $participants = $this->course['participants'];

        foreach ($participants as $participant) {
            // Get the starting coordinates for later use with borders
            $startX = $this->document->GetX();
            $startY = $this->document->GetY();

            $maxLength = 0;

            foreach (array_keys($this->columnHeaders) as $columnName) {
                switch ($columnName) {
                    case 'index':
                        $value = $itemNo;
                        break;
                    case 'name':
                        $value = $participant['userName'];
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

                $length = $this->document->MultiCell($this->widths[$columnName], 5, $value, '', 'L', 0, 0);
                if ($length > $maxLength) {
                    $maxLength = $length;
                }
            }

            // Reset for borders
            $this->document->SetXY($startX, $startY);

            foreach ($this->widths as $index => $width) {
                if ($index == 'index') {
                    $this->document->MultiCell($width, $maxLength * 5, '', 'LRB', 'L', 0, 0);
                } else {
                    $this->document->MultiCell($width, $maxLength * 5, '', 'RB', 'L', 0, 0);
                }
            }

            $this->document->Ln();

            if ($this->document->getY() > 260) {
                $this->addPage();
            }

            $itemNo++;
        }

        // Create empty cells for 25% more participants and round to a multiple of 6 due to the passports nature
        $bufferSize = ceil(count($participants) * 1.25) + 1;
        for ($itemNo; $itemNo < $bufferSize; $itemNo++) {
            foreach (array_keys($this->columnHeaders) as $columnName) {
                $value = $columnName == 'index' ? $itemNo : '';
                $this->document->MultiCell($this->widths[$columnName], 5, $value, 'LRB', 'L', 0, 0);
            }

            $this->document->Ln();

            if ($this->document->getY() > 260) {
                $this->document->Cell(array_sum($this->widths), 0, '', 'T');
                $this->addPage();
            }
        }
    }

    /**
     * Adds a new page to the document and creates the column headers for the table
     *
     * @return void
     */
    private function addPage()
    {
        $this->document->AddPage();

        // create the column headers for the page
        $this->document->SetFillColor(210);
        $this->document->SetFont('', 'B');
        foreach (array_keys($this->columnHeaders) as $columnName) {
            $this->document->Cell($this->widths[$columnName], 7, $this->columnHeaders[$columnName], 1, 0, 'L', 1);
        }
        $this->document->Ln();

        // reset styles
        $this->document->SetFillColor(235, 252, 238);
        $this->document->SetFont('');
    }
}
