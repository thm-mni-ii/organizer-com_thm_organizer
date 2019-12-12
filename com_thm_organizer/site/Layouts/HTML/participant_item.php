<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Router\Route;

// Course Status
$current = Languages::_('THM_ORGANIZER_CURRENT');
$expired = Languages::_('THM_ORGANIZER_EXPIRED');

$pathPrefix = 'index.php?option=com_thm_organizer';
$subjectURL = "{$pathPrefix}&view=subject_item&languageTag={$this->tag}";
$subjectURL .= empty($menuID) ? '' : "&Itemid=$menuID";

foreach ($this->items as $item)
{
	$subjectRoute = Route::_($subjectURL . "&id={$item->subjectID}");

	$startDate   = Dates::formatDate($item->start);
	$endDate     = Dates::formatDate($item->end);
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
			<?php echo Courses::getStatusDisplay($item->lessonID); ?>
        </td>
        <td class="registration">
			<?php echo Courses::getActionButton('participant', $item->lessonID); ?>
        </td>
    </tr>
	<?php
}
