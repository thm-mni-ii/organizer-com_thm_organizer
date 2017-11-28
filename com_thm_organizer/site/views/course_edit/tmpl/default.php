<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
?>
<div>
	<div class="toolbar">
		<div class="tool-wrapper language-switches">
			<?php foreach ($this->languageSwitches AS $switch)
			{
				echo $switch;
			} ?>
		</div>
	</div>

	<form action="index.php?option=com_thm_organizer&task=course_edit.save"
		  enctype="multipart/form-data"
		  method="post"
		  name="adminForm"
		  id="item-form"
		  class="form-horizontal">

		<input type="hidden" name="lessonID" value="<?php echo $this->lessonID; ?>"/>
		<input type='hidden' name='redirect' value="course_manager"/>

		<button type="submit" class="validate btn btn-primary">
			<?php echo $this->lang->_('JSAVE'); ?>
		</button>

		<a href="<?php echo JRoute::_(
			(empty($this->lessonID) ?
				('index.php?option=com_thm_organizer&view=course_list') :
				("index.php?option=com_thm_organizer&view=course_manager&lessonID={$this->lessonID}"))
		); ?>"
		   class="btn" type="button"><?php echo $this->lang->_("JCANCEL") ?></a>

		<hr>

		<h1><?php echo $this->lang->_("COM_THM_ORGANIZER_ACTION_EDIT"); ?></h1>

		<div class="form-horizontal">

			<?php
			echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']);
			$sets = $this->form->getFieldSets();
			foreach ($sets as $set)
			{
				$isInitialized  = (bool) $this->form->getValue('id');
				$displayInitial = isset($set->displayinitial) ? $set->displayinitial : true;
				if ($displayInitial OR $isInitialized)
				{
					echo JHtml::_('bootstrap.addTab', 'myTab', $set->name, JText::_($set->label, true));
					echo $this->form->renderFieldset($set->name);
					echo JHtml::_('bootstrap.endTab');
				}
			}
			echo JHtml::_('bootstrap.endTabSet');
			?>
		</div>
		<?php echo $this->form->getInput('id'); ?>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>