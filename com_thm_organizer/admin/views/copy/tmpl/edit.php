<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view copy edit
 * @description THM_Curriculum component admin view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

// No direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
?>

<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=color&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" name="adminForm" id="farbe-form">
	<fieldset class="adminform">
		<legend>Details</legend>
		<ul class="adminformlist">
			<?php foreach ($this->form->getFieldset() as $field)
			{
				?>
			<li><?php echo $field->label;
			echo $field->input; ?>
			</li>
			<?php
}
			?>

		</ul>
	</fieldset>
	<div>
		<input type="hidden" name="task" value="farbe.edit" /> <input
			type="hidden" name="cid"
			value="<?php echo JRequest::getVar("cid"); ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
