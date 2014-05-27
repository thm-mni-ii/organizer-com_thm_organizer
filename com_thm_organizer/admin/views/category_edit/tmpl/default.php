<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @descriptiom category edit view default template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <div id="thm_organizer_cat" class="width-60 fltlft thm_organizer_cat tab-content">
        <fieldset class="adminform">
            <legend><?php echo ($this->form->getValue('id'))? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW'); ?></legend>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('title'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('title'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('description'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('description'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('contentCatID'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('contentCatID'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('global'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('global'); ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">
                    <?php echo $this->form->getLabel('reserves'); ?>
                </div>
                <div class="controls">
                    <?php echo $this->form->getInput('reserves'); ?>
                </div>
            </div>
        </fieldset>
    </div>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="task" value="" />
    </div>
</form>
