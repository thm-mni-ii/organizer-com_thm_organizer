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
$regState   = THM_OrganizerHelperPrep_Course::getRegistrationState($this->item->lessonID);
$courseAuth = THM_OrganizerHelperPrep_Course::authSubjectTeacher($this->item->subjectID);
$regOpen    = THM_OrganizerHelperPrep_Course::isRegistrationOpen($this->item->lessonID);

$pathPrefix = "index.php?option=com_thm_organizer";

$subjectRoute  = JRoute::_("{$pathPrefix}&view=subject_details&id={$this->item->subjectID}&languageTag={$this->shortTag}");
$registerRoute = JRoute::_("{$pathPrefix}&task=participant.register&lessonID={$this->item->lessonID}&languageTag={$this->shortTag}");
$optionsRoute  = JRoute::_("{$pathPrefix}&view=course_manager&lessonID={$this->item->lessonID}&languageTag={$this->shortTag}");

$dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');

if (!empty($regState))
{
	if ($regState["status"] == 1)
	{
		$statusMessage = "success";
		$statusText    = $this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_STATE_REGISTERED");
	}
	else
	{
		$statusMessage = "warning";
		$statusText    = $this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_STATE_WAIT_LIST");
	}
}
else
{
	$statusMessage = "error";
	$statusText    = $this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_STATE_NOT_REGISTERED");
}

$status = sprintf("<div class='alert alert-%s'>%s</div>", $statusMessage, $statusText);

$registrationOpen = THM_OrganizerHelperPrep_Course::isRegistrationOpen($this->item->lessonID);

if (empty($this->item->expired) OR $courseAuth OR $this->authorized)
{
	echo "<tr class='row'>"
		. "<td> <a href='$subjectRoute'> {$this->item->name} </a> </td>"
		. "<td>" . JHtml::_('date', $this->item->start, $dateFormat) . "</td> "
		. "<td>" . JHtml::_('date', $this->item->end, $dateFormat) . "</td> ";

	if (!empty(JFactory::getUser()->id))
	{
		$isDisabled = !$registrationOpen ? "disabled" : "";
		$btnText    = $this->lang->_(!empty($regState) ? "JLOGOUT" : "JLOGIN");
		echo "<td> $status </td> "
			. "<td><a href='$registerRoute' class='btn btn-mini $isDisabled' type='button'>$btnText</a></td>";

		if ($this->authorized OR $courseAuth)
		{
			echo "<td><a href='$optionsRoute' class='btn' type='button'>{$this->lang->_("COM_THM_ORGANIZER_MANAGE")}</a></td>";
		}
		elseif ($this->oneAuth)
		{
			echo "<td></td>";
		}
	}

	echo "</tr>";
}
