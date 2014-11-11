<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        default layout for thm organizer's scheduler view
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
$editEventURL = 'index.php?option=com_thm_organizer&view=event_edit&eventID=';
$blankImageLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/ext_bak/resources/images/default/s.gif';
$addButtonLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/images/add.png';
$removeButtonLink = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/images/delete.png';
$mainPath = JURI::root(true) . '/components/com_thm_organizer/views/scheduler/tmpl/';
$curriculumLink = JURI::root(true);
$curriculumLink .= "/index.php?option=com_thm_organizer&view=subject_details&languageTag={$this->config['languageTag']}&Itemid=";
$curriculumLink .= JFactory::getApplication()->input->getInt('Itemid', 0);
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
if ($this->config['canWrite'] === true)
{
            require_once "components/com_thm_organizer/views/scheduler/tmpl/mySched/advancedFunctions.js";
}
?>
        MySched.SessionId = '<?php echo $this->config['sessionID']; ?>';
        MySched.class_semester_id = '<?php echo $this->semesterID; ?>';
        MySched.class_semester_author = '';
        MySched.class_semester_name = '<?php echo $this->config['name']; ?>';
        MySched.startup = '<?php echo $this->startup; ?>';
        MySched.searchModuleID = '<?php echo $this->searchModuleID; ?>';
        MySched.loadLessonsOnStartUp = <?php echo $this->loadLessonsOnStartUp? 'true' : 'false'; ?>;
        MySched.deltaDisplayDays = <?php echo $this->config['deltaDisplayDays']; ?>;
        MySched.departmentAndSemester = '<?php echo $this->config['name']; ?>';
        MySched.requestTeacherIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestResources['teachers'])); ?>'));
        MySched.requestRoomIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestResources['rooms'])); ?>'));
        MySched.requestPoolIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestResources['pools'])); ?>'));
        MySched.requestSubjectIDs =
            Ext.decode(decodeURIComponent('<?php echo rawurlencode(json_encode($this->requestResources['subjects'])); ?>'));
        MySched.joomlaItemid = '<?php echo $this->joomlaItemid; ?>';
        MySched.languageTag = '<?php echo $this->config['languageTag']; ?>';
        MySched.FPDFInstalled = <?php echo $this->libraries['fpdf']? 'true' : 'false'; ?>;
        MySched.iCalcreatorInstalled = <?php echo $this->libraries['iCalcreator']? 'true' : 'false'; ?>;
        MySched.PHPExcelInstalled = <?php echo $this->libraries['PHPExcel']? 'true' : 'false'; ?>;
        MySched.schedulerFromMenu = <?php echo $this->config['isMenu']? 'true' : 'false'; ?>;
        MySched.displayModuleNumber = <?php echo $this->displayModuleNumber? 'true' : 'false'; ?>;
        Ext.application({
            name: 'Scheduler',
            launch: MySched.Base.init
        });
    </script>
</div>
