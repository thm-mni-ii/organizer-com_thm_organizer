<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Uri\Uri;

$infoSpan      = '&nbsp;<span class="icon-info"></span>';
$initialHidden = ['date'];
?>
<script type="text/javascript">
    var rootURI = '<?php echo Uri::root(); ?>', allText = '<?php echo Languages::_('JALL');?>',
        selectionWarning = '<?php echo Languages::_('THM_ORGANIZER_EXPORT_SELECTION_WARNING');?>',
        downloadText = '<?php echo Languages::_('THM_ORGANIZER_DOWNLOAD');?>',
        generateText = '<?php echo Languages::_('THM_ORGANIZER_GENERATE_LINK');?>',
        copyText = '<?php echo Languages::_('THM_ORGANIZER_COPY_SUBSCRIPTION');?>';
</script>
<div id="j-main-container">
    <form action="index.php?option=com_thm_organizer&view=department_statistics&format=xls"
          method="post" name="adminForm" id="adminForm" target="_blank">
        <div id="header-container" class="header-container">
            <div class="header-title">
                <?php echo Languages::_('THM_ORGANIZER_DEPARTMENT_STATISTICS_TITLE'); ?>
            </div>
            <div class="toolbar">
                <button id="action-btn" class="btn" type="submit">
                    <?php echo Languages::_('THM_ORGANIZER_DOWNLOAD') ?>
                    <span class="icon-file-excel"></span>
                </button>
            </div>
            <div class="clear"></div>
        </div>
        <fieldset>
            <?php
            foreach ($this->fields['baseSettings'] as $settingID => $setting) {
                $hidden = in_array($settingID, $initialHidden) ? 'style="display: none;"' : '';
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
                <?php echo Languages::_('THM_ORGANIZER_FILTERS'); ?>
                <span class="disclaimer"><?php echo Languages::_('THM_ORGANIZER_OPTIONAL'); ?></span>
            </legend>
            <?php
            foreach ($this->fields['filterFields'] as $filterID => $filter) {
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
        <input name="use" type="hidden" value="termIDs"/>
    </form>
</div>
