<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\Languages;

$infoSpan      = '&nbsp;<span class="icon-info"></span>';
$initialHidden = ['format', 'pdfWeekFormat', 'displayFormat'];
?>
<script type="text/javascript">
    var rootURI = '<?php echo Uri::root(); ?>', allText = '<?php echo Languages::_('JALL');?>',
        selectionWarning = '<?php echo Languages::_('THM_ORGANIZER_EXPORT_SELECTION_WARNING');?>',
        downloadText = '<?php echo Languages::_('THM_ORGANIZER_DOWNLOAD');?>',
        generateText = '<?php echo Languages::_('THM_ORGANIZER_GENERATE_LINK');?>',
        copyText = '<?php echo Languages::_('THM_ORGANIZER_COPY_SUBSCRIPTION');?>';
    si = true;
</script>
<div id="j-main-container" class="export-si">
    <form action="index.php?" method="post" name="adminForm" id="adminForm" target="_blank">
        <div id="header-container" class="header-container">
            <div class="header-title">
                <?php echo Languages::_('THM_ORGANIZER_SCHEDULE_EXPORT_TITLE'); ?>
            </div>
            <div class="clear"></div>
        </div>
        <fieldset>
            <legend>
                <?php echo Languages::_('THM_ORGANIZER_FILTERS'); ?>
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
        <fieldset>
            <legend>
                <?php echo Languages::_('THM_ORGANIZER_SELECTION'); ?>
            </legend>
            <?php
            foreach ($this->fields['resourceFields'] as $resourceID => $resource) {
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
        <fieldset>
            <legend><?php echo Languages::_('THM_ORGANIZER_FORMAT_SETTINGS'); ?></legend>
            <?php
            foreach ($this->fields['formatSettings'] as $formatFieldID => $formatField) {
                $hidden = in_array($formatFieldID, $initialHidden);
                if ($hidden) {
                    continue;
                }

                echo '<div id="' . $formatFieldID . '-container" class="control-item" >';
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
        <a id="action-btn" class="btn" onclick="handleSubmit();">
            <?php echo Languages::_('THM_ORGANIZER_DOWNLOAD') ?>
        </a>
        <input type="hidden" name="option" value="com_thm_organizer"/>
        <input type="hidden" name="view" value="schedule_export"/>
        <input type="hidden" name="format" value="xls"/>
        <input type="hidden" name="documentFormat" value="si"/>
    </form>
</div>
