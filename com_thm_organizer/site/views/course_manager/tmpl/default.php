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

$shortTag = THM_OrganizerHelperLanguage::getShortTag();
$baseURL  = "index.php?option=com_thm_organizer&lessonID={$this->course['id']}&languageTag=$shortTag";

$exportURLBase    = "$baseURL&view=course_list&format=pdf&type=";
$participantListRoute = JRoute::_($exportURLBase . 0, false);
$departmentListRoute  = JRoute::_($exportURLBase . 1, false);
$badgesRoute          = JRoute::_($exportURLBase . 2, false);

$capacityText = THM_OrganizerHelperLanguage::sprintf("COM_THM_ORGANIZER_CURRENT_CAPACITY", sizeof($this->curCap), $this->capacity);
$editAuth     = THM_OrganizerHelperComponent::allowResourceManage('subject', $this->course["subjectID"]);

$subjectEditURL = "$baseURL&view=course_edit&id={$this->course["subjectID"]}";

$registeredText = $this->lang->_('COM_THM_ORGANIZER_COURSE_REGISTERED');
$waitListText   = $this->lang->_('COM_THM_ORGANIZER_WAIT_LIST');
$dateFormat     = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y') . " ";
$dateFormat     .= JComponentHelper::getParams('com_thm_organizer')->get('timeFormat', 'H.i');

if (!empty($this->menu))
{
	$menuText = $this->lang->_('COM_THM_ORGANIZER_BACK');
}

?>
<div class="toolbar">
	<div class="tool-wrapper language-switches">
		<?php foreach ($this->languageSwitches AS $switch)
		{
			echo $switch;
		}
		?>
	</div>
</div>
<div class="course-manager-view">
	<h1><?php echo "{$this->lang->_('COM_THM_ORGANIZER_COURSE_MANAGEMENT')}: {$this->course["name"]}"; ?></h1>
	<div class="course-descriptors">
		<div class="left"><?php echo $this->dateText ?></div>
		<div class="right"><?php echo $capacityText ?></div>
		<div class="clear"></div>
	</div>

	<form action="index.php?" method="post" id="adminForm" name="adminForm">
		<input type="hidden" name="option" value="com_thm_organizer"/>
		<input type="hidden" name="task" value="participant.changeStatus"/>
		<input type="hidden" name="participantStatus" value=""/>
		<input type="hidden" name="lessonID" value="<?php echo $this->course["id"]; ?>"/>
		<input type="hidden" name="subjectID" value="<?php echo $this->course["subjectID"]; ?>"/>

		<div class="group left status-container">
			<select title="<?php echo $this->lang->_('COM_THM_ORGANIZER_PARTICIPANT_OPTIONS'); ?>"
					id="participantState" name="participantState" required>
				<option value=""><?php echo $this->lang->_('COM_THM_ORGANIZER_PARTICIPANT_OPTIONS'); ?></option>
				<option value="1"><?php echo $this->lang->_('COM_THM_ORGANIZER_ACCEPT') ?></option>
				<option value="0"><?php echo $this->lang->_('COM_THM_ORGANIZER_ACTION_WAIT_LIST'); ?></option>
				<?php if ($this->isAdmin): ?>
					<option value='2'><?php echo $this->lang->_('COM_THM_ORGANIZER_ACTION_DELETE'); ?></option>
				<?php endif; ?>
			</select>
			<button title="<?php echo $this->lang->_('JSUBMIT'); ?>" type="submit" class="btn">
				<span class="icon-forward-2"></span>
			</button>
		</div>

		<div class="group right course-toolbar">
			<?php if ($editAuth): ?>
				<a href="<?php echo JRoute::_($subjectEditURL, false); ?>" class="btn btn-mini" type="button">
					<span class="icon-edit"></span>
					<?php echo $this->lang->_("COM_THM_ORGANIZER_EDIT_COURSE") ?>
				</a>
			<?php endif; ?>

			<a href="#" class="btn btn-mini callback-modal" type="button" data-toggle="modal" data-target="#modal">
				<span class="icon-mail"></span> <?php echo $this->lang->_("COM_THM_ORGANIZER_CIRCULAR") ?>
			</a>

			<div class="print-container">
				<a class="dropdown-toggle print btn" data-toggle="dropdown" href="#">
					<span class="icon-print"></span>
					<?php echo $this->lang->_('COM_THM_ORGANIZER_PRINT_OPTIONS'); ?>
					<span class="icon-arrow-down-3"></span>
				</a>
				<ul id="print" class="dropdown-menu">
					<li>
						<a href="<?php echo $participantListRoute; ?>" target="_blank">
							<span class="icon-file-pdf"></span><?php echo JText::_('COM_THM_ORGANIZER_EXPORT_PARTICIPANTS'); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo $departmentListRoute; ?>" target="_blank">
							<span class="icon-file-pdf"></span><?php echo JText::_('COM_THM_ORGANIZER_EXPORT_DEPARTMENTS'); ?>
						</a>
					</li>
					<li>
						<a href="<?php echo $badgesRoute; ?>" target="_blank">
							<span class="icon-file-pdf"></span><?php echo JText::_('COM_THM_ORGANIZER_EXPORT_BADGES'); ?>
						</a>
					</li>
				</ul>
			</div>

			<?php if (!empty($this->menu)): ?>
				<a href="<?php echo JRoute::_($this->menu['route'], false); ?>" class="btn btn-mini" type="button">
					<span class="icon-list"></span>
					<?php echo $menuText ?>
				</a>
			<?php endif; ?>
		</div>
		<div class="clear"></div>
		<table class="table table-striped">
			<thead>
			<tr>
				<th></th>
				<th><?php echo $this->lang->_('COM_THM_ORGANIZER_NAME'); ?></th>
				<th><?php echo $this->lang->_('COM_THM_ORGANIZER_PROGRAM'); ?></th>
				<th><?php echo $this->lang->_('JGLOBAL_EMAIL'); ?></th>
				<th><?php echo $this->lang->_('JSTATUS'); ?></th>
				<th><?php echo $this->lang->_('COM_THM_ORGANIZER_STATUS_DATE'); ?></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $item): ?>
				<tr>
					<td><input title='' type='checkbox' name='checked[]' value='<?php echo $item->cid; ?>'/></td>
					<td><?php echo $item->name; ?></td>
					<td><?php echo $item->program; ?></td>
					<td><?php echo $item->email; ?></td>
					<td><?php echo $item->status ? $registeredText : $waitListText; ?></td>
					<td><?php echo JHtml::_('date', $item->status_date, $dateFormat); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</form>
	<?php $this->loadTemplate('circular'); ?>
</div>