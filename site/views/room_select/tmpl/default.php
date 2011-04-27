<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        room selection default layout
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
?>
<div id="thm_organizer_rs">
    <fieldset id="thm_organizer_rs_fieldset">
	<legend id="thm_organizer_rs_legend"><?php echo JText::_('COM_THM_ORGANIZER_RS_SELECTION_TEXT'); ?></legend>
        <form enctype="multi" action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=room_display'); ?>"
              method="post" name="room_select" id="thm_organizer_rs_form" class="form-validate">
            <?php echo $this->form->getLabel('room'); ?>
            <?php echo $this->form->getInput('room'); ?>
            <br /><br />
            <?php echo $this->form->getLabel('date'); ?>
            <?php echo $this->form->getInput('date'); ?>
            <br /><br />
            <input type="submit" value="Submit">
	</form>
    </fieldset>
</div>