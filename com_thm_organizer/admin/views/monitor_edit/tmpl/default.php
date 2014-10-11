<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description monitor edit default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
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
<form action="index.php?option=com_thm_organizer"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <div class="width-60 fltlft">
        <fieldset class="adminform">
            <legend><?php echo $boxTitle; ?></legend>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('roomID'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('roomID'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('ip'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('ip'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('useDefaults'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('useDefaults'); ?>
                </div>
            </div>

            <fieldset id='specificSettings' class="adminform" style="display: <?php echo $show; ?>;">
                <legend><?php echo JText::_('COM_THM_ORGANIZER_MON_SPECIFIC_SETTINGS'); ?></legend>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('display'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('display'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('schedule_refresh'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('schedule_refresh'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('content_refresh'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('content_refresh'); ?>
                    </div>
                </div>

                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('content'); ?>
                    </div>
                    <div class="controls">
                        <?php echo $this->form->getInput('content'); ?>
                    </div>
                </div>
            </fieldset>
        </fieldset>
        <div>
            <?php echo $this->form->getInput('id'); ?>
            <?php echo JHtml::_('form.token'); ?>
            <input type="hidden" name="task" value="" />
        </div>
    </div>
</form>