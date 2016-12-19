<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @description consumption view default layout
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
$showHeading = $this->params->get('show_page_heading', '');
$title       = $this->params->get('page_title', '');
?>
<div id="consumption" class="consumption">
    <form id='statistic-form' name='statistic-form' enctype='multipart/form-data' method='post' action='index.php?'>
		<?php
		if (!empty($showHeading))
		{
			?>
            <h2 class="componentheading">
				<?php echo $title; ?>
            </h2>
			<?php
		}
		if (!empty($this->model->schedule))
		{
			?>
            <div class="button-panel">
                <button type="submit" value="submit"><i
                            class="icon-arrow-right-2"></i> <?php echo JText::_('COM_THM_ORGANIZER_ACTION_CALCULATE'); ?>
                </button>
                <button onclick="jQuery('#reset').val('1')"><i
                            class="icon-remove"></i> <?php echo JText::_('COM_THM_ORGANIZER_ACTION_RESET'); ?></button>
                <button id="export"><i
                            class="icon-file-excel"></i> <?php echo JText::_("COM_THM_ORGANIZER_ACTION_EXPORT_EXCEL"); ?>
                </button>
                <input type="hidden" id="reset" name="reset" value="0"/>
            </div>
			<?php
		}
		?>
        <div class="filter-bar">
            <div class="filter-header">
                <div class="control-group">
                    <div class="controls">
						<?php echo $this->scheduleSelectBox; ?>
                    </div>
                </div>
				<?php
				if (!empty($this->model->schedule))
				{
					?>
                    <div class="control-group">
                        <div class="controls">
							<?php echo $this->typeSelectBox; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="controls">
							<?php echo $this->hoursSelectBox; ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <label for="startDate"><?php echo JText::_('COM_THM_ORGANIZER_START_DATE') ?>:</label>
                        </div>
                        <div class="controls">
							<?php echo JHtml::calendar($this->model->startDate, 'startDate', 'startDate', '%d.%m.%Y') ?>
                        </div>
                    </div>
                    <div class="control-group">
                        <div class="control-label">
                            <label for="endDate"><?php echo JText::_('COM_THM_ORGANIZER_END_DATE') ?>:</label>
                        </div>
                        <div class="controls">
							<?php echo JHtml::calendar($this->model->endDate, 'endDate', 'endDate', '%d.%m.%Y') ?>
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
                <div class="filter-toggle">
                    <a class="toggle-link" onclick="toggle();">
                        <span id="filter-toggle-image" class="toggle-button toggle-closed"></span>
						<?php echo JText::_('COM_THM_ORGANIZER_FILTER_DISPLAY_ADDITIONAL'); ?>
                    </a>
                </div>
                <div id="filter-resource" class="filter-resource" style="display: none">
					<?php
					if ($this->model->type == ROOM)
					{
						?>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="roomTypes"><?php echo JText::_('COM_THM_ORGANIZER_TYPE') ?></label>
                            </div>
                            <div class="controls">
								<?php echo $this->roomTypesSelectBox; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="rooms"><?php echo JText::_('COM_THM_ORGANIZER_ROOM') ?></label>
                            </div>
                            <div class="controls">
								<?php echo $this->roomsSelectBox; ?>
                            </div>
                        </div>
						<?php
					}
					if ($this->model->type == TEACHER)
					{
						?>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="fields"><?php echo JText::_('COM_THM_ORGANIZER_FIELD') ?></label>
                            </div>
                            <div class="controls">
								<?php echo $this->fieldsSelectBox; ?>
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="control-label">
                                <label for="teachers"><?php echo JText::_('COM_THM_ORGANIZER_TEACHER') ?></label>
                            </div>
                            <div class="controls">
								<?php echo $this->teachersSelectBox; ?>
                            </div>
                        </div>
						<?php
					}
					?>
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
        <input type='hidden' name='option' value='com_thm_organizer'/>
        <input type='hidden' name='view' value='consumption'/>
    </form>
    <a id="dLink" style="display:none;"></a>
</div>
