<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/prep_course.php';

abstract class THM_OrganizerTemplatePC_Export
{
	protected $courseData;

	protected $document;

	protected $lang;

	/**
	 * THM_OrganizerTemplateCourse_List_PDF constructor.
	 *
	 * @param int $lessonID the lessonID of the exported course
	 */
	public function __construct($lessonID)
	{
		$this->lang = THM_OrganizerHelperLanguage::getLanguage();

		$course         = THM_OrganizerHelperPrep_Course::getCourse($lessonID);
		$dates          = THM_OrganizerHelperPrep_Course::getDates($lessonID);
		$max_part		= empty($course->lessonP) ? $course["subjectP"] : $course["lessonP"];
		$start          = explode("-", $dates[0]["schedule_date"]);
		$finish         = explode("-", end($dates)["schedule_date"]);
		$participants   = THM_OrganizerHelperPrep_Course::getFullParticipantData($lessonID);

		$this->courseData = array(
			"name"          => $course["name"],
			"place"         => "Gießen",
			"start"         => $start,
			"finish"        => $finish,
			"c_start"       => $start[2] . "." . $start[1] . "." . $start[0],
			"c_end"         => $finish[2] . "." . $finish[1] . "." . $finish[0],
			"capacity"      => $max_part,
			"planPeriod"    => $course["planningPeriodName"],
			"participants"  => $participants
		);

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
		$timestamp      = time();
		$date           = date(JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y'), $timestamp);
		$time           = date(JComponentHelper::getParams('com_thm_organizer')->get('timeFormat', 'H.i'), $timestamp);

		$this->document->SetHeaderData(
			"thm_logo.png", '50',
			$this->courseData["name"] . " in " .
			$this->courseData["place"] . ", " .
			$this->courseData["c_start"] . " - " .
			$this->courseData["c_end"],
			$date . " " .
			$time . "\n" . $this->lang->_("COM_THM_ORGANIZER_PARTICIPANTS") . ": " .
			count($this->courseData["participants"])
		);

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