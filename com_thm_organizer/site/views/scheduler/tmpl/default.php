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
$editEventURL = 'index.php?option=com_thm_organizer&view=event_edit&eventID=';
$blankImageLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/ext_bak/resources/images/default/s.gif';
$addButtonLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/images/add.png';
$removeButtonLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/images/delete.png';
$mainPath = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/';
$curriculumLink = JURI::root(true) . '/index.php?option=com_thm_organizer&view=subject_details&languageTag=de&Itemid=' . JRequest::getInt("Itemid");
$ajaxHandler = 'index.php?option=com_thm_organizer&view=ajaxhandler&format=raw';
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
        MySched.loadLessonsOnStartUp = <?php echo $this->loadLessonsOnStartUp? 'true' : 'false'; ?>;
        MySched.deltaDisplayDays = <?php echo $this->deltaDisplayDays; ?>;
        MySched.departmentAndSemester = '<?php echo $this->departmentAndSemester; ?>';
        MySched.requestTeacherIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestTeacherIDs)); ?>'));
        MySched.requestRoomIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestRoomIDs)); ?>'));
        MySched.requestPoolIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestPoolIDs)); ?>'));
        MySched.requestSubjectIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestSubjectIDs)); ?>'));
        MySched.joomlaItemid = '<?php echo $this->joomlaItemid; ?>';
        MySched.languageTag = '<?php echo $this->languageTag; ?>';
        MySched.FPDFInstalled = <?php echo $this->FPDFInstalled? 'true' : 'false'; ?>;
        MySched.iCalcreatorInstalled = <?php echo $this->iCalcreatorInstalled? 'true' : 'false'; ?>;
        MySched.PHPExcelInstalled = <?php echo $this->PHPExcelInstalled? 'true' : 'false'; ?>;
        MySched.schedulerFromMenu = <?php echo $this->schedulerFromMenu? 'true' : 'false'; ?>;
        MySched.displayModuleNumber = <?php echo $this->displayModuleNumber? 'true' : 'false'; ?>;
        Ext.application({
            name: 'Scheduler',
            launch: MySched.Base.init
        });
    </script>
</div>
