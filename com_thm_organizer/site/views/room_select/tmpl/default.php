<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        room select default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
?>
<div id="thm_organizer_rs">
    <fieldset id="thm_organizer_rs_fieldset" class='thm_organizer_rs_fieldset'>
    <legend id="thm_organizer_rs_legend" class='thm_organizer_rs_legend'><?php echo JText::_('COM_THM_ORGANIZER_RS_SELECTION_TEXT'); ?></legend>
        <form action="<?php echo 'index.php?option=com_thm_organizer&view=room_display'; ?>"
              method="post" name="room_select" id="thm_organizer_rs_form" class="form-validate">
            <?php echo $this->form->getLabel('room'); ?>
            <?php echo $this->form->getInput('room'); ?>
            <br /><br />
            <?php echo $this->form->getLabel('date'); ?>
            <?php echo $this->form->getInput('date'); ?>
            <br /><br />
            <input type="submit" value="Submit">
            <input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid'); ?>">
    </form>
    </fieldset>
</div>