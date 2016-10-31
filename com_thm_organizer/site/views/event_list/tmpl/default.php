<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$showHeading = $this->model->params->get('show_page_heading', '');
$title       = $this->model->params->get('page_title', '');

echo '<div id="event-list" class="component-container">';
if (!empty($showHeading))
{
	echo '<h2 class="blacomponentheading">' . $title . '</h2>';
}
foreach ($this->model->events as $date => $times)
{
	echo '<div class="event-date">';
	echo '<div class="event-date-head">' . THM_OrganizerHelperComponent::formatDate($date) . '</div>';
	echo '<table><thead><tr class="list-head">';
	echo '<th class="time-column">' . JText::_('COM_THM_ORGANIZER_TIMES') . '</th>';
	echo '<th class="name-column">' . JText::_('COM_THM_ORGANIZER_EVENT') . '</th>';
	echo '<th class="teachers-column">' . JText::_('COM_THM_ORGANIZER_TEACHERS') . '</th>';
	echo '<th class="rooms-column">' . JText::_('COM_THM_ORGANIZER_ROOMS') . '</th>';
	echo '<th class="org-column">' . JText::_('COM_THM_ORGANIZER_ORGANIZATION') . '</th>';
	echo '</tr></thead>';

	$rowNumber = 0;
	foreach ($times as $time => $lessons)
	{
		foreach ($lessons as $lesson)
		{
			$rowClass = 'row' . ($rowNumber % 2);
			$rowNumber++;
			echo '<tr class="' . $rowClass . '">';
			echo '<td class="time-column">';
			echo THM_OrganizerHelperComponent::formatTime($lesson['startTime']) . ' - ';
			echo THM_OrganizerHelperComponent::formatTime($lesson['endTime']);
			echo '</td>';
			echo '<td class="name-column">';
			echo implode(' / ', $lesson['titles']);
			if (!empty($lesson['method']))
			{
				echo ' - ' . $lesson['method'];
			}
			if (!empty($lesson['comment']))
			{
				echo '<br />(' . $lesson['comment'] . ')';
			}
			echo '</td>';
			echo '<td class="teachers-column">' . implode(' / ', $lesson['teachers']) . '</td>';
			echo '<td class="rooms-column">' . implode(', ', $lesson['rooms']) . '</td>';
			echo '<td class="org-column">' . implode(', ', $lesson['departments']) . '</td>';
			echo '</tr>';
		}
	}
	echo '</table></div>';
}
echo '</div>';