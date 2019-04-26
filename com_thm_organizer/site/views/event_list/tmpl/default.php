<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use THM_OrganizerHelperHTML as HTML;

$showHeading = $this->model->params->get('show_page_heading', '');
$title       = $this->model->params->get('page_title', '');

echo '<div id="event-list" class="component-container">';
if (!empty($showHeading)) {
    echo '<h2 class="componentheading">' . $title . '</h2>';
}

?>
    <form action="" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="languageTag" id="languageTag" value=""/>
        <div id="form-container" class="form-container">
            <div class="clear"></div>
            <div class='control-group'>
                <div class='control-label'>
                    <?php echo HTML::getLabel($this, 'startDate'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('startDate')->input; ?>
                </div>
            </div>
            <div class='control-group'>
                <div class='control-label'>
                    <?php echo HTML::getLabel($this, 'dateRestriction'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('dateRestriction')->input; ?>
                </div>
            </div>
            <div class="control-group">
                <button class="btn submit-button" onclick="showPostLoader();form.submit();">
                    <?php echo Languages::_('THM_ORGANIZER_ACTION_REFRESH'); ?>
                    <span class="icon-loop"></span>
                </button>
            </div>
            <div class="clear"></div>
        </div>
    </form>
<?php

if (empty($this->model->events)) {
    echo '<h3 class="no-entries-found"> ' . Languages::_('THM_ORGANIZER_NO_ENTRIES_FOUND') . '</h3>';
}

foreach ($this->model->events as $date => $times) {
    echo '<div class="event-date">';
    echo '<div class="event-date-head">' . THM_OrganizerHelperDate::formatDate($date) . '</div>';
    echo '<table><thead><tr class="list-head">';
    echo '<th class="time-column">' . Languages::_('THM_ORGANIZER_TIMES') . '</th>';
    echo '<th class="name-column">' . Languages::_('THM_ORGANIZER_EVENT') . '</th>';
    echo '<th class="teachers-column">' . Languages::_('THM_ORGANIZER_TEACHERS') . '</th>';
    echo '<th class="rooms-column">' . Languages::_('THM_ORGANIZER_ROOMS') . '</th>';
    echo '<th class="org-column">' . Languages::_('THM_ORGANIZER_ORGANIZATION') . '</th>';
    echo '</tr></thead>';

    $rowNumber = 0;
    foreach ($times as $time => $lessons) {
        foreach ($lessons as $lesson) {
            $rowClass = 'row' . ($rowNumber % 2);
            $rowNumber++;
            echo '<tr class="' . $rowClass . '">';
            echo '<td class="time-column">';
            echo THM_OrganizerHelperDate::formatTime($lesson['startTime']) . ' - ';
            echo THM_OrganizerHelperDate::formatTime($lesson['endTime']);
            echo '</td>';
            echo '<td class="name-column">';
            echo implode(' / ', $lesson['titles']);
            if (!empty($lesson['method'])) {
                echo ' - ' . $lesson['method'];
            }
            if (!empty($lesson['comment'])) {
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