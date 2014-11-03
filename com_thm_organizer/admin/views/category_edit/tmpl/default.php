<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @descriptiom category edit view default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
    {
        if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form')))
        {
            Joomla.submitform(task, document.getElementById('item-form'));
        }
    }
</script>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="item-form"
      class="form-horizontal">
    <div class="form-horizontal">
        <div class="span3">
            <fieldset class="form-vertical">
 <?php
                echo $this->form->renderField('title');
                echo $this->form->renderField('contentCatID');
                echo $this->form->renderField('global');
                echo $this->form->renderField('reserves');
 ?>
            </fieldset>
        </div>
        <div class="span9">
            <fieldset class="form-vertical">
                <?php echo $this->form->renderField('description'); ?>
            </fieldset>
        </div>
    </div>
    <?php echo $this->form->getInput('id'); ?>
    <?php echo JHtml::_('form.token'); ?>
    <input type="hidden" name="task" value="" />
</form>
