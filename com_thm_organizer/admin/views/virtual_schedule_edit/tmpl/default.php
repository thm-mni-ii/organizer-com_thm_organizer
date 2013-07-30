<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.view
 * @name        virtual schedule edit default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined("_JEXEC") or die;?>
<form action="<?php echo JRoute::_("index.php?option=com_thm_organizer"); ?>"
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div id="thm_organizer_se" class="width-60 fltlft thm_organizer_se">
        <fieldset class="adminform">
            <legend><?php echo $this->legend; ?></legend>
            <ul class="adminformtable">
                <li id="vse_name">
                    <?php echo $this->form->getLabel('name'); ?>
                    <?php echo $this->form->getInput('name'); ?>
                </li>
                <li id="vse_type">
                    <?php echo $this->form->getLabel('type'); ?>
                    <?php echo $this->form->getInput('type'); ?>
                </li>
                <li id="vse_semester">
                    <?php echo $this->form->getLabel('semester'); ?>
                    <?php echo $this->form->getInput('semester'); ?>
                </li>
                <li id="vse_responsible">
                    <?php echo $this->form->getLabel('responsible'); ?>
                    <?php echo $this->form->getInput('responsible'); ?>
                </li>
                <li id="vse_teacherDepartment">
                    <?php echo $this->form->getLabel('TeacherDepartment'); ?>
                    <?php echo $this->form->getInput('TeacherDepartment'); ?>
                </li>
                <li id="vse_roomDepartment">
                    <?php echo $this->form->getLabel('RoomDepartment'); ?>
                    <?php echo $this->form->getInput('RoomDepartment'); ?>
                </li>
                <li id="vse_classDepartment">
                    <?php echo $this->form->getLabel('ClassDepartment'); ?>
                    <?php echo $this->form->getInput('ClassDepartment'); ?>
                </li>
                <li id="vse_teachers">
                    <?php echo $this->form->getLabel('Teachers'); ?>
                    <?php echo $this->form->getInput('Teachers'); ?>
                </li>
                <li id="vse_rooms">
                    <?php echo $this->form->getLabel('Rooms'); ?>
                    <?php echo $this->form->getInput('Rooms'); ?>
                </li>
                <li id="vse_classes">
                    <?php echo $this->form->getLabel('Classes'); ?>
                    <?php echo $this->form->getInput('Classes'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo $this->form->getInput('vid'); ?>
    <?php echo $this->form->getInput('id'); ?>
</form>