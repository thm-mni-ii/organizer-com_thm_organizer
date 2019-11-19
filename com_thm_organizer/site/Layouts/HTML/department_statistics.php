<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

$infoSpan      = '&nbsp;<span class="icon-info"></span>';
$initialHidden = ['date'];
?>
<div id="j-main-container">
    <form action="index.php?option=com_thm_organizer&view=department_occupancy&format=xls"
          method="post" name="adminForm" id="adminForm" target="_blank">
        <div id="header-container" class="header-container clearfix">
            <div class="header-title">
				<?php echo Languages::_('THM_ORGANIZER_DEPARTMENT_OCCUPANCY'); ?>
            </div>
            <div class="toolbar">
                <button id="action-btn" class="btn" type="submit">
					<?php echo Languages::_('THM_ORGANIZER_DOWNLOAD') ?>
                    <span class="icon-file-excel"></span>
                </button>
            </div>
        </div>
        <fieldset>
			<?php
			foreach ($this->fields['baseSettings'] as $settingID => $setting)
			{
				$hidden = in_array($settingID, $initialHidden) ? 'style="display: none;"' : '';
				echo '<div id="' . $settingID . '-container" class="control-item clearfix" ' . $hidden . '>';
				echo '<div class="control-label">';
				echo '<label title="' . $setting['description'] . '" for="' . $settingID . '">';
				echo '<span class="label-text">' . $setting['label'] . '</span>' . $infoSpan;
				echo '</label>';
				echo '</div>';
				echo '<div class="controls">';
				echo $setting['input'];
				echo '</div>';
				echo '</div>';
			}
			?>
        </fieldset>
        <fieldset>
            <legend>
				<?php echo Languages::_('THM_ORGANIZER_FILTERS'); ?>
                <span class="disclaimer"><?php echo Languages::_('THM_ORGANIZER_OPTIONAL'); ?></span>
            </legend>
			<?php
			foreach ($this->fields['filterFields'] as $filterID => $filter)
			{
				$hidden = in_array($filterID, $initialHidden) ? 'style="display: none;"' : '';
				echo '<div id="' . $filterID . '-container" class="control-item clearfix" ' . $hidden . '>';
				echo '<div class="control-label">';
				echo '<label title="' . $filter['description'] . '" for="' . $filterID . '">';
				echo '<span class="label-text">' . $filter['label'] . '</span>' . $infoSpan;
				echo '</label>';
				echo '</div>';
				echo '<div class="controls">';
				echo $filter['input'];
				echo '</div>';
				echo '</div>';
			}
			?>
        </fieldset>
    </form>
</div>
