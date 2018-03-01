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
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/courses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Base PDF export class used for the generation of various course exports.
 */
abstract class THM_OrganizerTemplatePC_Export
{
    protected $courseData;

    protected $document;

    protected $lang;

    /**
     * THM_OrganizerTemplateCourse_List_PDF constructor.
     *
     * @param int $lessonID the lessonID of the exported course
     *
     * @throws Exception
     */
    public function __construct($lessonID)
    {
        $this->lang = THM_OrganizerHelperLanguage::getLanguage();

        $course       = THM_OrganizerHelperCourses::getCourse($lessonID);
        $dates        = THM_OrganizerHelperCourses::getDates($lessonID);
        $max_part     = empty($course->lessonP) ? $course["subjectP"] : $course["lessonP"];
        $start        = explode("-", $dates[0]["schedule_date"]);
        $finish       = explode("-", end($dates)["schedule_date"]);
        $participants = THM_OrganizerHelperCourses::getFullParticipantData($lessonID);

        $this->courseData = [
            "name"         => $course["name"],
            "start"        => $start,
            "finish"       => $finish,
            "c_start"      => $start[2] . "." . $start[1] . "." . $start[0],
            "c_end"        => $finish[2] . "." . $finish[1] . "." . $finish[0],
            "capacity"     => $max_part,
            "participants" => $participants
        ];

        if (!empty($course['campusID'])) {
            $this->courseData['place'] = THM_OrganizerHelperCampuses::getName($course['campusID']);
        } elseif (!empty($course['abstractCampusID'])) {
            $this->courseData['place'] = THM_OrganizerHelperCampuses::getName($course['abstractCampusID']);
        }

        if (!empty($course['planningPeriodID'])) {
            $nextPPID = THM_OrganizerHelperPlanning_Periods::getNextID($course['planningPeriodID']);

            $this->courseData['planningPeriodName'] = empty($nextPPID) ?
                $course['planningPeriodName'] : THM_OrganizerHelperPlanning_Periods::getName($nextPPID);
        } else {
            $this->courseData['planningPeriodName'] = $course['planningPeriodName'];
        }

        $this->document = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->document->SetAuthor(JFactory::getUser()->name);
        $this->document->SetCreator(PDF_CREATOR);
    }

    /**
     * Create a new TCPDF and format the header with necessary information about course
     *
     * @return void
     */
    protected function setHeader()
    {
        $header = $this->courseData["name"];
        $header .= empty($this->courseData["place"]) ? '' : " in {$this->courseData["place"]}";
        $header .= ", {$this->courseData["c_start"]} - {$this->courseData["c_end"]}";

        $timestamp = time();

        $date = date(JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y'), $timestamp);
        $time = date(JComponentHelper::getParams('com_thm_organizer')->get('timeFormat', 'H.i'), $timestamp);

        $participants     = $this->lang->_("COM_THM_ORGANIZER_PARTICIPANTS");
        $participantCount = count($this->courseData["participants"]);
        $subHeader        = "$date $time\n$participants: $participantCount";

        $this->document->SetHeaderData('thm_logo.png', '50', $header, $subHeader);
        $this->document->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->document->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $this->document->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->document->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->document->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->document->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->document->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $this->document->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->document->SetFont('', '', 10);
    }
}