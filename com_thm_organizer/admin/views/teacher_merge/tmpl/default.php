<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view teacher emerge
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form action="index.php?option=com_thm_organizer" method="post" name="adminForm">
	<fieldset class="adminform">
		<legend>
			<?php echo JText::_('COM_THM_ORGANIZER_TRM_PROPERTIES')?>
		</legend>
<?php if (count($this->surname))
{
	$surnameTitle = JText::_('COM_THM_ORGANIZER_TRM_SURNAME_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_TRM_SURNAME_DESC');
	echo '<fieldset class="adminform hasTip" title="' . $surnameTitle . '">';
	echo '<legend>' . JText::_('COM_THM_ORGANIZER_TRM_SURNAME_TITLE') . '</legend>';
	echo '<ul class="adminformlist">';
	foreach ($this->surname AS $surname)
	{
	echo '<li>' . $surname . '</li>';
	}
	echo '</ul></fieldset>';
}
if (count($this->forename))
{
	$forenameTitle = JText::_('COM_THM_ORGANIZER_TRM_FORENAME_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_TRM_FORENAME_DESC');
	echo '<fieldset class="adminform hasTip" title="' . $forenameTitle . '">';
	echo '<legend>' . JText::_('COM_THM_ORGANIZER_TRM_FORENAME_TITLE') . '</legend>';
	echo '<ul class="adminformlist">';
	foreach ($this->forename AS $forename)
	{
	echo '<li>' . $forename . '</li>';
	}
	echo '</ul></fieldset>';
}
if (count($this->title))
{
	$titleTitle = JText::_('COM_THM_ORGANIZER_TRM_TITLE_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_TRM_TITLE_DESC');
	echo '<fieldset class="adminform hasTip" title="' . $titleTitle . '">';
	echo '<legend>' . JText::_('COM_THM_ORGANIZER_TRM_TITLE_TITLE') . '</legend>';
	echo '<ul class="adminformlist">';
	foreach ($this->title AS $title)
	{
	echo '<li>' . $title . '</li>';
	}
	echo '</ul></fieldset>';
}
if (count($this->username))
{
	$usernameTitle = JText::_('COM_THM_ORGANIZER_TRM_USERNAME_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_TRM_USERNAME_DESC');
	echo '<fieldset class="adminform hasTip" title="' . $usernameTitle . '">';
	echo '<legend>' . JText::_('COM_THM_ORGANIZER_TRM_USERNAME_TITLE') . '</legend>';
	echo '<ul class="adminformlist">';
	foreach ($this->username AS $username)
	{
	echo '<li>' . $username . '</li>';
	}
	echo '</ul></fieldset>';
}
if (count($this->gpuntisID))
{
	$gpuntisIDTitle = JText::_('COM_THM_ORGANIZER_GPUNTISID') . "::" . JText::_('COM_THM_ORGANIZER_TRM_GPUNTISID_DESC');
	echo '<fieldset class="adminform hasTip" title="' . $gpuntisIDTitle . '">';
	echo '<legend>' . JText::_('COM_THM_ORGANIZER_GPUNTISID') . '</legend>';
	echo '<ul class="adminformlist">';
	foreach ($this->gpuntisID AS $gpuntisID)
	{
	echo '<li>' . $gpuntisID . '</li>';
	}
	echo '</ul></fieldset>';
}
if (count($this->fieldID))
{
	$fieldIDTitle = JText::_('COM_THM_ORGANIZER_TRM_FIELD_TITLE') . "::" . JText::_('COM_THM_ORGANIZER_TRM_FIELD_DESC');
	echo '<fieldset class="adminform hasTip" title="' . $fieldIDTitle . '">';
	echo '<legend>' . JText::_('COM_THM_ORGANIZER_TRM_FIELD_TITLE') . '</legend>';
	echo '<ul class="adminformlist">';
	foreach ($this->fieldID AS $fieldID)
	{
	echo '<li>' . $fieldID . '</li>';
	}
	echo '</ul></fieldset>';
}
?>
	</fieldset>
	<div>
		<?php echo $this->ID; ?>
		<?php echo $this->otherIDs; ?>
        <input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
