<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view teacher emerge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$nameTitle = JText::_('COM_THM_ORGANIZER_NAME') . "::" . JText::_('COM_THM_ORGANIZER_SCH_NAME_DESC');
$semesterTitle = JText::_('COM_THM_ORGANIZER_SCH_SEMESTER_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_SCH_SEMESTER_DESC');
$schedulesTitle = JText::_('COM_THM_ORGANIZER_SCH_SCHEDULES_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_SCH_SCHEDULES_DESC');
?>
<form action="index.php?option=com_thm_organizer" method="post" name="adminForm">
	<fieldset class="adminform">
		<legend>
			<?php echo JText::_('COM_THM_ORGANIZER_SCH_PROPERTIES')?>
		</legend>
		<ul>
			<li class="hasTip" title="<?php echo $nameTitle; ?>">
				<label for='departmentname' class="required"><?php echo JText::_('COM_THM_ORGANIZER_NAME'); ?></label>
				<input id='departmentname' name='departmentname' type='text' class='inputbox'/>
			</li>
			<li class="hasTip" title="<?php echo $semesterTitle; ?>">
				<label for='semestername' class="required"><?php echo JText::_('COM_THM_ORGANIZER_SCH_SEMESTER_TITLE'); ?></label>
				<input id='semestername' name='semestername' type='text' class='inputbox'/>
			</li>
			<li class="hasTip" title="<?php echo $schedulesTitle; ?>">
				<fieldset style="clear: left; float: left;">
					<legend><?php echo JText::_('COM_THM_ORGANIZER_SCH_SCHEDULES_TITLE'); ?></legend>
					<ul>
<?php
foreach ($this->schedules as $schedule)
{
	echo "<li>";
	echo "<input name='schedules[]' type='checkbox' value='{$schedule['id']}' checked/>";
	echo "{$schedule['departmentname']} - {$schedule['semestername']}";
	echo "</li>";
}
?>
					</ul>
				</fieldset>
			</li>
		</ul>
	</fieldset>
	<div>
        <input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
