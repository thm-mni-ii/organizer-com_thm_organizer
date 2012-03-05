<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        default.php
 * @description default template for the virtual schedule edit view
 * @author      Wolf Rost
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die ('Restricted access');
?>
<form action="index.php" method="post" name="adminForm">
<div class="col100">
	<fieldset class="adminform">
		<legend><?php if(isset($this->cid)) echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_EDIT" )." #".$this->cid; else echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_NEW" ); ?></legend>
		<table class="admintable">
			<tr>
				<td class="key">
					<label for="vscheduler_name"><?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_NAME" ); ?></label>
				</td>
				<td>
					<input class="text_area" type="text" name="vscheduler_name" id="vscheduler_name" maxlength="100"
							value="<?php echo $this->name; ?>" />
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td class="key">
					<label for="vscheduler_type"><?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_TYPE" ); ?></label>
				</td>
				<td>
					<?php echo $this->types; ?>
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td class="key">
					<label for="vscheduler_semid"><?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_SEMESTER" ); ?></label>
				</td>
				<td>
					<?php echo $this->semesters; ?>
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td class="key">
					<label for="vscheduler_resps"><?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_RESPONSIBLE" ); ?></label>
				</td>
				<td>
					<?php echo $this->resps; ?>
				</td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td class="key">
					<label for="vscheduler_dep"><?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_DEPARTMENT" ); ?></label>
				</td>
				<td>
					<?php echo $this->classesDepartments; ?>
				</td>
				<td>
					<?php echo $this->teacherDepartments; ?>
				</td>
				<td>
					<?php echo $this->roomDepartments; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="vscheduler_resss"><?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_RESOURCES" ); ?></label>
				</td>
				<td>
					<?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_CLASSES" ); ?><br/>
					<?php echo $this->classes; ?>
				</td>
				<td>
					<?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_ROOMS" ); ?><br/>
					<?php echo $this->rooms; ?>
				</td>
				<td>
					<?php echo JText::_( "COM_THM_ORGANIZER_VSE_LABEL_TEACHERS" ); ?><br/>
					<?php echo $this->teachers; ?>
				</td>
			</tr>
		</table>
	</fieldset>
</div>

<div id="itemselector" class="demo-ct"></div>

<input type="hidden" name="option" value="com_thm_organizer" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="view" value="virtual_schedule_edit" />
<input type="hidden" name="controller" value="virtual_schedule_edit" />
<input type="hidden" name="cid" value="<?php if(isset($this->cid)) echo $this->cid ?>" />

</form>

<script type="text/javascript" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all.js"></script>
<script type="text/javascript" src="components/com_thm_organizer/views/virtual_schedule_edit/tmpl/js/hideshowmultiselect.js"></script>