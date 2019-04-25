<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use THM_OrganizerHelperLanguages as Languages;

$shortTag = Languages::getShortTag();
$editURL  = "index.php?option=com_thm_organizer&lessonID={$this->course['id']}&languageTag=$shortTag";
$editURL  .= "&view=subject_edit&id={$this->course['subjectID']}";

if (!empty($this->menu)) {
    $menuText = $this->lang->_('THM_ORGANIZER_BACK');
}

?>
<div class="toolbar">
    <?php echo $this->languageLinks->render($this->languageParams); ?>
</div>
<div class="course-manager-view">
    <h1><?php echo "{$this->lang->_('THM_ORGANIZER_COURSE_MANAGEMENT')}: {$this->course['name']}"; ?></h1>
    <div class="course-descriptors">
        <div class="left"><?php echo $this->course['dateText'] ?></div>
    </div>
    <div class="right">
        <?php if (THM_OrganizerHelperSubjects::allowEdit($this->course['subjectID'])): ?>
            <a href="<?php echo \JRoute::_($editURL, false); ?>" class="btn btn-mini" type="button">
                <span class="icon-edit"></span>
                <?php echo $this->lang->_('THM_ORGANIZER_EDIT_COURSE_DESCRIPTION') ?>
            </a>
        <?php endif; ?>
        <?php if (!empty($this->menu)): ?>
            <a href="<?php echo \JRoute::_($this->menu['route'], false); ?>" class="btn btn-mini" type="button">
                <span class="icon-list"></span>
                <?php echo $menuText ?>
            </a>
        <?php endif; ?>
    </div>
    <div class="clear"></div>
    <hr>
    <?php echo $this->loadTemplate('course_settings'); ?>
    <hr>
    <?php echo $this->loadTemplate('participant_management'); ?>
    <?php echo $this->loadTemplate('circular'); ?>
</div>