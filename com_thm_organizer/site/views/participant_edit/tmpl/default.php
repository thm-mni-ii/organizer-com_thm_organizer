<?php
/**
 * Created by PhpStorm.
 * User: Florian Fenzl
 * Date: 08.03.2017
 * Time: 11:02
 */
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/templates/edit_basic.php';
?>

<div class="front-end-edit">
	<div class="toolbar">
		<div class="tool-wrapper language-switches">
			<?php
			foreach ($this->languageSwitches AS $switch)
			{
				echo $switch;
			} ?>
		</div>
	</div>

	<?php
	if (empty($this->course))
	{
		echo "<h1>{$this->lang->_("COM_THM_ORGANIZER_USER_PROFILE")}</h1>";
	}
	else
	{
		echo "<h3>{$this->lang->_("COM_THM_ORGANIZER_PARTICIPANT_EDIT_REGISTER_HEADER")}</h3>";
		echo "<h1>{$this->course["name"]}</h1>";
	}

	if (!empty($this->dates))
	{
		$dateFormat = JComponentHelper::getParams('com_thm_organizer')->get('dateFormat', 'd.m.Y');
		$start_f = JHtml::_('date', $this->dates[0]["schedule_date"],   $dateFormat);
		$end_f   = JHtml::_('date', end($this->dates)["schedule_date"], $dateFormat);
		echo "<div>$start_f - $end_f</div>";
	}

	if ($this->signedIn)
	{
		if ($this->signedIn["status"] == 1)
		{
			$status = 'success';
			$msg = 'COM_THM_ORGANIZER_PREP_COURSE_STATE_REGISTERED';
		}
		else
		{
			$status = 'warning';
			$msg = 'COM_THM_ORGANIZER_PREP_COURSE_STATE_WAIT_LIST';
		}
	}
	else
	{
		$status = 'error';
		$msg = 'COM_THM_ORGANIZER_PREP_COURSE_STATE_NOT_REGISTERED';
	}

	if (!empty($this->course))
	{
		echo "<br><div class='alert alert-$status'>{$this->lang->_($msg)}</div>";
	}
	?>

	<form action="index.php?option=com_thm_organizer&task=participant.save"
		  enctype="multipart/form-data"
		  method="post"
		  id="form-participant_edit"
		  class="form-horizontal">



		<?php
		if (!empty($this->course))
		{
			echo "<input type='hidden' name='id' value='{$this->course["id"]}'/>";
		}

		echo "<input type='hidden' name='redirect' value='course_list'/>";
		?>

		<div class="form-horizontal">

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

		<?php echo JHtml::_('form.token'); ?>
		<div class="control-group">
			<div class="controls">
				<button type="submit" class="validate btn btn-primary">
					<?php echo (empty($this->course) ?
							$this->lang->_('JSAVE') :
							($this->signedIn ?
								$this->lang->_('JLOGOUT') :
								$this->lang->_('JLOGIN'))); ?>
				</button>
				<a  href="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=course_list', false, 2); ?>"
				   class="btn" type="button"><?php echo $this->lang->_("JCANCEL") ?></a>
			</div>
		</div>
	</form>
</div>

