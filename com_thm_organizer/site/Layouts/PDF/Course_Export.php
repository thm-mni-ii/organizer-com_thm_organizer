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

namespace Organizer\Layouts\PDF;

define('K_PATH_IMAGES', JPATH_ROOT . '/components/com_thm_organizer/images/');

jimport('tcpdf.tcpdf');

use Joomla\CMS\Factory;
use Organizer\Helpers\Access;
use Organizer\Helpers\Terms;

/**
 * Base PDF export class used for the generation of various course exports.
 */
abstract class Course_Export
{
    protected $course;

    protected $document;

    protected $filename;

    /**
     * THM_OrganizerTemplateCourse_List_PDF constructor.
     *
     * @param int $courseID the lessonID of the exported course
     *
     * @throws Exception => invalid request / unauthorized access / not found
     */
    public function __construct($courseID)
    {
        if (empty($courseID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_400'), 400);
        }

        if (!Access::allowCourseAccess($courseID)) {
            throw new Exception(Languages::_('THM_ORGANIZER_401'), 401);
        }

        $course = Courses::getCourse($courseID);
        if (empty($course)) {
            throw new Exception(Languages::_('THM_ORGANIZER_404'), 404);
        }

        $dates           = Courses::getDates($courseID);
        $maxParticipants = empty($course->lessonP) ? $course['subjectP'] : $course['lessonP'];
        $start           = explode('-', $dates[0]);
        $end             = explode('-', end($dates));
        $participants    = Courses::getFullParticipantData($courseID);

        $this->course = [
            'capacity'     => $maxParticipants,
            'end'          => $end[2] . '.' . $end[1] . '.' . $end[0],
            'fee'          => $course['fee'],
            'name'         => $course['name'],
            'participants' => $participants,
            'start'        => $start[2] . '.' . $start[1] . '.' . $start[0]
        ];

        if (!empty($course['campusID'])) {
            $this->course['place'] = Campuses::getName($course['campusID']);
        } elseif (!empty($course['abstractCampusID'])) {
            $this->course['place'] = Campuses::getName($course['abstractCampusID']);
        }

        // Preparatory course 'semesters' are the semesters they are preparing for, not the actual semesters
        if (!empty($course['is_prep_course']) and !empty($course['termID'])) {
            $nextPPID = Terms::getNextID($course['termID']);

            $this->course['termName'] = empty($nextPPID) ?
                $course['termName'] : Terms::getName($nextPPID);
        } else {
            $this->course['termName'] = $course['termName'];
        }

        $this->document = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->document->SetAuthor(Factory::getUser()->name);
        $this->document->SetCreator(PDF_CREATOR);
    }

    /**
     * Create a new TCPDF document and format the header with course information
     *
     * @return void
     */
    protected function setHeader()
    {
        $header           = $this->course['name'];
        $location         = empty($this->course['place']) ? '' : "{$this->course['place']}, ";
        $dates            = "{$this->course['start']} - {$this->course['end']}";
        $participants     = Languages::_('THM_ORGANIZER_PARTICIPANTS');
        $participantCount = count($this->course['participants']);
        $subHeader        = "$location$dates\n$participants: $participantCount";

        $this->document->SetHeaderData('thm_logo.png', '50', $header, $subHeader);
        $this->document->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->document->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $this->document->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->document->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->document->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->document->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->document->SetAutoPageBreak(true, 0);
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
