<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        template for the uploading of new schedules
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined("_JEXEC") or die;?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm">
    <div id="thm_organizer_se" class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo $this->legend; ?></legend>
            <ul class="adminformtable">
                <li>
                    <label class="thm_organizer_label" for="file">
                        <?php echo JText::_("COM_THM_ORGANIZER_SCH_UPLOAD_TITLE"); ?>
                    </label>
                    <input name="file" type="file" />
                </li>
                <li>
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="scheduleID" value="<?php echo $this->form->getValue('id'); ?>" />
</form>