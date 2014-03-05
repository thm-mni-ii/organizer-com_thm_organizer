<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description monitor edit default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
$boxTitle = ($this->form->getValue('monitorID'))?
        JText::_('COM_THM_ORGANIZER_MON_EDIT_TITLE') : JText::_('COM_THM_ORGANIZER_MON_NEW_TITLE');
$show = $this->form->getValue('useDefaults')? 'none' : 'block';
?>
<script type="text/javascript">
    window.addEvent('domready', function()
    {
        var radio1 = document.getElementById('jform_useDefaults0'), radio2 = document.getElementById('jform_useDefaults1');
        radio1.onclick = function(){document.getElementById('specificSettings').style.display = 'none'};
        radio2.onclick = function(){document.getElementById('specificSettings').style.display = 'block'};
    });
</script>
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
                    <?php echo $this->form->getLabel('useDefaults'); ?>
                    <?php echo $this->form->getInput('useDefaults'); ?>
                </li>
            </ul>
            <fieldset id='specificSettings' class="adminform" style="display: <?php echo $show; ?>;">
                <legend><?php echo JText::_('COM_THM_ORGANIZER_MON_SPECIFIC_SETTINGS'); ?></legend>
                <ul>
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
        </fieldset>
        <?php echo $this->form->getInput('id'); ?>
        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>