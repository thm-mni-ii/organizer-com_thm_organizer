<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerViewSchedule_Export
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';
/** @noinspection PhpIncludeInspection */
jimport('phpexcel.library.PHPExcel');

/**
 * Class provides methods to create xls documents based upon schedule data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerViewSchedule_Export extends JViewLegacy
{
	private $parameters;

	private $lessons;

	private $spreadsheet;

	/**
	 * Method to get extra
	 *
	 * @param string $tpl template
	 *
	 * @return  mixed  false on error, otherwise void
	 */
	public function display($tpl = null)
	{
		$model      = $this->getModel();
		$parameters = $model->parameters;

		$fileName = $parameters['documentFormat'] . '_' . $parameters['xlsWeekFormat'];
		require_once __DIR__ . "/tmpl/$fileName.php";
		$export = new THM_OrganizerTemplateExport_XLS($parameters, $model->lessons);
		$export->render();
		ob_flush();
	}

	/**
	 * Method to add an event to the calendar
	 *
	 * @param string $date           the lesson instance date
	 * @param array  $lessonInstance the lesson instance
	 *
	 * @return void sets object variables
	 */
	private function setEvent($date, $lessonInstance)
	{
		$vEvent = new vevent;
		$vEvent->setProperty("TRANSP", "OPAQUE");
		$vEvent->setProperty("SEQUENCE", "0");
		$vEvent->setProperty("PRIORITY", "5");

		$datePieces      = explode("-", $date);
		$startTimePieces = explode(":", $lessonInstance['startTime']);
		$endTimePieces   = explode(":", $lessonInstance['endTime']);

		$dtStart = array(
			"year"  => $datePieces[0],
			"month" => $datePieces[1],
			"day"   => $datePieces[2],
			"hour"  => $startTimePieces[0],
			"min"   => $startTimePieces[1],
			"sec"   => $startTimePieces[2]
		);
		$vEvent->setProperty("DTSTART", $dtStart);

		$dtEnd = array(
			"year"  => $datePieces[0],
			"month" => $datePieces[1],
			"day"   => $datePieces[2],
			"hour"  => $endTimePieces[0],
			"min"   => $endTimePieces[1],
			"sec"   => $endTimePieces[2]
		);
		$vEvent->setProperty("DTEND", $dtEnd);

		$subjectNames = array_keys($lessonInstance['subjects']);
		$subjectNos   = array();
		$teachers     = array();
		$rooms        = array();
		foreach ($lessonInstance['subjects'] AS $subjectConfiguration)
		{
			if (!empty($subjectConfiguration['subjectNo']))
			{
				$subjectNos[$subjectConfiguration['subjectNo']] = $subjectConfiguration['subjectNo'];
			}

			$teachers = $teachers + $subjectConfiguration['teachers'];
			$rooms    = $rooms + $subjectConfiguration['rooms'];
		}

		$comment = empty($lessonInstance['comment']) ? '' : $lessonInstance['comment'];
		$vEvent->setProperty("DESCRIPTION", $comment);

		$title = implode('/', $subjectNames);
		$title .= empty($lessonInstance['method']) ? '' : " - {$lessonInstance['method']}";
		$title .= empty($subjectNos) ? '' : " (" . implode('/', $subjectNos) . ")";

		$teachersText = implode('/', $teachers);
		$roomsText    = implode('/', $rooms);

		$summary = JText::sprintf('COM_THM_ORGANIZER_ICS_SUMMARY', $title, $teachersText);

		$vEvent->setProperty("ORGANIZER", $teachersText);
		$vEvent->setProperty("LOCATION", $roomsText);
		$vEvent->setProperty("SUMMARY", $summary);
		$this->calendar->setComponent($vEvent);
	}
}
