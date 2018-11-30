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

if (!empty($this->languageSwitches)): ?>
    <div class="toolbar">
        <div class="tool-wrapper language-switches">
            <?php foreach ($this->languageSwitches as $switch) {
                echo $switch;
            } ?>
        </div>
    </div>
    <div class="clear"></div>
<?php endif; ?>
<div class="room-overview-view">
    <form action="" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="languageTag" id="languageTag" value=""/>
        <div id="form-container" class="form-container">
            <h1 class="componentheading"><?php echo $this->lang->_('COM_THM_ORGANIZER_ROOM_OVERVIEW_TITLE'); ?></h1>
            <div class="right">
                <button class="btn submit-button" onclick="showPostLoader();form.submit();">
                    <?php echo JText::_('COM_THM_ORGANIZER_ACTION_REFRESH'); ?>
                    <span class="icon-loop"></span>
                </button>
            </div>
            <div class="clear"></div>
            <div class='control-group'>
                <div class='control-label'>
                    <?php echo $this->getLabel('template'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('template')->input; ?>
                </div>
            </div>
            <div class='control-group'>
                <div class='control-label'>
                    <?php echo $this->getLabel('date'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('date')->input; ?>
                </div>
            </div>
            <div class='control-group'>
                <div class='control-label'>
                    <?php echo $this->getLabel('campusID'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('campusID')->input; ?>
                </div>
            </div>
            <div class='control-group'>
                <div class='control-label'>
                    <?php echo $this->getLabel('buildingID'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('buildingID')->input; ?>
                </div>
            </div>
            <div class="clear"></div>
            <div class='control-group-wide'>
                <div class='control-label'>
                    <?php echo $this->getLabel('types'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('types')->input; ?>
                </div>
            </div>
            <div class='control-group-wide'>
                <div class='control-label'>
                    <?php echo $this->getLabel('rooms'); ?>
                </div>
                <div class='controls'>
                    <?php echo $this->form->getField('rooms')->input; ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    </form>
    <div id="overview-container" class="overview-container">
        <?php
        if (empty($this->model->data) or empty($this->model->rooms)) {
            echo $this->loadTemplate('empty');
        } else {
            $template = $this->state->get('template', 1);
            if ($template == DAY) {
                echo $this->loadTemplate('day');
            }
            if ($template == WEEK) {
                echo $this->loadTemplate('week');
            }
        }
        ?>
    </div>
</div>
