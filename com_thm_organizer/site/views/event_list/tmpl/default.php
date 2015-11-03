<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        template for display of scheduled lessons on registered monitors
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$showHeading = $this->model->params->get('show_page_heading', '');
$showOrg = $this->model->params->get('show_org', true);
$showRooms = $this->model->params->get('show_rooms', true);
$showNames = $this->model->params->get('show_names', true);
$showComment = $this->model->params->get('show_comment', true);
$title = $this->model->params->get('page_title', '');
?>
<div id="event-list" class="component-container">
<?php if (!empty($showHeading)): ?>
<h2 class="blacomponentheading"><?php echo $title; ?></h2>
<?php endif; ?>
<?php foreach ($this->model->events as $date => $events): ?>
    <div class="event-date">
        <div class="event-date-head"><?php echo THM_OrganizerHelperComponent::formatDate($date); ?></div>
        <table>
            <thead>
                <tr class="list-head">
                    <th class="time-column"><?php echo JText::_('COM_THM_ORGANIZER_START_TIME'); ?></th>
                    <th class="time-column"><?php echo JText::_('COM_THM_ORGANIZER_END_TIME'); ?></th>
                    <?php if ($showRooms): ?>
                    <th class="rooms-column"><?php echo JText::_('COM_THM_ORGANIZER_ROOMS'); ?></th>
                    <?php endif; ?>
                    <?php if ($showOrg): ?>
                    <th class="org-column"><?php echo JText::_('COM_THM_ORGANIZER_ORGANIZATION'); ?></th>
                    <?php endif; ?>
                    <th class="speakers-column"><?php echo JText::_('COM_THM_ORGANIZER_TEACHERS'); ?></th>
                    <?php if ($showNames): ?>
                    <th class="name-column"><?php echo JText::_('COM_THM_ORGANIZER_SUBJECTS'); ?></th>
                    <?php endif; ?>
                    <?php if ($showComment): ?>
                    <th class="comment-column"><?php echo JText::_('COM_THM_ORGANIZER_COMMENT'); ?></th>
                    <?php endif; ?>
                </tr>
            </thead>
            <?php
            $rowNumber = 0;
            foreach ($events as $event):
            foreach ($event['blocks'] as $block):
            $rowClass = 'row' . ($rowNumber % 2);
            $rowNumber++;
            $rooms = implode(', ', $block['rooms']);
            $speakersArray = array();
            foreach ($block['speakers'] as $speaker)
            {
                $speakersArray[] = implode(', ', array_filter($speaker));
            }
            $speakers = implode(' / ', $speakersArray); ?>
            <tr class="<?php echo $rowClass; ?>">
                <td class="time-column">
                    <?php echo THM_OrganizerHelperComponent::formatTime($block['starttime']); ?>
                </td>
                <td class="time-column">
                    <?php echo THM_OrganizerHelperComponent::formatTime($block['endtime']); ?>
                </td>
                <?php if ($showRooms): ?>
                <td class="rooms-column"><?php echo $rooms; ?></td>
                <?php endif; ?>
                <?php if ($showOrg): ?>
                <td class="org-column"><?php echo $event['organization']; ?></td>
                <?php endif; ?>
                <td class="speakers-column"><?php echo $speakers; ?></td>
                <?php if ($showNames): ?>
                <td class="name-column"><?php echo $event['name']; ?></td>
                <?php endif; ?>
                <?php if ($showComment): ?>
                <td class="comment-column"><?php echo $event['comment']; ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </table>
    </div>
<?php endforeach; ?>
</div>