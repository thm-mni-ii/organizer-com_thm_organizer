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

$infoSpan = '&nbsp;<span class="icon-info"></span>';
$initialHidden = array('date');
?>
<script type="text/javascript">
	var rootURI = '<?php echo JUri::root(); ?>', allText = '<?php echo JText::_('JALL');?>',
		selectionWarning = '<?php echo JText::_('COM_THM_ORGANIZER_EXPORT_SELECTION_WARNING');?>',
		downloadText = '<?php echo JText::_('COM_THM_ORGANIZER_ACTION_DOWNLOAD');?>',
		generateText = '<?php echo JText::_('COM_THM_ORGANIZER_ACTION_GENERATE_LINK');?>',
		copyText = '<?php echo JText::_('COM_THM_ORGANIZER_COPY_SUBSCRIPTION');?>';
</script>
<div id="j-main-container">
	<form action="index.php?option=com_thm_organizer&view=room_statistics&format=xls"
          method="post" name="adminForm" id="adminForm" target="_blank"">
		<div id="header-container" class="header-container">
			<div class="header-title">
				<?php echo JText::_('COM_THM_ORGANIZER_ROOM_STATISTICS_TITLE'); ?>
			</div >
			<div class="toolbar">
				<button id="action-btn" class="btn" type="submit">
					<?php echo JText::_('COM_THM_ORGANIZER_ACTION_DOWNLOAD')?>
					<span class="icon-file-xls"></span>
				</button>
			</div>
			<div class="clear"></div>
		</div>
        <fieldset>
            <?php
            foreach ($this->fields['baseSettings'] as $settingID => $setting)
            {
                $hidden = in_array($settingID, $initialHidden)? 'style="display: none;"' : '';
                echo '<div id="' . $settingID . '-container" class="control-item" ' . $hidden . '>';
                echo '<div class="control-label">';
                echo '<label title="' . $setting['description'] . '" for="' . $settingID . '">';
                echo '<span class="label-text">' . $setting['label'] . '</span>' . $infoSpan;
                echo '</label>';
                echo '</div>';
                echo '<div class="controls">';
                echo $setting['input'];
                echo '</div>';
                echo '<div class="clear"></div>';
                echo '</div>';
            }
            ?>
        </fieldset>
		<fieldset>
			<legend>
				<?php echo $this->lang->_('COM_THM_ORGANIZER_FILTERS'); ?>
				<span class="disclaimer"><?php echo $this->lang->_('COM_THM_ORGANIZER_OPTIONAL'); ?></span>
			</legend>
			<?php
			foreach ($this->fields['filterFields'] as $filterID => $filter)
			{
				$hidden = in_array($filterID, $initialHidden)? 'style="display: none;"' : '';
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
        <input name="use" type="hidden" value="planningPeriodIDs" />
	</form>
</div>
