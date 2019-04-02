<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Base class for use in schedule export PDF files.
 */
abstract class THM_OrganizerTemplateSchedule_Sequence_PDF
{
    const FULL = 1;
    const SHORT = 2;
    const ABBR = 3;
    protected $document;

    protected $grid;

    protected $lessons;

    protected $parameters;

    /**
     * THM_OrganizerTemplateSchedule_Export_PDF_A4 constructor.
     *
     * @param array  $parameters the parameters for document
     * @param array &$lessons    the lessons to be displayed
     * @param array  $grid       the lesson grid for use in display
     */
    public function __construct($parameters, &$lessons, $grid = null)
    {
        $this->document   = $this->getDocument();
        $this->lessons    = $lessons;
        $this->parameters = $parameters;
        $this->grid       = $grid;
    }

    /**
     * Filters the lesson indexes for those applicable to the row being iterated.
     *
     * @param array $lessonIndexes An array of indexes (startTime-endTime) for the given day.
     * @param array $rowHeader     An array containing information about the row being iterated.
     *
     * @return array The lesson indexes applicable for the row.
     */
    protected function filterIndexes($lessonIndexes, $rowHeader = null)
    {
        if (empty($rowHeader)) {
            return $lessonIndexes;
        }

        $rowStart = $rowHeader['startTime'];
        $rowEnd   = $rowHeader['endTime'];

        $filteredIndexes = [];
        foreach ($lessonIndexes as $index) {
            list($indexStart, $indexEnd) = explode('-', $index);

            $tooEarly = $indexEnd <= $rowStart;
            $tooLate  = $rowEnd <= $indexStart;

            if (!$tooEarly and !$tooLate) {
                $filteredIndexes[] = $index;
            }
        }

        return $filteredIndexes;
    }

    /**
     * Gets the row header information. startTime and endTime are used for later indexing purposes. Text is the text to
     * actually be displayed in the row header.
     *
     * @return array
     */
    protected function getColumnHeaders()
    {
        $dates = array_keys($this->lessons);

        $columns = [];

        foreach ($dates as $date) {
            $columns[$date]          = [];
            $columns[$date]['value'] = $date;
            $columns[$date]['text']  = THM_OrganizerHelperDate::formatDate($date, true, true);
        }

        return $columns;
    }

    /**
     * Creates the basic pdf object
     *
     * @return THM_Organizer_PDF_Schedule_Export
     */
    abstract protected function getDocument();

    /**
     * Creates the text to be output for the lesson instance
     *
     * @param array  $instance  the instance information
     * @param string $timeIndex the times used as indexes for lesson instances
     * @param array  $rowHeader the row header information
     *
     * @return string the html for the instance text
     */
    protected function getInstanceText($instance, $timeIndex, $rowHeader = null)
    {
        $text = '';

        list($startTime, $endTime) = explode('-', $timeIndex);

        // The 'grid' isn't one or the lesson is being displayed in a grid in which it wasn't planned.
        if (empty($rowHeader) or $startTime != $rowHeader['startTime'] or $endTime != $rowHeader['endTime']) {
            $formattedStart = THM_OrganizerHelperDate::formatTime($startTime);
            $formattedEnd   = THM_OrganizerHelperDate::formatTime($endTime);
            $text           .= "$formattedStart - $formattedEnd\n";
        }

        $subjectNames = [];
        $subjectNos   = [];
        $pools        = [];
        $teachers     = [];
        $rooms        = [];
        $method       = empty($instance['method']) ? '' : $instance['method'];
        $comment      = empty($instance['comment']) ? '' : $instance['comment'];

        foreach ($instance['subjects'] as $subject) {
            $name = $this->getName($subject);

            if (!in_array($name, $subjectNames)) {
                $subjectNames[] = $name;
            }

            if (!empty($subject['subjectNo']) and !in_array($subject['subjectNo'], $subjectNos)) {
                $subjectNos[] = $subject['subjectNo'];
            }

            // Only if no specific pool was requested individually
            if (empty($this->parameters['poolIDs']) or count($this->parameters['poolIDs']) > 1) {
                foreach ($subject['pools'] as $poolID => $pool) {
                    $pools[$poolID] = $pool['gpuntisID'];
                }
            }

            // Only if no specific teacher was requested individually
            if (empty($this->parameters['teacherIDs']) or count($this->parameters['teacherIDs']) > 1) {
                foreach ($subject['teachers'] as $teacherID => $teacherName) {
                    $teachers[$teacherID] = $teacherName;
                }
            }

            // Only if no specific room was requested individually
            if (empty($this->parameters['roomIDs']) or count($this->parameters['roomIDs']) > 1) {
                foreach ($subject['rooms'] as $roomID => $roomName) {
                    $rooms[$roomID] = $roomName;
                }
            }
        }

        $subjectName = implode('/', $subjectNames);
        $subjectName .= " - $method";

        if (!empty($subjectNos)) {
            $subjectName .= ' (' . implode('/', $subjectNos) . ')';
        }

        $text .= "$subjectName\n";

        $output = [];

        if (!empty($pools)) {
            $output[] = implode('/', $pools);
        }

        if (!empty($teachers)) {
            $output[] = implode('/', $teachers);
        }

        if (!empty($rooms)) {
            $output[] = implode('/', $rooms);
        }

        if (!empty($comment)) {
            $output[] = "$comment";
        }

        $text .= implode(' ', $output);

        return $text;
    }

    /**
     * Returns the name according to the export settings
     *
     * @param array $subject the subject data
     *
     * @return string the display name
     */
    protected function getName($subject)
    {
        switch ($this->parameters['titles']) {
            case self::ABBR:
                if (!empty($subject['abbr'])) {
                    return $subject['abbr'];
                } elseif (!empty($subject['shortName'])) {
                    return $subject['shortName'];
                }
                break;
            case self::SHORT:
                if (!empty($subject['shortName'])) {
                    return $subject['shortName'];
                }
                break;
        }

        return $subject['name'];
    }

    /**
     * Gets the text to be displayed in the row cells
     *
     * @param array $columnHeaders the column header information:
     *                             value => the date (Y-m-d), text => the text to display
     * @param array $rowHeader     the row header information:
     *                             start- and endTime used for indexing, text => the text to display
     *
     * @return array
     */
    abstract protected function getRowCells($columnHeaders, $rowHeader = null);

    /**
     * Gets the row header information. startTime and endTime are used for later indexing purposes. Text is the text to
     * actually be displayed in the row header.
     *
     * @return mixed
     */
    protected function getRowHeaders()
    {
        $rows = [];

        if (empty($this->grid)) {
            return $rows;
        }

        $rowIndex = 0;

        foreach ($this->grid as $times) {
            $rows[$rowIndex]              = [];
            $rows[$rowIndex]['startTime'] = $times['startTime'];
            $rows[$rowIndex]['endTime']   = $times['endTime'];
            $formattedStartTime           = THM_OrganizerHelperDate::formatTime($times['startTime']);
            $formattedEndTime             = THM_OrganizerHelperDate::formatTime($times['endTime']);
            $rows[$rowIndex]['text']      = $formattedStartTime . "\n-\n" . $formattedEndTime;
            $rowIndex++;
        }

        return $rows;
    }

    /**
     * Outputs the lessons organized according to a grid structure with times
     *
     * @param array  &$columnHeaders the dates
     * @param array  &$dimensions    the dimensions of the cells
     * @param string  $startDate     the date to start from
     * @param string  $breakDate     the date to stop iteration
     *
     * @return void
     */
    abstract protected function outputGrid(&$columnHeaders, $dimensions, $startDate, $breakDate);

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
    abstract protected function outputHeader($columnHeaders, $startDate, $breakDate, $outputTimeGrid);

    /**
     * Determines whether the given segment, typically a work week, should be output. IE are there lessons
     * in the given time frame.
     *
     * @param string $startDate the start date of the segment
     * @param string $endDate   the end date of the segment
     *
     * @return bool true if at least one segment day has lessons
     */
    protected function outputSegment($startDate, $endDate)
    {
        $currentDate = $startDate;

        while ($currentDate != $endDate) {
            if (!empty($this->lessons[$currentDate])) {
                return true;
            }

            $currentDate = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
        }

        return false;
    }

    /**
     * Outputs the schedule table to the document
     *
     * @return void Outputs lesson instance data to the document.
     */
    protected function outputTable()
    {
        $rowHeaders    = $this->getRowHeaders();
        $columnHeaders = $this->getColumnHeaders();
        $dimensions    = $this->document->getPageDimensions();
        $timeConstant  = $this->parameters['dateRestriction'] == 'day' ?
            '' : \JText::_('COM_THM_ORGANIZER_WEEK') . ': ';

        $outputTimeGrid = !empty($rowHeaders);
        $startDate      = key($columnHeaders);

        while (!empty($columnHeaders[$startDate])) {
            $startDateText = THM_OrganizerHelperDate::formatDate($startDate);
            $endDate       = date('Y-m-d', strtotime('+6 day', strtotime($startDate)));
            $endDateText   = THM_OrganizerHelperDate::formatDate($endDate);
            $breakDate     = date('Y-m-d', strtotime('+7 day', strtotime($startDate)));

            $showSegment = $this->outputSegment($startDate, $endDate);

            if ($showSegment) {
                $headerString = \JText::_($timeConstant) . "$startDateText - $endDateText";
                $this->document->SetHeaderData(
                    'thm.svg',
                    40,
                    $this->parameters['pageTitle'],
                    $headerString,
                    [57, 74, 89]
                );

                $this->outputHeader($columnHeaders, $startDate, $breakDate, $outputTimeGrid);

                if ($outputTimeGrid) {
                    $this->outputTimeGrid($rowHeaders, $columnHeaders, $dimensions, $startDate, $breakDate);
                } else {
                    $this->outputGrid($columnHeaders, $dimensions, $startDate, $breakDate);
                }
            }

            $startDate = $breakDate;
        }
    }

    /**
     * Outputs the lessons organized according to a grid structure with times
     *
     * @param array  &$rowHeaders    the row grid times
     * @param array  &$columnHeaders the dates
     * @param array  &$dimensions    the dimensions of the cells
     * @param string  $startDate     the date to start from
     * @param string  $breakDate     the date to stop iteration
     *
     * @return void
     */
    abstract protected function outputTimeGrid(&$rowHeaders, &$columnHeaders, $dimensions, $startDate, $breakDate);

    /**
     * Renders the document
     *
     * @return void
     */
    protected function render()
    {
        if (empty($this->lessons['pastDate']) and empty($this->lessons['futureDate'])) {
            $this->outputTable();
        } else {
            $this->document->AddPage();
            $this->document->cell('', '', \JText::_('COM_THM_ORGANIZER_NO_LESSONS'));
        }
        $this->document->Output($this->parameters['docTitle'] . '.pdf', 'I');
        ob_flush();
    }
}
