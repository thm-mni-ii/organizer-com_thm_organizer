<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description default template for the thm organizer main menu view
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;
$logoURL = 'media/com_thm_organizer/images/thm_organizer.png';
$actions = $this->getModel()->actions;
//echo '<div id="j-sidebar-container" class="span2">' . $this->sidebar . '</div>';
?>
<div id="j-main-container" class="span5">
	<div class="span10 form-vertical actions">
		<div class="organizer-header">
			<div class="organizer-logo">
				<?php echo JHtml::_('image', $logoURL, JText::_('COM_THM_ORGANIZER'), ['class' => 'thm_organizer_main_image']); ?>
			</div>
		</div>
		<?php if ($actions->{'organizer.menu.schedule'}): ?>
			<div class="action-group">
				<h3><?php echo JText::_('COM_THM_ORGANIZER_SCHEDULING'); ?></h3>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=schedule_edit">
						<?php echo JText::_('COM_THM_ORGANIZER_SCHEDULE_UPLOAD'); ?>
						<span class="icon-upload"></span>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=schedule_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_SCHEDULE_MANAGER_TITLE'); ?>
					</a>
				</div>
				<?php if ($actions->{'core.admin'}): ?>
					<div class="action-item">
						<a href="index.php?option=com_thm_organizer&view=plan_program_manager">
							<?php echo JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE'); ?>
						</a>
					</div>
				<?php endif; ?>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=plan_pool_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE'); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($actions->{'organizer.menu.department'} OR $actions->{'organizer.menu.manage'}): ?>
			<div class="action-group">
				<h3><?php echo JText::_('COM_THM_ORGANIZER_MANAGEMENT_AND_DOCUMENTATION'); ?></h3>
				<?php if ($actions->{'organizer.menu.department'}): ?>
					<div class="action-item">
						<a href="index.php?option=com_thm_organizer&view=department_manager">
							<?php echo JText::_('COM_THM_ORGANIZER_DEPARTMENT_MANAGER_TITLE'); ?>
						</a>
					</div>
				<?php endif; ?>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=program_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_PROGRAM_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=pool_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_POOL_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=subject_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_SUBJECT_MANAGER_TITLE'); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($actions->{'organizer.hr'}): ?>
			<div class="action-group">
				<h3><?php echo JText::_('COM_THM_ORGANIZER_HUMAN_RESOURCES'); ?></h3>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=teacher_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_TEACHER_MANAGER_TITLE'); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($actions->{'organizer.fm'}): ?>
			<div class="action-group">
				<h3><?php echo JText::_('COM_THM_ORGANIZER_FACILITY_MANAGEMENT'); ?></h3>
				<!--<div class="action-item">
				<a href="index.php?option=com_thm_organizer&view=building_manager">
				    <?php echo JText::_('COM_THM_ORGANIZER_BUILDING_MANAGER_TITLE'); ?>
				</a>
			</div>
			<div class="action-item">
				<a href="index.php?option=com_thm_organizer&view=campus_manager">
				    <?php echo JText::_('COM_THM_ORGANIZER_CAMPUS_MANAGER_TITLE'); ?>
				</a>
			</div>
			<!-- <div class="action-item">
				<a href="index.php?option=com_thm_organizer&view=equipment_manager">
				    <?php echo JText::_('COM_THM_ORGANIZER_EQUIPMENT_MANAGER_TITLE'); ?>
				</a>
			</div> -->
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=monitor_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_MONITOR_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=room_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_ROOM_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=room_type_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_ROOM_TYPE_MANAGER_TITLE'); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
		<?php if ($actions->{'core.admin'}): ?>
			<div class="action-group">
				<h3><?php echo JText::_('COM_THM_ORGANIZER_ADMINISTRATION'); ?></h3>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=color_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_COLOR_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=degree_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_DEGREE_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=field_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_FIELD_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=grid_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_GRID_MANAGER_TITLE'); ?>
					</a>
				</div>
				<div class="action-item">
					<a href="index.php?option=com_thm_organizer&view=method_manager">
						<?php echo JText::_('COM_THM_ORGANIZER_METHOD_MANAGER_TITLE'); ?>
					</a>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>
