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

// Course Status
$current = $this->lang->_('COM_THM_ORGANIZER_CURRENT');
$expired = $this->lang->_('COM_THM_ORGANIZER_EXPIRED');

$pathPrefix = "index.php?option=com_thm_organizer";
$subjectURL = "{$pathPrefix}&view=subject_details&languageTag={$this->shortTag}";
$subjectURL .= empty($menuID) ? '' : "&Itemid=$menuID";

foreach ($this->items as $item) {
    $subjectRoute = JRoute::_($subjectURL . "&id={$item->subjectID}");

    $startDate   = THM_OrganizerHelperComponent::formatDate($item->start);
    $endDate     = THM_OrganizerHelperComponent::formatDate($item->end);
    $displayDate = $startDate == $endDate ? $endDate : "$startDate - $endDate";

    $courseStatus = $item->expired ? '<span class="disabled">' . $expired . '</span>' : $current;
    $name         = empty($item->campus['name']) ? $item->name : "$item->name ({$item->campus['name']})";

    ?>
	<tr class='row'>
		<td>
			<a href='<?php echo $subjectRoute; ?>'>
                <?php echo $name; ?>

			</a>
		</td>
		<td><?php echo $displayDate; ?></td>
		<td class="course-state"><?php echo $courseStatus ?></td>
		<td class="user-state">
            <?php echo THM_OrganizerHelperCourse::getStatusDisplay($item->lessonID, $item->admin, $item->expired); ?>
		</td>
		<td class="registration">
            <?php echo THM_OrganizerHelperCourse::getActionButton('participant', $item->lessonID, $item->admin,
                $item->expired); ?>
		</td>
	</tr>
    <?php
}
