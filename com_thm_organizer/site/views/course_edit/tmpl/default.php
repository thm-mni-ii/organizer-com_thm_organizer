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

// Sets page configuration and component option
$backURL = empty($this->menu) ? JUri::base() . '?option=com_thm_organizer&' : $this->menu['route'] . '?';
$backURL .= "languageTag={$this->languageTag}";

// Accessed from subject_details
$backURL .= empty($this->lessonID) ?
	"&view=subject_details&id={$this->subjectID}" : "&view=course_manager&lessonID={$this->lessonID}";
?>
<div class="toolbar">
	<div class="tool-wrapper language-switches">
		<?php foreach ($this->languageSwitches AS $switch)
		{
			echo $switch;
		} ?>
	</div>
</div>
<div class="course-edit-view">
	<h1><?php echo $this->lang->_("COM_THM_ORGANIZER_ACTION_EDIT"); ?></h1>
	<form action="index.php?" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm"
		  class="form-horizontal">

		<button type="submit" class="validate btn btn-primary">
			<?php echo $this->lang->_('JSAVE'); ?>
		</button>

		<a href="<?php echo JRoute::_($backURL, false); ?>"
		   class="btn" type="button"><?php echo $this->lang->_("JCANCEL") ?></a>

		<hr>


		<div class="form-horizontal">
			<?php
			echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']);
			foreach ($this->form->getFieldSets() as $set)
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
		<?php echo JHtml::_('form.token'); ?>
		<input type="hidden" name="option" value="com_thm_organizer"/>
		<input type="hidden" name="task" value="course.save"/>
		<?php echo $this->form->getInput('id'); ?>
		<input type="hidden" name="lessonID" value="<?php echo $this->lessonID; ?>"/>
		<input type="hidden" name="languageTag" value="<?php echo $this->languageTag; ?>"/>
		<?php if (!empty($this->menu)): ?>
			<input type="hidden" name="Itemid" value="<?php echo $this->menu['id']; ?>"/>
		<?php endif; ?>
	</form>
</div>