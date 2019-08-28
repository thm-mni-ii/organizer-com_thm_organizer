<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Layouts\XLS;

jimport('phpexcel.library.PHPExcel');

use Joomla\CMS\Factory;

/**
 * Class generates an XLS file for the schedule where lessons are listed sequentially.
 */
class ScheduleSequence
{
    private $spreadSheet;

    private $lessons;

    /**
     * THM_OrganizerTemplateExport_XLS constructor.
     *
     * @param array $parameters the parameters used for determining the export structure
     * @param array $lessons    the lessons for the given time frame and chosen resources
     */
    public function __construct($parameters, &$lessons)
    {
        $this->parameters = $parameters;
        $this->lessons    = $lessons;

        $spreadSheet = new \PHPExcel();

        $userName    = Factory::getUser()->name;
        $description = $this->getDescription();
        $spreadSheet->getProperties()->setCreator("THM Organizer")
            ->setLastModifiedBy($userName)
            ->setTitle($this->parameters['pageTitle'])
            ->setDescription($description);

        $this->spreadSheet = $spreadSheet;

        $this->setColumnDisplay();
        $this->addHeader();
        $this->addData();
    }

    /**
     * Iterates the dates / times and calls the function to add the event data
     * @return void
     */
    private function addData()
    {
        $row = 2;
        foreach ($this->lessons as $date => $timesIndexes) {
            if ($date === 'pastDate' or $date === 'futureDate') {
                continue;
            }

            foreach ($timesIndexes as $lessonInstances) {
                foreach ($lessonInstances as $lessonInstance) {
                    $this->addEvent($row, $date, $lessonInstance);
                    $row++;
                }
            }
        }
    }

    /**
     * Adds lesson instances to the spreadsheet
     *
     * @param int    $row            the row number for the event
     * @param string $date           the date on which the lesson occurs
     * @param array  $lessonInstance the lesson instance data
     *
     * @return void
     */
    private function addEvent($row, $date, $lessonInstance)
    {
        $this->spreadSheet->setActiveSheetIndex(0);

        $date = Dates::formatDate($date);
        $this->spreadSheet->getActiveSheet()->setCellValue("A$row", $date);

        $startTime = Dates::formatTime($lessonInstance['startTime']);
        $this->spreadSheet->getActiveSheet()->setCellValue("B$row", $startTime);

        $endTime = Dates::formatTime($lessonInstance['endTime']);
        $this->spreadSheet->getActiveSheet()->setCellValue("C$row", $endTime);

        $name = implode(' / ', array_keys($lessonInstance['subjects']));
        $name .= empty($lessonInstance['method']) ? '' : " - {$lessonInstance['method']}";
        $this->spreadSheet->getActiveSheet()->setCellValue("D$row", $name);

        $pools   = [];
        $rooms   = [];
        $persons = [];

        foreach ($lessonInstance['subjects'] as $subjectConfig) {
            foreach ($subjectConfig['pools'] as $poolID => $poolData) {
                $pools[$poolID] = $poolData['fullName'];
            }

            $rooms   = $rooms + $subjectConfig['rooms'];
            $persons = $persons + $subjectConfig['persons'];
        }

        $letter = 'D';
        if ($this->parameters['showPersons']) {
            $column      = ++$letter;
            $cell        = "$column$row";
            $personsText = implode(' / ', $persons);
            $this->spreadSheet->getActiveSheet()->setCellValue($cell, $personsText);
        }

        if ($this->parameters['showRooms']) {
            $column    = ++$letter;
            $cell      = "$column$row";
            $roomsText = implode(' / ', $rooms);
            $this->spreadSheet->getActiveSheet()->setCellValue($cell, $roomsText);
        }

        if ($this->parameters['showPools']) {
            $column    = ++$letter;
            $cell      = "$column$row";
            $poolsText = implode(' / ', $pools);
            $this->spreadSheet->getActiveSheet()->setCellValue($cell, $poolsText);
        }
    }

    /**
     * Adds column headers to the sheet
     *
     * @return void
     */
    private function addHeader()
    {
        $this->spreadSheet->setActiveSheetIndex(0);
        $this->spreadSheet->getActiveSheet()->setCellValue('A1', Languages::_('THM_ORGANIZER_DATE'));
        $this->spreadSheet->getActiveSheet()->setCellValue('B1', Languages::_('THM_ORGANIZER_START_TIME'));
        $this->spreadSheet->getActiveSheet()->setCellValue('C1', Languages::_('THM_ORGANIZER_END_TIME'));
        $this->spreadSheet->getActiveSheet()->setCellValue('D1', Languages::_('THM_ORGANIZER_SUBJECTS'));

        $letter = 'D';
        if ($this->parameters['showPersons']) {
            $column = ++$letter;
            $cell   = "{$column}1";
            $this->spreadSheet->getActiveSheet()->setCellValue($cell, Languages::_('THM_ORGANIZER_TEACHERS'));
        }

        if ($this->parameters['showRooms']) {
            $column = ++$letter;
            $cell   = "{$column}1";
            $this->spreadSheet->getActiveSheet()->setCellValue($cell, Languages::_('THM_ORGANIZER_ROOMS'));
        }

        if ($this->parameters['showPools']) {
            $column = ++$letter;
            $cell   = "{$column}1";
            $this->spreadSheet->getActiveSheet()->setCellValue($cell, Languages::_('THM_ORGANIZER_POOLS'));
        }

        foreach (range('A', $letter) as $columnID) {
            $this->spreadSheet->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    /**
     * Creates a description for the document
     *
     * @return string
     */
    private function getDescription()
    {
        $lessonDates = array_keys($this->lessons);
        $startDate   = Dates::formatDate(reset($lessonDates));
        $endDate     = Dates::formatDate(end($lessonDates));

        return Languages::_('THM_ORGANIZER_SCHEDULE') . " $startDate - $endDate " . $this->parameters['pageTitle'];
    }

    /**
     * Outputs the generated Excel file
     *
     * @return void
     */
    public function render()
    {
        $objWriter = PHPExcel_IOFactory::createWriter($this->spreadSheet, 'Excel2007');
        ob_end_clean();
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename={$this->parameters['docTitle']}.xlsx");
        $objWriter->save('php://output');
    }

    /**
     * Determines whether individual resource columns will be displayed
     * @return void
     */
    private function setColumnDisplay()
    {
        $this->parameters['showPools'] = (
            (empty($this->parameters['poolIDs']) or count($this->parameters['poolIDs']) !== 1)
            or !empty($this->parameters['roomIDs'])
            or !empty($this->parameters['personIDs'])
        );

        $this->parameters['showRooms'] = (
            (empty($this->parameters['roomIDs']) or count($this->parameters['roomIDs']) !== 1)
            or !empty($this->parameters['poolIDs'])
            or !empty($this->parameters['personIDs'])
        );

        $this->parameters['showPersons'] = (
            (empty($this->parameters['personIDs']) or count($this->parameters['personIDs']) !== 1)
            or !empty($this->parameters['poolIDs'])
            or !empty($this->parameters['roomIDs'])
        );
    }
}
