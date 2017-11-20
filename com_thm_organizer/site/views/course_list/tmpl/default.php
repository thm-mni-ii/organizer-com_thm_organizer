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
$loginRoute    = JRoute::_('index.php?option=com_users&view=login', false, 2);
$registerRoute = JRoute::_('index.php?option=com_users&view=registration', false, 2);
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
<div class="course-list-view">
	<h1><?php echo $this->lang->_("COM_THM_ORGANIZER_PREP_COURSES_HEADER") ?></h1>

	<?php if (empty(JFactory::getUser()->id)): ?>
		<div class="tbox-yellow">
			<p><?php echo $this->lang->_("COM_THM_ORGANIZER_COURSE_LOGIN_WARNING"); ?></p>
			<ul>
				<li>
					<a onclick="<?php echo $casURL; ?>">
						<?php echo $this->lang->_('COM_THM_ORGANIZER_LOGIN_THM'); ?><span class="icon-apply"></span>
					</a>
				</li>
				<li>
					<a href="<?php echo $loginRoute; ?>">
						<?php echo $this->lang->_('COM_THM_ORGANIZER_LOGIN_LOCAL'); ?><span class="icon-apply"></span>
					</a>
				</li>
				<li>
					<a href="<?php echo $registerRoute; ?>">
						<?php echo $this->lang->_('COM_THM_ORGANIZER_REGISTER_LOCAL'); ?><span
								class="icon-user-plus"></span>
					</a>
				</li>
			</ul>
		</div>
	<?php else: ?>
		<div class="toolbar">
			<div class="tool-wrapper">
				<a class='btn btn-max' href='<?php echo $profileRoute; ?>'>
					<span class='icon-address'></span> <?php echo $this->lang->_("COM_THM_ORGANIZER_EDIT_USER_PROFILE"); ?>
				</a>
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