<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        default template thm_organizer monitor editor view
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
$boxTitle = ($this->form->getValue('monitorID'))?
        JText::_('COM_THM_ORGANIZER_MON_EDIT_TITLE') : JText::_('COM_THM_ORGANIZER_MON_NEW_TITLE');?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer'); ?>" method="post" name="adminForm">
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
                    <?php echo $this->behaviour; ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('interval'); ?>
                    <?php echo $this->form->getInput('interval'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('content'); ?>
                    <?php echo $this->form->getInput('content'); ?>
                </li>
            </ul>
        </fieldset>
        <input type="hidden" name="monitorID" value="<?php echo $this->form->getValue('monitorID'); ?>" />
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>