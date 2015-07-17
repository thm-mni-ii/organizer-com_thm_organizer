<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view curriculum default
 * @description consumption view default layout
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
$showHeading = $this->params->get('show_page_heading', '');
$title = $this->params->get('page_title', '');
?>
<div id="deputat" class="deputat">
    <form id='deputat-form' name='deputat-form' enctype='multipart/form-data' method='post' action='<?php echo JURI::current(); ?>' >
<?php
if (!empty($showHeading))
{
?>
        <h2 class="componentheading">
            <?php echo $title; ?>
        </h2>
<?php
}/*
if (!empty($this->model->schedule))
{
?>
        <div class="button-panel">
            <button type="submit" value="submit"><i class="icon-forward-2"></i> <?php echo JText::_('JSUBMIT'); ?></button>
            <button onclick="jQuery('#reset').val('1')"><i class="icon-delete"></i> <?php echo JText::_('COM_THM_ORGANIZER_ACTION_RESET'); ?></button>
            <button id="export"><i class="icon-download"></i> <?php echo JText::_("COM_THM_ORGANIZER_ACTION_EXPORT_EXCEL"); ?></button>
            <input type="hidden" id="reset" name="reset" value="0" />
        </div>
<?php
}*/
?>
        <div class="filter-bar">
            <div class="filter-header">
                <div class="control-group">
                    <div class="controls">
                        <?php echo $this->scheduleSelectBox; ?>
                    </div>
                </div>
            </div>
<?php
if (!empty($this->model->schedule))
{
?>
            <div id="filter-resource" class="filter-resource" style="display: none">
                <div class="control-group">
                    <div class="control-label">
                        <label for="teachers"><?php echo JText::_('COM_THM_ORGANIZER_TEACHER')?></label>
                    </div>
                    <div class="controls">
                        <?php echo $this->teachersSelectBox; ?>
                    </div>
                </div>
            </div>
<?php
}
?>
        </div>
<?php
if (!empty($this->model->schedule))
{
?>
        <?php echo $this->table; ?>
<?php
}
?>
    </form>
    <a id="dLink" style="display:none;"></a>
</div>
