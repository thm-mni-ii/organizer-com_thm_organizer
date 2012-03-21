<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        room managaer edit template
 * @author      Markus Bader markusDOTbaderATmniDOTthmDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined("_JEXEC") or die;
JHtml::_('behavior.tooltip');
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=room_edit&layout=edit&id='.(int) $this->item->id); ?>"
      method="post" name="adminForm" id="room_edit-form">
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_THM_ORGANIZER_RM_DETAILS' ); ?></legend>
		<ul class="adminformlist">
			<?php $emptyID = false; ?>
			<?php foreach($this->form->getFieldset() as $field): ?>
				<li><?php	
				echo $field->label;
				if ($field->fieldname == 'id' && $field->value == '') {
					$emptyID = true;
				}
				if ($field->fieldname == 'gpuntisID' && $emptyID == false) {
					$input = str_replace('type="text"', 'type="text" readonly="readonly"', $field->input);
					echo $input;
				} else {
					echo $field->input;
				} ?></li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="room_edit.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>