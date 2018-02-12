<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

require_once 'pdf_schedule_sequence.php';

/**
 * Class generates a PDF file in A4 format.
 */
class THM_OrganizerTemplateSchedule_Export_PDF extends THM_OrganizerTemplateSchedule_Sequence_PDF
{
    /**
     * THM_OrganizerTemplateSchedule_Export_PDF_A4 constructor.
     *
     * @param array $parameters the parameters for document
     * @param array &$lessons   the lessons to be displayed
     * @param array $grid       the lesson grid for use in display
     */
    public function __construct($parameters, &$lessons, $grid = null)
    {
        parent::__construct($parameters, $lessons, $grid);

        $this->parameters['cellLineHeight'] = 4.4;

        if ($this->parameters['dateRestriction'] == 'day') {
            $this->parameters['dataWidth'] = empty($this->grid) ? 200 : 188;
        } else {
            $this->parameters['dataWidth'] = empty($this->grid) ? 46.5 : 45;
        }

        $this->parameters['padding']   = 1;
        $this->parameters['timeWidth'] = 11;

        $this->render();
    }

    /**
     * Creates the basic pdf object
     *
     * @return THM_OrganizerTCPDFScheduleA4
     */
    protected function getDocument()
    {
        $orientation = $this->parameters['dateRestriction'] == 'day' ? 'p' : 'l';
        $document    = new THM_OrganizerTCPDFScheduleA4($orientation);
        $document->SetCreator('THM Organizer');
        $document->SetAuthor(JFactory::getUser()->name);
        $document->SetTitle($this->parameters['pageTitle']);
        $document->SetMargins(5, 25, 5);
        $document->SetAutoPageBreak(true, 5);
        $document->setHeaderMargin(5);
        $document->setCellPaddings('', 1, '', 1);
        $document->SetTextColor(57, 74, 89);
        $document->setHeaderTemplateAutoreset(true);

        return $document;
    }

    /**
     * Gets the text to be displayed in the row cells
     *
     * @param array $columnHeaders the column header information: value => the date (Y-m-d), text => the text to display
     * @param array $rowHeader     the row header information: start- and endTime used for indexing, text => the text to display
     *
     * @return array
     */
    protected function getRowCells($columnHeaders, $rowHeader = null)
    {
        $rowCells = [];

        foreach ($columnHeaders as $columnHeader) {
            $date         = $columnHeader['value'];
            $indexLessons = $this->lessons[$date];
            $allIndexes   = array_keys($indexLessons);
            $timeIndexes  = $this->filterIndexes($allIndexes, $rowHeader);
            $indexCount   = 0;

            // No lesson instances on the given day
            if (empty($timeIndexes)) {
                continue;
            }

            foreach ($timeIndexes as $timeIndex) {
                foreach ($this->lessons[$date][$timeIndex] as $instance) {
                    if (empty($rowCells[$indexCount])) {
                        $rowCells[$indexCount] = [];
                    }

                    $rowCells[$indexCount][$date] = $this->getInstanceText($instance, $timeIndex, $rowHeader);
                    $indexCount++;
                }
            }
        }

        // Skip line counting if empty
        if (empty($rowCells)) {
            return $rowCells;
        }

        $totalLineCount = 0;

        foreach ($rowCells as $index => $instances) {
            $counts = [];
            foreach ($instances as $instance) {
                $counts[] = $this->document->getNumLines($instance, $this->parameters['dataWidth']);
            }
            $lineCount = max($counts);

            $rowCells[$index]['lineCount'] = $lineCount;
            $totalLineCount                += $lineCount;
        }

        $rowCells['lineCount'] = $totalLineCount;

        return $rowCells;
    }

    /**
     * Outputs the lessons organized according to a grid structure with times
     *
     * @param array  &$columnHeaders the object with the column headers
     * @param array  $dimensions     the dimensions
     * @param string $startDate      the start date for the interval
     * @param string $breakDate      the end date for the interval
     *
     * @return void
     */
    protected function outputGrid(&$columnHeaders, $dimensions, $startDate, $breakDate)
    {
        $rowCells  = $this->getRowCells($columnHeaders);
        $originalY = $this->document->getY();

        if (empty($rowCells)) {
            return;
        } else {
            $totalRowHeight     = $rowCells['lineCount'] * $this->parameters['cellLineHeight'];
            $totalPaddingHeight = 2 * $this->parameters['padding'];
        }

        // The row size would cause it to traverse the page break
        if (($originalY + $totalRowHeight + $totalPaddingHeight + $dimensions['bm']) > ($dimensions['hk'])) {
            $this->document->Ln();
            $this->outputHeader($columnHeaders, $startDate, $breakDate, true);
        }

        $this->document->SetFont('helvetica', '', 8, '', 'default', true);


        $rowHeight = 0;
        foreach ($rowCells as $rowName => $row) {
            if ($rowName === 'lineCount') {
                continue;
            }

            $this->document->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => [57, 74, 89]]);

            $originalY = $this->document->getY();
            $newY      = $originalY + $rowHeight + $this->parameters['padding'];
            $this->document->setY($newY);

            $cellHeight = $row['lineCount'] * $this->parameters['cellLineHeight'];

            for ($currentDate = $startDate; $currentDate != $breakDate; $currentDate = date('Y-m-d',
                strtotime("+1 day", strtotime($currentDate)))) {
                $dow        = date('w', strtotime($currentDate));
                $validIndex = (!empty($columnHeaders[$currentDate]) and $dow >= (int)$this->parameters['startDay'] and $dow <= (int)$this->parameters['endDay']);
                if ($validIndex) {
                    // Small horizontal spacer
                    $this->document->MultiCell(1, $cellHeight, '', 0, 0, 0, 0);

                    if (empty($row[$columnHeaders[$currentDate]['value']])) {
                        $dataText = '';
                        $border   = 0;
                    } else {
                        $dataText = $row[$columnHeaders[$currentDate]['value']];
                        $border   = 'LRBT';
                    }

                    // Lesson instance cell
                    $this->document->MultiCell(
                        $this->parameters['dataWidth'],
                        $cellHeight,
                        $dataText,
                        $border, 'C', 0, 0, '', '', true, 0, false, true,
                        $cellHeight, 'M'
                    );
                }
            }

            $this->document->Ln();
        }

        $this->outputRowEnd();
    }

    /**
     * Outputs the column headers to the document
     *
     * @param array  $columnHeaders  The date information to be output to the document.
     * @param string $startDate      the first column date/index to use
     * @param string $breakDate      the last column date/index to iterate
     * @param bool   $outputTimeGrid whether or not the time column should be written
     *
     * @return void  outputs to the document
     */
    protected function outputHeader($columnHeaders, $startDate, $breakDate, $outputTimeGrid)
    {
        $this->document->AddPage();

        $this->document->SetFont('helvetica', '', 10, '', 'default', true);
        $this->document->SetLineStyle(['width' => 0.5, 'dash' => 0, 'color' => [74, 92, 102]]);

        if ($outputTimeGrid) {
            $this->document->MultiCell($this->parameters['timeWidth'], 0, JText::_('COM_THM_ORGANIZER_TIME'), 'TB', 'C',
                0, 0);
        }

        for ($currentDate = $startDate; $currentDate != $breakDate; $currentDate = date('Y-m-d',
            strtotime("+1 day", strtotime($currentDate)))) {
            $dow        = date('w', strtotime($currentDate));
            $validIndex = (!empty($columnHeaders[$currentDate]) and $dow >= (int)$this->parameters['startDay'] and $dow <= (int)$this->parameters['endDay']);
            if ($validIndex) {
                $this->document->MultiCell($this->parameters['dataWidth'] + 1, 0, $columnHeaders[$currentDate]['text'],
                    'TB', 'C', 0, 0);
            }
        }

        $this->document->Ln();
        $this->document->SetFont('helvetica', '', 8, '', 'default', true);
    }

    /**
     * Creates a line to cap the end of a row
     *
     * @return void outputs to the document
     */
    private function outputRowEnd()
    {
        $originalY = $this->document->getY();
        $this->document->setY($originalY - 4);

        $this->document->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => [119, 133, 140]]);
        $this->document->cell(0, 0, '', 'B', 1, 0, 0);
    }

    /**
     * Writes the time cell to the document
     *
     * @param int    $height the estimated cell height
     * @param string $text   the time text
     *
     * @return void
     */
    private function outputTimeCell($height, $text)
    {
        $this->document->MultiCell(
            $this->parameters['timeWidth'],
            $height,
            $text,
            0, 'C', 0, 0, '', '', true, 0, false, true,
            $height, 'M'
        );
    }

    /**
     * Outputs the lessons organized according to a grid structure with times
     *
     * @param array  &$rowHeaders    the row grid times
     * @param array  &$columnHeaders the dates
     * @param array  &$dimensions    the dimensions of the cells
     * @param string $startDate      the date to start from
     * @param string $breakDate      the date to stop iteration
     *
     * @return void
     */
    protected function outputTimeGrid(&$rowHeaders, &$columnHeaders, $dimensions, $startDate, $breakDate)
    {
        foreach ($rowHeaders as $rowHeader) {
            $headerLineCount = $this->document->getNumLines($rowHeader['text'], $this->parameters['dataWidth']);
            $rowCells        = $this->getRowCells($columnHeaders, $rowHeader);
            $originalY       = $this->document->getY();

            if (empty($rowCells)) {
                $totalRowHeight = $headerLineCount * $this->parameters['cellLineHeight'];

                // This should actually be less one because of the line count index, but the footer adds it back.
                $totalPaddingHeight = count($rowCells) * $this->parameters['padding'];
            } else {
                $minLineCount       = max($headerLineCount, $rowCells['lineCount']);
                $totalRowHeight     = $minLineCount * $this->parameters['cellLineHeight'];
                $totalPaddingHeight = 2 * $this->parameters['padding'];
            }

            // The row size would cause it to traverse the page break
            if (($originalY + $totalRowHeight + $totalPaddingHeight + $dimensions['bm']) > ($dimensions['hk'])) {
                $this->document->Ln();
                $this->outputHeader($columnHeaders, $startDate, $breakDate, true);

                // New page, new Y
                $originalY = $this->document->getY();
            }

            $this->document->SetFont('helvetica', '', 8, '', 'default', true);

            if (empty($rowCells)) {
                $newY = $originalY + $this->parameters['padding'];
                $this->document->setY($newY);

                $height = $headerLineCount * $this->parameters['cellLineHeight'];
                $text   = $rowHeader['text'];
                $this->outputTimeCell($height, $text);

                // One long cell for the border
                $this->document->MultiCell(0, $height, '', 0, 0, 0, 0, '', '', true);

                $this->document->Ln();

                $this->outputRowEnd();

                continue;
            }

            $rowHeight  = 0;
            $outputTime = true;
            foreach ($rowCells as $rowName => $row) {
                if ($rowName === 'lineCount') {
                    continue;
                }

                $this->document->SetLineStyle(['width' => 0.1, 'dash' => 0, 'color' => [57, 74, 89]]);

                $originalY = $this->document->getY();
                $newY      = $originalY + $rowHeight + $this->parameters['padding'];
                $this->document->setY($newY);

                $lineCount  = $outputTime ? max($headerLineCount, $row['lineCount']) : $row['lineCount'];
                $cellHeight = $lineCount * $this->parameters['cellLineHeight'];

                $timeText = $outputTime ? $rowHeader['text'] : '';
                $this->outputTimeCell($cellHeight, $timeText);
                $outputTime = false;

                for ($currentDate = $startDate; $currentDate != $breakDate; $currentDate = date('Y-m-d',
                    strtotime("+1 day", strtotime($currentDate)))) {
                    $dow        = date('w', strtotime($currentDate));
                    $validIndex = (!empty($columnHeaders[$currentDate]) and $dow >= (int)$this->parameters['startDay'] and $dow <= (int)$this->parameters['endDay']);
                    if ($validIndex) {
                        // Small horizontal spacer
                        $this->document->MultiCell(1, $cellHeight, '', 0, 0, 0, 0);

                        if (empty($row[$columnHeaders[$currentDate]['value']])) {
                            $dataText = '';
                            $border   = 0;
                        } else {
                            $dataText = $row[$columnHeaders[$currentDate]['value']];
                            $border   = 'LRBT';
                        }

                        // Lesson instance cell
                        $this->document->MultiCell(
                            $this->parameters['dataWidth'],
                            $cellHeight,
                            $dataText,
                            $border, 'C', 0, 0, '', '', true, 0, false, true,
                            $cellHeight, 'M'
                        );
                    }
                }

                $this->document->Ln();
            }

            $this->outputRowEnd();
        }
    }
}

/**
 * Class extends TCPDF for ease of instanciation and customized header/footer.
 */
class THM_OrganizerTCPDFScheduleA4 extends TCPDF
{

    /**
     * Constructs using the implementation of the parent class restricted to the relevant parameters.
     *
     * @param string $orientation the page orientation 'p' => portrait, 'l' => landscape
     */
    public function __construct($orientation = 'l')
    {
        parent::__construct($orientation, 'mm', 'A4');
    }

    //Page header
    public function Header()
    {
        if ($this->header_xobjid === false) {
            // start a new XObject Template
            $this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);

            $this->y = $this->header_margin;
            $this->x = $this->original_lMargin;

            $this->Image(K_PATH_IMAGES . 'thm_logo.png', 5, 5, 46, 15);

            $headerFont  = $this->getHeaderFont();
            $headerData  = $this->getHeaderData();
            $cell_height = $this->getCellHeight($headerFont[2] / $this->k);

            // set starting margin for text data cell
            $header_x = 70;
            $cw       = $this->w - $this->original_lMargin - $this->original_rMargin - 200;
            $this->SetTextColorArray($this->header_text_color);

            // Plan title
            $this->SetFont($headerFont[0], 'B', $headerFont[2] + 1);
            $this->SetX($header_x);
            $this->Cell($cw, $cell_height, $headerData['title'], 0, 1, '', 0, '', 0);

            // Plan format and date
            $this->SetFont($headerFont[0], $headerFont[1], $headerFont[2]);
            $this->SetX($header_x);
            $this->MultiCell($cw, $cell_height, $headerData['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0,
                'T', false);

            $this->endTemplate();
        }

        // print header template
        $this->printTemplate($this->header_xobjid, 0, 0, 0, 0, '', '', false);

        // reset header xobject template at each page
        if ($this->header_xobj_autoreset) {
            $this->header_xobjid = false;
        }
    }

    // Page footer
    public function Footer()
    {
        // Position at 15 mm from bottom
        $this->SetY(-10);
        // Set font
        $this->SetFont('helvetica', 'I', 7);
        // Page number
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0,
            false, 'T', 'M');
    }
}