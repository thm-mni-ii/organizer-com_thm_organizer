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

$showHeading = $this->params->get('show_page_heading', '');
$title       = $this->params->get('page_title', '');
if (!empty($this->departmentName)) {
    $title .= " - $this->departmentName";
}
$weeks = $this->params->get('deputat_weeks', 13);
?>
<div id="deputat" class="deputat">
    <?php
    if (!empty($showHeading)) {
        ?>
        <div class="componentheading">
            <?php echo $title; ?>
        </div>
        <?php
    }
    ?>
    <form id='deputat-form' name='deputat-form' enctype='multipart/form-data' method='post'
          action='<?php echo Uri::current(); ?>'>
        <div class="filter-bar">
            <div class="filter-header">
                <div class="deputat-settings">
                    <div class="deputat-settings-description">
                        <?php echo Languages::_('THM_ORGANIZER_DEPUTAT_CALCULATION_SETTINGS'); ?>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo Languages::_('THM_ORGANIZER_DEPUTAT_WEEKS'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $weeks . ' ' . Languages::_('THM_ORGANIZER_WEEKS'); ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo Languages::_('THM_ORGANIZER_BACHELOR_VALUE'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $this->params->get('bachelor_value', 25) . '%'; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <?php echo Languages::_('THM_ORGANIZER_MASTER_VALUE'); ?>
                        </div>
                        <div class="controls">
                            <?php echo $this->params->get('master_value', 50) . '%'; ?>
                        </div>
                    </div>
                </div>
                <div class="selection-settings">
                    <div class="control-group">
                        <div class="control-label">
                            <label for="schedules">
                                <?php echo Languages::_('THM_ORGANIZER_DATA_SOURCE') ?>
                            </label>
                        </div>
                        <div class="controls">
                            <?php echo $this->scheduleSelectBox; ?>
                        </div>
                    </div>
                    <?php
                    if (!empty($this->model->schedule)) {
                        ?>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="teachers">
                                    <?php echo Languages::_('THM_ORGANIZER_TEACHERS') ?>
                                </label>
                            </div>
                            <div class="controls">
                                <?php echo $this->teachers; ?>
                            </div>
                        </div>
                        <div class="button-group">
                            <button type="submit">
                                <?php echo Languages::_('THM_ORGANIZER_ACTION_SHOW') ?>
                                <span class="icon-play"></span>
                            </button>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if (!empty($this->model->schedule)) {
            echo $this->tables;
        }
        ?>
    </form>
    <a href="https://www.thm.de/semesterplan-dev/service/werkzeug/deputat-fb-bau.html?format=xls">form</a>
    <a id="dLink" style="display:none;"></a>
</div>
