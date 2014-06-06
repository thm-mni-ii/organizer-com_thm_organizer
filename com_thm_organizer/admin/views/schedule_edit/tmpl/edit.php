<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description template for the editing of schedule commentary
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined("_JEXEC") or die;?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <div id="thm_organizer_se" class="width-60 fltlft thm_organizer_se ">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_THM_ORGANIZER_SCH_PROPERTIES'); ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('departmentname'); ?>
                    <?php echo $this->form->getInput('departmentname'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('semestername'); ?>
                    <?php echo $this->form->getInput('semestername'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('startdate'); ?>
                    <?php echo $this->form->getInput('startdate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('enddate'); ?>
                    <?php echo $this->form->getInput('enddate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('creationdate'); ?>
                    <?php echo $this->form->getInput('creationdate'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo $this->form->getInput('id'); ?>
</form>