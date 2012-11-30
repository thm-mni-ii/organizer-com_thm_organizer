<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitor edit default template
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
$boxTitle = ($this->form->getValue('monitorID'))?
        JText::_('COM_THM_ORGANIZER_MON_EDIT_TITLE') : JText::_('COM_THM_ORGANIZER_MON_NEW_TITLE');?>
<form action="index.php?option=com_thm_organizer" method="post" name="adminForm">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo $boxTitle; ?></legend>
            <ul class="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('roomID'); ?>
                    <?php echo $this->form->getInput('roomID'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('ip'); ?>
                    <?php echo $this->form->getInput('ip'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('display'); ?>
                    <?php echo $this->form->getInput('display'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('schedule_refresh'); ?>
                    <?php echo $this->form->getInput('schedule_refresh'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('content_refresh'); ?>
                    <?php echo $this->form->getInput('content_refresh'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('content'); ?>
                    <?php echo $this->form->getInput('content'); ?>
                </li>
            </ul>
        </fieldset>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>