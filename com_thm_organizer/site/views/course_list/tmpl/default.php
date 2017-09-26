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
<div>

	<div class="toolbar">
		<div class="tool-wrapper language-switches">
			<?php foreach ($this->languageSwitches AS $switch)
			{
				echo $switch;
			} ?>
		</div>
	</div>

	<h1> <?php echo $this->lang->_("COM_THM_ORGANIZER_PREP_COURSES_HEADER") ?></h1> <br>

	<?php if (!$this->loggedIn)
	{
		$loginCASRoute = JRoute::_('index.php?option=com_users&view=login', false, 2);
		$registerRoute = JRoute::_('index.php?option=com_users&view=registration', false, 2);
		printf(
			"<div class='alert alert-warning'>" . $this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_MESSAGE_LOGIN_PREFIX") . "<br>" .
			$this->lang->_("COM_THM_ORGANIZER_PREP_COURSE_MESSAGE_LOGIN_REQUEST") . "</div>",
			"<a href='$loginCASRoute'><span class='icon-apply'></span></a><br>",
			"<a href='#login-form'><span class='icon-apply'></span></a><br>",
			"<a href='$registerRoute'><span class='icon-pencil-2'></span></a>"
		);
	}
	else
	{
		$profileRoute = JRoute::_("index.php?option=com_thm_organizer&view=participant_edit&languageTag={$this->shortTag}");
		echo "<a class='btn btn-max' href='$profileRoute'><span class='icon-pencil-2'></span> {$this->lang->_("COM_THM_ORGANIZER_USER_PROFILE")}</a>";
	}

	if ($this->oneAuth)
	{
		echo $this->loadTemplate('filter');
	}

	?>

	<table class="table table-striped">
		<thead>
		<tr>
			<?php
			echo "<th class='left'>{$this->lang->_("COM_THM_ORGANIZER_NAME")}</th>";
			echo "<th class='left'>{$this->lang->_("COM_THM_ORGANIZER_START_DATE")}</th>";
			echo "<th class='left'>{$this->lang->_("COM_THM_ORGANIZER_END_DATE")}</th>";
			if ($this->loggedIn)
			{
				echo "<th class='left'>{$this->lang->_("COM_THM_ORGANIZER_STATE")}</th>";
				echo "<th class='left'> </th>";
			}

			if ($this->oneAuth)
			{
				echo "<th class='left'>{$this->lang->_("COM_THM_ORGANIZER_MODERATOR")}</th>";
			}
			?>
		</tr>
		</thead>

		<tbody>

		<?php
		foreach ($this->items as $item)
		{
			$this->item = $item;
			echo $this->loadTemplate('list');
		}
		?>

		</tbody>
	</table>

</div>