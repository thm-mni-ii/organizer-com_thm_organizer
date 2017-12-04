<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

$casURL         = "document.location.href='index.php?option=com_externallogin&view=server&server=1';return false;";
$containerClass = $this->showRegistration ? ' uses-login' : '';

$color = ($this->status === null) ? 'blue' : ($this->status === 1 OR $this->isAdmin) ? 'green' : 'yellow';

if (!empty($this->menu))
{
	$menuText = $this->lang->_('COM_THM_ORGANIZER_BACK');
}
?>
<div class="toolbar">
	<div class="tool-wrapper language-switches">
		<?php
		foreach ($this->languageSwitches AS $switch)
		{
			echo $switch;
		}
		?>
	</div>
</div>
<div class="subject-list <?php echo $containerClass; ?>">
	<?php if (!empty($this->item->name)): ?>
		<h1 class="componentheading"><?php echo $this->item->name; ?></h1>
		<?php if ($this->showRegistration): ?>
			<div class="course-descriptors">
				<div class="left"><?php echo $this->dateText ?></div>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php if ($this->showRegistration): ?>
		<?php if (empty(JFactory::getUser()->id)): ?>
			<div class="tbox-yellow">
				<p><?php echo $this->lang->_("COM_THM_ORGANIZER_COURSE_LOGIN_WARNING"); ?></p>
				<?php echo JHtml::_('content.prepare', '{loadposition bound_login}'); ?>
				<div class="right">
					<?php if (!empty($this->menu)): ?>
						<a href="<?php echo JRoute::_($this->menu['route'], false); ?>" class="btn btn-mini"
						   type="button">
							<span class="icon-list"></span>
							<?php echo $menuText ?>
						</a>
					<?php endif; ?>
					<a class="btn" onclick="<?php echo $casURL; ?>">
						<span class="icon-apply"></span>
						<?php echo $this->lang->_('COM_THM_ORGANIZER_COURSE_ADMINISTRATOR_LOGIN'); ?>
					</a>
				</div>
				<div class="clear"></div>
			</div>
		<?php else: ?>
			<div class="tbox-<?php echo $color; ?> course-status">
				<div class="status-container left">
					<?php echo $this->lang->_("COM_THM_ORGANIZER_STATE") . ': ' . $this->statusDisplay; ?>
				</div>
				<div class="right">
					<?php echo $this->registrationButton; ?>
					<?php if (!empty($this->menu)): ?>
						<a href="<?php echo JRoute::_($this->menu['route'], false); ?>" class="btn btn-mini"
						   type="button">
							<span class="icon-list"></span>
							<?php echo $menuText ?>
						</a>
					<?php endif; ?>
					<?php echo JHtml::_('content.prepare', '{loadposition bound_login}'); ?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
	<?php endif;
	$this->displayAttribute('externalID', 'MODULE_CODE');
	$this->displayAttribute('short_name');
	$this->displayTeacherAttribute('executors', 'MODULE_COORDINATOR');
	$this->displayTeacherAttribute('teachers', 'teacher', $this->lang->_('COM_THM_ORGANIZER_TEACHERS_PLACEHOLDER'));
	$this->displayAttribute('description', 'SHORT_DESCRIPTION');
	$this->displayAttribute('objective', 'objectives');
	$this->displayAttribute('content', 'contents');
	$this->displayStarAttribute('expertise');
	$this->displayStarAttribute('method_competence');
	$this->displayStarAttribute('social_competence');
	$this->displayStarAttribute('self_competence');
	$this->displayAttribute('duration');

	if (!empty($this->item->instructionLanguage))
	{
		$value = ($this->item->instructionLanguage == 'D') ?
			$this->lang->_('COM_THM_ORGANIZER_GERMAN') : $this->lang->_('COM_THM_ORGANIZER_ENGLISH');
		$this->displayValue('INSTRUCTION_LANGUAGE', $value);
	}

	$this->displayAttribute('expenditureOutput', 'EXPENDITURE');
	$this->displayAttribute('sws');
	$this->displayAttribute('method');
	$this->displayAttribute('preliminary_work');

	if (!empty($this->item->proof))
	{
		$method = empty($this->item->pform) ? '' : ' ( ' . $this->item->pform . ' )';
		echo '
	<div class="subject-item">';
		echo '
		<div class="subject-label">' . $this->lang->_('COM_THM_ORGANIZER_PROOF') . '</div>
		';
		echo '
		<div class="subject-content">' . $this->item->proof . $method . '</div>
		';
		echo '
	</div>
	';
	}

	$this->displayAttribute('evaluation');
	$this->displayAttribute('frequency', 'AVAILABILITY');
	$this->displayAttribute('literature');
	$this->displayAttribute('aids', 'STUDY_AIDS');

	// Prerequisites which could not be completely resolved to specific modules
	$this->displayAttribute('prerequisites');
	$prerequisites = $this->getDependencies('pre');
	$this->displayValue('PREREQUISITE_MODULES', $prerequisites);
	$this->displayAttribute('recommended_prerequisites');
	$this->displayAttribute('prerequisiteOf', 'PREREQUISITE_FOR');
	$postrequisites = $this->getDependencies('post');
	$this->displayValue('POSTREQUISITE_MODULES', $postrequisites);

	$displayeCollab = JComponentHelper::getParams('com_thm_organizer')->get('displayeCollabLink');

	if (!empty($this->item->externalID) AND !empty($displayeCollab))
	{
		$ecollabLink = JComponentHelper::getParams('com_thm_organizer')->get('eCollabLink');
		$ecollabIcon = JUri::root() . 'media/com_thm_organizer/images/icon-32-moodle.png';
		echo '
	<div class="subject-item">';
		echo '
		<div class="subject-label">eCollaboration Link</div>
		';
		echo '
		<div class="subject-content">';
		echo '<a href="' . $ecollabLink . $this->item->externalID . '" target="_blank">';
		echo "<img class='eCollabImage' src='$ecollabIcon' title='eCollabLink'></a>";
		echo '
		</div>
	</div>
	';
	}

	?>
	<?php echo $this->disclaimer->render($this->disclaimerData); ?>
</div>
