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

$casURL        = "document.location.href='index.php?option=com_externallogin&view=server&server=1';return false;";
$loginRoute    = JRoute::_('index.php?option=com_users&view=login&tmpl=component', false, 1);
$registerRoute = JRoute::_('index.php?option=com_users&view=registration&tmpl=component', false, 1);
$profileRoute  = JRoute::_("index.php?option=com_thm_organizer&view=participant_edit&languageTag={$this->shortTag}");

?>
<div class="toolbar">
	<div class="tool-wrapper language-switches">
		<?php foreach ($this->languageSwitches AS $switch)
		{
			echo $switch;
		} ?>
	</div>
</div>
<div class="course-list-view uses-login">
	<h1><?php echo $this->lang->_("COM_THM_ORGANIZER_PREP_COURSES_HEADER") ?></h1>

	<?php if (empty(JFactory::getUser()->id)): ?>
		<div class="tbox-yellow">
			<p><?php echo $this->lang->_("COM_THM_ORGANIZER_COURSE_LOGIN_WARNING"); ?></p>
			<?php echo JHtml::_('content.prepare', '{loadposition bound_login}'); ?>
			<div class="clear"></div>
		</div>
	<?php else: ?>
		<div class="toolbar">
			<div class="tool-wrapper">
				<a class='btn btn-max' href='<?php echo $profileRoute; ?>'>
					<span class='icon-address'></span> <?php echo $this->lang->_("COM_THM_ORGANIZER_EDIT_USER_PROFILE"); ?>
				</a>
				<?php echo JHtml::_('content.prepare', '{loadposition bound_login}'); ?>
			</div>
		</div>
	<?php endif; ?>
	<div id="form-container" class="form-container">
		<form action="<?php echo JUri::current(); ?>"
			  method="post" name="adminForm" id="adminForm">
			<?php if ($this->showFilters): ?>
				<div class="filter-item short-item">
					<?php echo $this->filters['filter_subject']; ?>
				</div>
				<div class="filter-item short-item">
					<?php echo $this->filters['filter_status']; ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="languageTag" value="<?php echo $this->shortTag; ?>"/>
		</form>
	</div>
	<table class="table table-striped">
		<thead>
		<tr>
			<th><?php echo $this->lang->_("COM_THM_ORGANIZER_NAME"); ?></th>
			<th><?php echo $this->lang->_("COM_THM_ORGANIZER_DATES"); ?></th>
			<th class='course-state'><?php echo $this->lang->_("COM_THM_ORGANIZER_COURSE_STATE"); ?></th>
			<th class='user-state'><?php echo $this->lang->_("COM_THM_ORGANIZER_REGISTRATION_STATE"); ?></th>
			<th class='registration'></th>
		</tr>
		</thead>
		<tbody>
		<?php echo $this->loadTemplate('list'); ?>
		</tbody>
	</table>
</div>