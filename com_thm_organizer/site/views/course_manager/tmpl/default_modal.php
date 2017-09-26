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

?>

<div class="modal fade" id="modal">

	<div class="modal-dialog">

		<div class="modal-content">

			<form action="index.php?option=com_thm_organizer&task=mailer.circular"
				  method="post" id="adminForm" name="adminForm">

				<div class="modal-header">

					<h3 style="float: left;"><?php echo $this->lang->_("COM_THM_ORGANIZER_CIRCULAR") ?></h3>
					<button type="button" class="btn btn-mini" data-dismiss="modal">
						<span class="icon-cancel"></span></button>
					<button id="submitBtn" type="submit" class="validate btn btn-mini">
						<span class="icon-mail"></span> <?php echo $this->lang->_("JSUBMIT") ?></button>

					<br>
					<br>

				</div>

				<div class="modal-body" style="overflow-y: auto;">

					<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
					<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
					<input type="hidden" name="lessonID" value="<?php echo $this->course["id"]; ?>"/>
					<input type="hidden" name="subjectID" value="<?php echo $this->course["subjectID"]; ?>"/>

					<?php
					foreach ($this->form->getFieldset() as $field)
					{
						echo "<div class='control-group'>";
						echo "<div class='control-label'>" . $field->label . "</div>";
						echo "<div class='controls'>" . $field->input . "</div>";
						echo "</div>";
					}
					?>

				</div>

			</form>

		</div>

	</div>

</div>

<a href="#" class="btn btn-mini callback-modal" type="button" data-toggle="modal" data-target="#modal">
	<span class="icon-mail"></span> <?php echo $this->lang->_("COM_THM_ORGANIZER_CIRCULAR") ?></a>
