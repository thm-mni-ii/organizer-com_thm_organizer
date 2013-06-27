<?php 
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default layout for thm organizer's scheduler view
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2013 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
$editEventURL = 'index.php?option=com_thm_organizer&view=event_edit&schedulerCall=true&eventID=';
$blankImageLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/ext_bak/resources/images/default/s.gif';
$addButtonLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/images/add.png';
$removeButtonLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/images/delete.png';
$mainPath = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/';
$curriculumLink = JRoute::_('index.php?option=com_thm_organizer&view=subject_details&tmpl=component&lang=de');
$ajaxHandler = JRoute::_('index.php?option=com_thm_organizer&view=ajaxhandler&format=raw');
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<script type="text/javascript" charset="utf-8">
var externLinks = [];
externLinks.eventLink = '<?php echo $editEventURL; ?>';
externLinks.blankImageLink = '<?php echo $blankImageLink; ?>';
externLinks.lectureAddButton = '<?php echo $addButtonLink; ?>';
externLinks.lectureRemoveButton = '<?php echo $removeButtonLink; ?>';
externLinks.mainPath = '<?php echo $mainPath; ?>';
externLinks.curriculumLink = '<?php echo $curriculumLink; ?>';
externLinks.ajaxHandler = '<?php echo $ajaxHandler; ?>';
</script>
<div id="MySchedMainW" class="MySchedMainW">
	<script type="text/javascript" charset="utf-8">
		<?php require_once "components/com_thm_organizer/views/scheduler/tmpl/mySched/language.js"; ?>
	</script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/coreextension.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/libs.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/authorize.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/mapping.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/models.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/readers.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/grid.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/main.js"></script>
	<script type="text/javascript" charset="utf-8"
		src="components/com_thm_organizer/views/scheduler/tmpl/mySched/plugins.js"></script>
	<script type="text/javascript" charset="utf-8">
<?php
if ($this->canWriteEvents === true)
{
			require_once "components/com_thm_organizer/views/scheduler/tmpl/mySched/advancedFunctions.js";
}
?>
		MySched.SessionId = '<?php echo $this->jsid; ?>';
		MySched.class_semester_id = '<?php echo $this->semesterID; ?>';
		MySched.class_semester_author = '<?php echo $this->semAuthor; ?>';
		MySched.class_semester_name = '<?php echo $this->semesterName; ?>';
		MySched.startup = '<?php echo $this->startup; ?>';
		MySched.searchModuleID = '<?php echo $this->searchModuleID; ?>';
		MySched.loadLessonsOnStartUp = new Boolean('<?php echo $this->loadLessonsOnStartUp; ?>');
		MySched.deltaDisplayDays = '<?php echo $this->deltaDisplayDays; ?>';
		MySched.departmentAndSemester = '<?php echo $this->departmentAndSemester; ?>';
<?php
if ($this->libraryFPDFIsInstalled)
{
?>
        MySched.libraryFPDFIsInstalled = true;
<?php
}
else
{
?>
        MySched.libraryFPDFIsInstalled = false;
<?php
}
if ($this->libraryiCalcreatorIsInstalled)
{
?>
        MySched.libraryiCalcreatorIsInstalled = true;
<?php
}
else
{
?>
        MySched.libraryiCalcreatorIsInstalled = false;
<?php
}
?>
		Ext.application({
		    name: 'Scheduler',    
		    launch: MySched.Base.init
		});
	</script>
</div>
<iframe id="MySchedexternURL" name="MySchedexternURL" src="#"
	scrolling="auto" align="top" frameborder="0"
	class="MySchedexternURLClass_DIS"> </iframe>
