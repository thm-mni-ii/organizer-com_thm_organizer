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
 * Class generates a PDF file in A3 format.
 */
class THM_OrganizerTemplateSchedule_Export_PDF extends THM_OrganizerTemplateSchedule_Sequence_PDF
{
    private $resources;

    /**
     * Aggregates the disparate requested resources to a single array for ease of later iteration.
     *
     * @return void
     */
    private function aggregateRequestedResources()
    {
        $resources = [];

        if (!empty($this->parameters['poolIDs'])) {
            foreach ($this->parameters['poolIDs'] as $poolID) {
                $resources[] = ['id' => $poolID, 'index' => 'pools'];
            }
        }

        if (!empty($this->parameters['teacherIDs'])) {
            foreach ($this->parameters['teacherIDs'] as $teacherID) {
                $resources[] = ['id' => $teacherID, 'index' => 'teachers'];
            }
        }

        if (!empty($this->parameters['roomIDs'])) {
            foreach ($this->parameters['roomIDs'] as $roomID) {
                $resources[] = ['id' => $roomID, 'index' => 'rooms'];
            }
        }

        $this->resources = $resources;
    }

    /**
     * THM_OrganizerTemplateSchedule_Export_PDF_A3 constructor.
     *
     * @param array $parameters the parameters for document
     * @param array &$lessons   the lessons to be displayed
     * @param array $grid       the lesson grid for use in display
     */
    public function __construct($parameters, &$lessons, $grid = null)
    {
        parent::__construct($parameters, $lessons, $grid);

        $resourceCount = $this->countRequestedResources();

        if ($resourceCount > 1) {
            $this->aggregateRequestedResources();

            if ($this->parameters['dateRestriction'] == 'day') {
                $this->parameters['dataWidth'] = empty($this->grid) ? 175 : 164;
            } else {
                $this->parameters['dataWidth'] = empty($this->grid) ? 63.5 : 62;
            }

            $this->parameters['cellLineHeight'] = 3.6;
        } else {

            if ($this->parameters['dateRestriction'] == 'day') {
                $this->parameters['dataWidth'] = empty($this->grid) ? 200 : 188;
            } else {
                $this->parameters['dataWidth'] = empty($this->grid) ? 67.5 : 66;
            }

            $this->parameters['cellLineHeight'] = 3.8;
        }

        $this->parameters['resourceCount'] = $resourceCount;
        $this->parameters['padding']       = 2;
        $this->parameters['timeWidth']     = 11;
        $this->parameters['resourceWidth'] = 24;

        $this->render();
    }

    /**
     * Retrieves an array of normal border styles
     *
     * @return array an array of border styles
     */
    private function getCellBorder()
    {
        return [
            'R' => ['width' => '.1', 'color' => [223, 229, 230]],
            'B' => ['width' => '.1', 'color' => [223, 229, 230]]
        ];
    }

    /**
     * Creates the basic pdf object
     *
     * @return THM_OrganizerTCPDFScheduleA3
     */
    protected function getDocument()
    {
        $orientation = $this->parameters['dateRestriction'] == 'day' ? 'p' : 'l';
        $document    = new THM_OrganizerTCPDFScheduleA3($orientation);
        $document->SetCreator('THM Organizer');
        $document->SetAuthor(JFactory::getUser()->name);
        $document->SetTitle($this->parameters['pageTitle']);
        $document->SetMargins(5, 25, 5);
        $document->setHeaderMargin(5);

        // This is here to access the bottom margin.
        $document->setPageOrientation($orientation, false, '15');
        $document->setCellPaddings('', .5, '', .5);
        $document->SetTextColor(0, 0, 0);
        $document->setHeaderTemplateAutoreset(true);

        return $document;
    }

    /**
     * Retrieves an array of border styles for the last element in a column grouping
     *
     * @return array an array of border styles
     */
    private function getLastCellBorder()
    {
        return [
            'R' => ['width' => '.1', 'color' => [223, 229, 230]],
            'B' => ['width' => '.5', 'color' => [74, 92, 102]]
        ];
    }

    /**
     * Retrieves an array of border styles for the last row header in a column grouping
     *
     * @return array an array of border styles
     */
    private function getLastRowHeadCellBorder()
    {
        return [
            'R' => ['width' => '.5', 'color' => [74, 92, 102]],
            'B' => ['width' => '.5', 'color' => [74, 92, 102]]
        ];
    }

    /**
     * Retrieves the resource name and the instance text.
     *
     * @param array $instance the instance information
     * @param array $resource the resource information id & type
     *
     * @return array instance texts
     */
    private function getResourceInstanceText($instance, $resource)
    {
        $return        = [];
        $resourceID    = $resource['id'];
        $resourceIndex = $resource['index'];

        $subjectNames = [];
        $subjectNos   = [];
        $pools        = [];
        $teachers     = [];
        $rooms        = [];
        $method       = empty($instance['method']) ? '' : $instance['method'];
        $comment      = empty($instance['comment']) ? '' : $instance['comment'];

        foreach ($instance['subjects'] as $subjectName => $subject) {
            // The iterated resource is not associated with this instance
            if (empty($subject[$resourceIndex][$resourceID])) {
                continue;
            }

            $name = $this->getName($subject);

            if (!in_array($name, $subjectNames)) {
                $subjectNames[] = $name;
            }

            if (!empty($subject['subjectNo']) and !in_array($subject['subjectNo'], $subjectNos)) {
                $subjectNos[] = $subject['subjectNo'];
            }

            if ($resourceIndex == 'pools') {
                $return['resourceName'] = $subject[$resourceIndex][$resourceID]['gpuntisID'];
            } else {
                foreach ($subject['pools'] as $poolID => $pool) {
                    $pools[$poolID] = $pool['gpuntisID'];
                }
            }

            if ($resourceIndex == 'teachers') {
                $return['resourceName'] = $subject[$resourceIndex][$resourceID];
            } else {
                foreach ($subject['teachers'] as $teacherID => $teacherName) {
                    $teachers[$teacherID] = $teacherName;
                }
            }

            if ($resourceIndex == 'rooms') {
                $return['resourceName'] = $subject[$resourceIndex][$resourceID];
            } else {
                foreach ($subject['rooms'] as $roomID => $roomName) {
                    $rooms[$roomID] = $roomName;
                }
            }
        }

        // None of the instances were relevant to the resource.
        if (empty($subjectNames)) {
            return $return;
        }

        $subjectName = implode(' / ', $subjectNames);
        $subjectName .= " - $method";

        if (!empty($subjectNos)) {
            $subjectName .= ' (' . implode(' / ', $subjectNos) . ')';
        }

        $text = "$subjectName\n";

        $output = [];

        if (!empty($pools)) {
            $output[] = implode(' / ', $pools);
        }

        if (!empty($teachers)) {
            $output[] = implode(' / ', $teachers);
        }

        if (!empty($rooms)) {
            $output[] = implode(' / ', $rooms);
        }

        if (!empty($comment)) {
            $output[] = "$comment";
        }

        $text .= implode(' ', $output);

        $return['instanceText'] = $text;

        return $return;

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
                    if ($this->parameters['resourceCount'] === 1) {
                        if (empty($rowCells[$indexCount])) {
                            $rowCells[$indexCount] = [];
                        }

                        $rowCells[$indexCount][$date] = $this->getInstanceText($instance, $timeIndex, $rowHeader);
                        $indexCount++;
                    } else {
                        foreach ($this->resources as $resource) {
                            $rowNumber = 0;
                            $results   = $this->getResourceInstanceText($instance, $resource);

                            if (empty($results)) {
                                continue;
                            }

                            $resourceName = $results['resourceName'];

                            if (empty($rowCells[$resourceName])) {
                                $rowCells[$resourceName] = [];
                            } else {
                                $rowFound = false;
                                foreach ($rowCells[$resourceName] as $tempNumber => $dates) {
                                    if (!key_exists($date, $dates)) {
                                        $rowNumber = $tempNumber;
                                        $rowFound  = true;
                                        break;
                                    }
                                }

                                if (!$rowFound) {
                                    $rowNumber = count($rowCells[$resourceName]);
                                }
                            }

                            if (empty($rowCells[$resourceName][$rowNumber])) {
                                $rowCells[$resourceName][$rowNumber] = [];
                            }

                            $rowCells[$resourceName][$rowNumber][$date] = $results['instanceText'];
                        }
                    }
                }
            }
        }

        ksort($rowCells);

        // Skip line counting if empty
        if (empty($rowCells)) {
            return $rowCells;
        }

        $totalLineCount = 0;

        if ($this->parameters['resourceCount'] < 2) {
            foreach ($rowCells as $index => $dates) {
                $lineCount                     = max(
                    array_map(
                        function ($instance) {
                            return $this->document->getNumLines($instance, $this->parameters['dataWidth']);
                        },
                        $dates
                    )
                );
                $rowCells[$index]['lineCount'] = $lineCount;
                $totalLineCount                += $lineCount;
            }
        } else {
            $rowCount = 0;
            foreach ($rowCells as $resourceName => $rows) {
                foreach ($rows as $index => $dates) {
                    $lineCount                                    = max(
                        array_map(
                            function ($instance) {
                                return $this->document->getNumLines($instance, $this->parameters['dataWidth']);
                            },
                            $dates
                        )
                    );
                    $rowCells[$resourceName][$index]['lineCount'] = $lineCount;
                    $totalLineCount                               += $lineCount;
                    $rowCount++;
                }
            }

            $rowCells['rowCount'] = $rowCount;
        }

        $rowCells['lineCount'] = $totalLineCount;

        return $rowCells;
    }

    /**
     * Retrieves an array of normal border styles
     *
     * @return array an array of border styles
     */
    private function getRowHeadCellBorder()
    {
        return [
            'R' => ['width' => '.5', 'color' => [74, 92, 102]]
        ];
    }

    /**
     * Counts the number of requested resources
     *
     * @return int the number of requested resources
     */
    private function countRequestedResources()
    {
        // No grouping => the count plays no role
        if (empty($this->parameters['grouping'])) {
            return 1;
        }

        $countPools    = empty($this->parameters['poolIDs']) ? 0 : count($this->parameters['poolIDs']);
        $countTeachers = empty($this->parameters['teacherIDs']) ? 0 : count($this->parameters['teacherIDs']);
        $countRooms    = empty($this->parameters['roomIDs']) ? 0 : count($this->parameters['roomIDs']);

        return $countPools + $countTeachers + $countRooms;
    }

    /**
     * Outputs an empty row for the given time
     *
     * @param int    $lineCount     the number of lines calculated for the time text
     * @param string $timeText      the text to be output for the row time
     * @param array  $columnHeaders the column headers / indexes
     * @param string $startDate     the first column date/index to use
     * @param string $breakDate     the last column date/index to iterate
     *
     * @return void
     */
    private function outputEmptyRow($lineCount, $timeText, $columnHeaders, $startDate, $breakDate)
    {
        $height = $lineCount * $this->parameters['cellLineHeight'];
        $border = $this->getLastRowHeadCellBorder();
        $this->outputTimeCell($height, $timeText, $border);

        if ($this->parameters['resourceCount'] > 1) {
            $this->document->MultiCell(
                $this->parameters['resourceWidth'],
                $height,
                '',
                'RB',
                'C',
                0,
                0,
                '',
                '',
                true,
                0,
                false,
                true
            );
        }

        for ($currentDate = $startDate; $currentDate != $breakDate;) {
            $dow        = date('w', strtotime($currentDate));
            $validIndex = (!empty($columnHeaders[$currentDate])
                and $dow >= (int)$this->parameters['startDay']
                and $dow <= (int)$this->parameters['endDay']
            );
            if ($validIndex) {
                $this->document->MultiCell(
                    $this->parameters['dataWidth'],
                    $height,
                    '',
                    'RB',
                    'C',
                    0,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true
                );
            }

            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }

        $this->document->Ln();
    }

    /**
     * Outputs the lessons organized according to a grid structure with times
     *
     * @param array  &$columnHeaders the dates
     * @param array  &$dimensions    the dimensions of the cells
     * @param string $startDate      the date to start from
     * @param string $breakDate      the date to stop iteration
     *
     * @return void
     */
    protected function outputGrid(&$columnHeaders, $dimensions, $startDate, $breakDate)
    {
        $rowCells       = $this->getRowCells($columnHeaders);
        $originalY      = $this->document->getY();
        $totalRowHeight = $rowCells['lineCount'] * $this->parameters['cellLineHeight'];

        // The row size would cause it to traverse the page break
        if (($originalY + $totalRowHeight + $dimensions['bm']) > ($dimensions['hk'])) {
            $this->document->Ln();
            $this->outputHeader($columnHeaders, $startDate, $breakDate, false);
        }

        if ($this->parameters['resourceCount'] < 2) {
            $this->outputTimeRows($rowCells, 3, $columnHeaders, $startDate, $breakDate);
        } else {
            $this->outputResourceRows($rowCells, 3, $columnHeaders, $startDate, $breakDate);
        }
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
        $this->document->SetLineStyle([
            'width' => 0.5,
            'cap'   => 'butt',
            'join'  => 'miter',
            'dash'  => 0,
            'color' => [74, 92, 102]
        ]);

        if ($outputTimeGrid) {
            $this->document->MultiCell(
                $this->parameters['timeWidth'],
                0,
                JText::_('COM_THM_ORGANIZER_TIME'),
                'TB',
                'C',
                0,
                0
            );
        }

        if ($this->parameters['resourceCount'] > 1) {
            $this->document->MultiCell(
                $this->parameters['resourceWidth'],
                0,
                JText::_('COM_THM_ORGANIZER_RESOURCE'),
                'TB',
                'C',
                0,
                0
            );
        }

        for ($currentDate = $startDate; $currentDate != $breakDate;) {
            $dow        = date('w', strtotime($currentDate));
            $validIndex = (!empty($columnHeaders[$currentDate]) and $dow >= (int)$this->parameters['startDay'] and $dow <= (int)$this->parameters['endDay']);

            if ($validIndex) {
                $this->document->MultiCell(
                    $this->parameters['dataWidth'],
                    0,
                    $columnHeaders[$currentDate]['text'],
                    'TB',
                    'C',
                    0,
                    0
                );
            }

            $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
        }

        $this->document->Ln();

        // Reset font after header
        $this->document->SetFont('helvetica', '', 6, '', 'default', true);
    }

    /**
     * Outputs the rows associated with a particular time index. (Not further compartmentalized according to resources.)
     *
     * @param array  $resourceCells the data for the rows associated with the time index
     * @param int    $minLineCount  the minimum amount of lines a row may have => number used in time column of row header.
     * @param array  $columnHeaders the column headers / indexes
     * @param string $startDate     the first column date/index to use
     * @param string $breakDate     the last column date/index to iterate
     * @param array  $rowHeader     the row header / index information being iterated
     *
     * @return void
     */
    private function outputResourceRows(
        &$resourceCells,
        $minLineCount,
        &$columnHeaders,
        $startDate,
        $breakDate,
        $rowHeader = null
    ) {
        // Less one because of the line count index
        $lastRowNumber = $resourceCells['rowCount'];
        $rowNumber     = 1;
        $outputTime    = true;
        foreach ($resourceCells as $resourceName => $resourceRows) {
            if ($resourceName === 'lineCount' or $resourceName === 'rowCount') {
                continue;
            }

            $lastResourceRowNumber = count($resourceRows);
            $resourceRowNumber     = 1;
            $outputResource        = true;

            foreach ($resourceRows as $row) {
                $lineCount  = max($minLineCount, $row['lineCount']);
                $cellHeight = $lineCount * $this->parameters['cellLineHeight'];

                if (!empty($rowHeader)) {
                    $timeText   = $outputTime ? $rowHeader['text'] : '';
                    $timeBorder = $rowNumber == $lastRowNumber ? $this->getLastRowHeadCellBorder() : $this->getRowHeadCellBorder();
                    $this->outputTimeCell($cellHeight, $timeText, $timeBorder);
                    $outputTime = false;
                    $rowNumber++;
                }

                $resourceText = $outputResource ? $resourceName : '';
                if ($resourceRowNumber == $lastResourceRowNumber) {

                    $resourceBorder = $this->getLastRowHeadCellBorder();
                    $dataBorder     = $this->getLastCellBorder();
                } else {
                    $resourceBorder = $this->getRowHeadCellBorder();
                    $dataBorder     = $this->getCellBorder();
                }

                $this->document->MultiCell(
                    $this->parameters['resourceWidth'],
                    $cellHeight,
                    $resourceText,
                    $resourceBorder,
                    'C',
                    0,
                    0,
                    '',
                    '',
                    true,
                    0,
                    false,
                    true,
                    $cellHeight,
                    'M'
                );
                $outputResource = false;
                $resourceRowNumber++;

                for ($currentDate = $startDate; $currentDate != $breakDate;) {
                    $dow        = date('w', strtotime($currentDate));
                    $validIndex = (!empty($columnHeaders[$currentDate])
                        and $dow >= (int)$this->parameters['startDay']
                        and $dow <= (int)$this->parameters['endDay']
                    );

                    if ($validIndex) {
                        $dataText = empty($row[$columnHeaders[$currentDate]['value']]) ?
                            '' : $row[$columnHeaders[$currentDate]['value']];
                        // Lesson instance cell
                        $this->document->MultiCell(
                            $this->parameters['dataWidth'],
                            $cellHeight,
                            $dataText,
                            $dataBorder,
                            'C',
                            0,
                            0,
                            '',
                            '',
                            true,
                            0,
                            false,
                            true,
                            $cellHeight,
                            'M'
                        );
                    }

                    $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
                }

                $this->document->Ln();
            }
        }
    }

    /**
     * Writes the time cell to the document
     *
     * @param int    $height the estimated cell height
     * @param string $text   the time text
     * @param string $border the sides of the cell to add a border to
     *
     * @return void
     */
    private function outputTimeCell($height, $text, $border = 'R')
    {
        $this->document->MultiCell(
            $this->parameters['timeWidth'],
            $height,
            $text,
            $border,
            'C',
            0,
            0,
            '',
            '',
            true,
            0,
            false,
            true,
            $height,
            'M'
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
            } else {
                $minLineCount   = max($headerLineCount, $rowCells['lineCount']);
                $totalRowHeight = $minLineCount * $this->parameters['cellLineHeight'];
            }

            // The row size would cause it to traverse the page break
            if (($originalY + $totalRowHeight + $dimensions['bm']) > ($dimensions['hk'])) {
                $this->document->Ln();
                $this->outputHeader($columnHeaders, $startDate, $breakDate, true);
            }

            if (empty($rowCells)) {
                $this->outputEmptyRow($headerLineCount, $rowHeader['text'], $columnHeaders, $startDate, $breakDate);
            } elseif ($this->parameters['resourceCount'] < 2) {
                $this->outputTimeRows($rowCells, $headerLineCount, $columnHeaders, $startDate, $breakDate, $rowHeader);
            } else {
                $this->outputResourceRows(
                    $rowCells,
                    $headerLineCount,
                    $columnHeaders,
                    $startDate,
                    $breakDate,
                    $rowHeader
                );
            }
        }
    }

    /**
     * Outputs the rows associated with a particular time index. (Not further compartmentalized according to resources.)
     *
     * @param array  $rowCells      the data for the rows associated with the time index
     * @param int    $minLineCount  the minimum amount of lines a row may have => number used in time column of row header.
     * @param array  $columnHeaders the column headers / indexes
     * @param string $startDate     the first column date/index to use
     * @param string $breakDate     the last column date/index to iterate
     * @param array  $rowHeader     the row header / index information being iterated
     *
     * @return void
     */
    private function outputTimeRows(
        &$rowCells,
        $minLineCount,
        &$columnHeaders,
        $startDate,
        $breakDate,
        $rowHeader = null
    ) {
        // Less one because of the line count index
        $lastRowNumber = count($rowCells) - 1;
        $rowNumber     = 1;
        $outputTime    = true;
        foreach ($rowCells as $rowName => $row) {
            if ($rowName === 'lineCount') {
                continue;
            }

            $lineCount  = max($minLineCount, $row['lineCount']);
            $cellHeight = $lineCount * $this->parameters['cellLineHeight'];

            if (!empty($rowHeader)) {
                $timeText = $outputTime ? $rowHeader['text'] : '';
                $border   = $rowNumber == $lastRowNumber ? $this->getLastRowHeadCellBorder() : $this->getRowHeadCellBorder();
                $this->outputTimeCell($cellHeight, $timeText, $border);
                $outputTime = false;
                $rowNumber++;
            }

            for ($currentDate = $startDate; $currentDate != $breakDate;) {
                $dow        = date('w', strtotime($currentDate));
                $validIndex = (!empty($columnHeaders[$currentDate]) and $dow >= (int)$this->parameters['startDay'] and $dow <= (int)$this->parameters['endDay']);

                if ($validIndex) {
                    $dataText = empty($row[$columnHeaders[$currentDate]['value']]) ? '' : $row[$columnHeaders[$currentDate]['value']];
                    // Lesson instance cell
                    $this->document->MultiCell(
                        $this->parameters['dataWidth'],
                        $cellHeight,
                        $dataText,
                        'RB',
                        'C',
                        0,
                        0,
                        '',
                        '',
                        true,
                        0,
                        false,
                        true,
                        $cellHeight,
                        'M'
                    );
                }

                $currentDate = date('Y-m-d', strtotime("+1 day", strtotime($currentDate)));
            }

            $this->document->Ln();
        }
    }
}

/**
 * Class extends TCPDF for ease of instantiation and customized header/footer.
 */
class THM_OrganizerTCPDFScheduleA3 extends TCPDF
{

    /**
     * Constructs using the implementation of the parent class restricted to the relevant parameters.
     *
     * @param string $orientation the page orientation 'p' => portrait, 'l' => landscape
     */
    public function __construct($orientation = 'l')
    {
        parent::__construct($orientation, 'mm', 'A3');
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
            $this->MultiCell(
                $cw,
                $cell_height,
                $headerData['string'],
                0,
                '',
                0,
                1,
                '',
                '',
                true,
                0,
                false,
                true,
                0,
                'T',
                false
            );

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
        $pagination = 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages();
        $this->Cell(0, 10, $pagination, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}