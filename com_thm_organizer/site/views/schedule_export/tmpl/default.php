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

$infoSpan      = '&nbsp;<span class="icon-info"></span>';
$initialHidden = ['xlsWeekFormat'];
$user          = JFactory::getUser();
?>
<script type="text/javascript">
	var rootURI = '<?php echo JUri::root(); ?>',
		allText = '<?php echo JText::_('JALL');?>',
		selectionWarning = '<?php echo JText::_('COM_THM_ORGANIZER_EXPORT_SELECTION_WARNING');?>',
		downloadText = '<?php echo JText::_('COM_THM_ORGANIZER_ACTION_DOWNLOAD');?>',
		generateText = '<?php echo JText::_('COM_THM_ORGANIZER_ACTION_GENERATE_LINK');?>',
		copyText = '<?php echo JText::_('COM_THM_ORGANIZER_COPY_SUBSCRIPTION');?>',
		registered = <?php echo $user->id; ?>,
		si = <?php echo JFactory::getApplication()->getMenu()->getActive()->params->get('si', 0); ?>;

	<?php if ($user->id !== 0) :?>
	var username = '<?php echo $user->username; ?>',
		auth = '<?php echo urlencode(password_hash($user->email . $user->registerDate, PASSWORD_BCRYPT)); ?>';
	<?php endif; ?>

</script>
<div id="j-main-container">
	<form action="index.php?" method="post" name="adminForm" id="adminForm" target="_blank"
	">
	<div id="header-container" class="header-container">
		<div class="header-title">
			<?php echo JText::_('COM_THM_ORGANIZER_SCHEDULE_EXPORT_TITLE'); ?>
		</div>
		<div class="toolbar">
			<a id="action-btn" class="btn" onclick="handleSubmit();">
				<?php echo JText::_('COM_THM_ORGANIZER_ACTION_DOWNLOAD') ?>
				<span class="icon-file-pdf"></span>
			</a>
		</div>
		<div class="clear"></div>
	</div>
	<fieldset id="resourceFields">
		<legend>
			<?php echo $this->lang->_('COM_THM_ORGANIZER_SELECTION'); ?>
			<span class="disclaimer"><?php echo $this->lang->_('COM_THM_ORGANIZER_SELECTION_DESC'); ?></span>
		</legend>
		<?php
		foreach ($this->fields['resourceFields'] as $resourceID => $resource)
		{
			$hidden = in_array($resourceID, $initialHidden) ? 'style="display: none;"' : '';
			echo '<div id="' . $resourceID . '-container" class="control-item" ' . $hidden . '>';
			echo '<div class="control-label">';
			echo '<label title="' . $resource['description'] . '" for="' . $resourceID . '">';
			echo '<span class="label-text">' . $resource['label'] . '</span>' . $infoSpan;
			echo '</label>';
			echo '</div>';
			echo '<div class="controls">';
			echo $resource['input'];
			echo '</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
		?>
	</fieldset>
	<fieldset id="filterFields">
		<legend>
			<?php echo $this->lang->_('COM_THM_ORGANIZER_FILTERS'); ?>
			<span class="disclaimer"><?php echo $this->lang->_('COM_THM_ORGANIZER_OPTIONAL'); ?></span>
		</legend>
		<?php
		foreach ($this->fields['filterFields'] as $filterID => $filter)
		{
			$hidden = in_array($filterID, $initialHidden) ? 'style="display: none;"' : '';
			echo '<div id="' . $filterID . '-container" class="control-item" ' . $hidden . '>';
			echo '<div class="control-label">';
			echo '<label title="' . $filter['description'] . '" for="' . $filterID . '">';
			echo '<span class="label-text">' . $filter['label'] . '</span>' . $infoSpan;
			echo '</label>';
			echo '</div>';
			echo '<div class="controls">';
			echo $filter['input'];
			echo '</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
		?>
	</fieldset>
	<fieldset>
		<legend><?php echo $this->lang->_('COM_THM_ORGANIZER_FORMAT_SETTINGS'); ?></legend>
		<?php
		foreach ($this->fields['formatSettings'] as $formatFieldID => $formatField)
		{
			$hidden = in_array($formatFieldID, $initialHidden) ? 'style="display: none;"' : '';
			echo '<div id="' . $formatFieldID . '-container" class="control-item" ' . $hidden . '>';
			echo '<div class="control-label">';
			echo '<label title="' . $formatField['description'] . '" for="' . $formatFieldID . '">';
			echo '<span class="label-text">' . $formatField['label'] . '</span>' . $infoSpan;
			echo '</label>';
			echo '</div>';
			echo '<div class="controls">';
			echo $formatField['input'];
			echo '</div>';
			echo '<div class="clear"></div>';
			echo '</div>';
		}
		?>
	</fieldset>
	<input type="hidden" name="option" value="com_thm_organizer"/>
	<input type="hidden" name="view" value="schedule_export"/>
	<input type="hidden" name="format" value="pdf"/>
	<input type="hidden" name="documentFormat" value="a4"/>
	<?php if ($user->id != 0): ?>
		<input type="hidden" name="myschedule" value="0"/>
	<?php endif; ?>
	</form>
</div>
