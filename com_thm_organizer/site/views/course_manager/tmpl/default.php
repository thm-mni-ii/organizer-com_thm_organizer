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
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/templates/edit_basic.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$linkPrefix = "index.php?option=com_thm_organizer&view=course_list&format=pdf&type=%s&lessonID=%s&languageTag=%s";
$shortTag = JFactory::getApplication()->input->get('languageTag', 'de');
$participantListRoute = JRoute::_(sprintf($linkPrefix, 0, $this->course["id"], $shortTag), false, 2);
$departmentListRoute = JRoute::_(sprintf($linkPrefix, 1, $this->course["id"], $shortTag), false, 2);
$badgesRoute = JRoute::_(sprintf($linkPrefix, 2, $this->course["id"], $shortTag), false, 2);
?>

<div>

	<div class="toolbar">
		<div class="tool-wrapper language-switches">
			<?php foreach ($this->languageSwitches AS $switch)
			{
				echo $switch;
			}
			?>
		</div>
	</div>

	<h1> <?php echo "{$this->lang->_('COM_THM_ORGANIZER_MANAGE')}: {$this->course["name"]}"; ?> </h1>

	<h3> <?php echo $this->dateText ?> </h3>

	<h3 style="text-align: right;">
		<?php echo $this->lang->_("COM_THM_ORGANIZER_CAPACITY") . ": " . sizeof($this->curCap) . "\\" . $this->capacity; ?>
	</h3>

	<a href="<?php echo $participantListRoute; ?>"
	   class="btn btn-mini" type="button"><span class="icon-file-pdf"></span>
		<?php echo $this->lang->_("COM_THM_ORGANIZER_EXPORT_PARTICIPANTS") ?></a>
	<a href="<?php echo $departmentListRoute; ?>"
	   class="btn btn-mini" type="button"><span class="icon-file-pdf"></span>
		<?php echo $this->lang->_("COM_THM_ORGANIZER_EXPORT_DEPARTMENTS") ?></a>
	<a href="<?php echo $badgesRoute; ?>"
	   class="btn btn-mini" type="button"><span class="icon-file-pdf"></span>
		<?php echo $this->lang->_("COM_THM_ORGANIZER_EXPORT_BADGES") ?></a>

	<?php
	$editAuth = THM_OrganizerHelperComponent::allowResourceManage('subject', $this->course["subjectID"]);
	if ($editAuth): ?>
		<a href="<?php echo JRoute::_(
			'index.php?option=com_thm_organizer&view=prep_course_edit' .
			'&id=' . $this->course["subjectID"] .
			'&lessonID=' . $this->course["id"], false, 2
		); ?>"
		   class="btn btn-mini" type="button"> <span class="icon-edit"></span>
			<?php echo $this->lang->_("JACTION_EDIT") ?>
		</a>

	<?php endif;
	if ($this->isAdmin): ?>
		<script>
            function deleteStudents(s) {
                if (window.confirm(s))
                {
                    window.location.href = "<?php echo JRoute::_(
						'index.php?option=com_thm_organizer&task=participant.clear' .
						'&lessonID=' . $this->course["id"], false, 2
					); ?>";
                }
            }
		</script>

		<a onclick="deleteStudents('<?php echo $this->lang->_("COM_THM_ORGANIZER_ACTION_CLEAR_COURSE_VERIFY")?>')"
		   class="btn btn-mini" type="button" > <span class="icon-warning"></span>
			<?php echo $this->lang->_("COM_THM_ORGANIZER_ACTION_CLEAR_COURSE") ?>
		</a>

	<?php endif;

	echo $this->loadTemplate('modal');

	echo $this->loadTemplate('form');

	?>

</div>