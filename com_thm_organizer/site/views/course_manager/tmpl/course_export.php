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
define('K_PATH_IMAGES', JPATH_ROOT . '/media/com_thm_organizer/images/');
jimport('tcpdf.tcpdf');

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/planning_periods.php';

/**
 * Base PDF export class used for the generation of various course exports.
 */
abstract class THM_OrganizerTemplateCourse_Export
{
    protected $course;

    protected $document;

    protected $filename;

    protected $lang;

    /**
     * THM_OrganizerTemplateCourse_List_PDF constructor.
     *
     * @param int $lessonID the lessonID of the exported course
     *
     * @throws Exception => invalid request / unauthorized access / not found
     */
    public function __construct($lessonID)
    {
        if (empty($lessonID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_400'), 400);
        }

        if (!THM_OrganizerHelperCourses::authorized($lessonID)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_401'), 401);
        }

        $course          = THM_OrganizerHelperCourses::getCourse($lessonID);
        if (empty($course)) {
            throw new Exception(JText::_('COM_THM_ORGANIZER_404'), 404);
        }

        $this->lang = THM_OrganizerHelperLanguage::getLanguage();
        $dates           = THM_OrganizerHelperCourses::getDates($lessonID);
        $maxParticipants = empty($course->lessonP) ? $course['subjectP'] : $course['lessonP'];
        $start           = explode('-', $dates[0]);
        $end             = explode('-', end($dates));
        $participants    = THM_OrganizerHelperCourses::getFullParticipantData($lessonID);

        $this->course = [
            'capacity'     => $maxParticipants,
            'end'          => $end[2] . '.' . $end[1] . '.' . $end[0],
            'fee'          => $course['fee'],
            'name'         => $course['name'],
            'participants' => $participants,
            'start'        => $start[2] . '.' . $start[1] . '.' . $start[0]
        ];

        if (!empty($course['campusID'])) {
            $this->course['place'] = THM_OrganizerHelperCampuses::getName($course['campusID']);
        } elseif (!empty($course['abstractCampusID'])) {
            $this->course['place'] = THM_OrganizerHelperCampuses::getName($course['abstractCampusID']);
        }
        // Before a placeholder is output, the third option is ignored.

        // Preparatory course 'semesters' are the semesters they are preparing for, not the actual semesters
        if (!empty($course['is_prep_course']) and !empty($course['planningPeriodID'])) {
            $nextPPID = THM_OrganizerHelperPlanning_Periods::getNextID($course['planningPeriodID']);

            $this->course['planningPeriodName'] = empty($nextPPID) ?
                $course['planningPeriodName'] : THM_OrganizerHelperPlanning_Periods::getName($nextPPID);
        } else {
            $this->course['planningPeriodName'] = $course['planningPeriodName'];
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
        $header   = $this->course['name'];
        $location = empty($this->course['place']) ? '' : "{$this->course['place']}, ";
        $dates    = "{$this->course['start']} - {$this->course['end']}";

        $participants     = $this->lang->_('COM_THM_ORGANIZER_PARTICIPANTS');
        $participantCount = count($this->course['participants']);
        $subHeader        = "$location$dates\n$participants: $participantCount";

        $this->document->SetHeaderData('thm_logo.png', '50', $header, $subHeader);
        $this->document->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->document->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $this->document->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->document->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->document->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->document->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->document->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        $this->document->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->document->SetFont('', '', 10);
    }

    /**
     * Sets the document title and the exported file name
     *
     * @param string $exportType the type of export being performed
     *
     * @return void sets object variables
     */
    protected function setNames($exportType)
    {
        $courseData   = [];
        $courseData[] = $this->course['name'];

        if (!empty($this->course['place'])) {
            $courseData[] = $this->course['place'];
        }

        $courseData[] = $this->course['start'];

        $this->document->SetTitle("$exportType - " . implode(' - ', $courseData));
        $this->filename = urldecode(implode('_', $courseData) . "_$exportType.pdf");
    }
}
