<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view pool add template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<form action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=pool_edit&id=' . (int) $this->item->id); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal">
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_DE'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('name_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('name_de'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('short_name_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('short_name_de'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('abbreviation_de'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('abbreviation_de'); ?>
            </div>
        </div>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_PROPERTIES_EN'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('name_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('name_en'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('short_name_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('short_name_en'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('abbreviation_en'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('abbreviation_en'); ?>
            </div>
        </div>
    </fieldset>
    <fieldset class="adminform">
        <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES'); ?></legend>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('lsfID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('lsfID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('hisID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('hisID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('externalID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('externalID'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('minCrP'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('minCrP'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('maxCrP'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('maxCrP'); ?>
            </div>
        </div>

        <div class="control-group">
            <div class="control-label">
                <?php echo $this->form->getLabel('fieldID'); ?>
            </div>
            <div class="controls">
                <?php echo $this->form->getInput('fieldID'); ?>
            </div>
        </div>
    </fieldset>
    <div>
        <?php echo $this->form->getInput('id'); ?>
        <?php echo JHtml::_('form.token'); ?>
        <input type="hidden" name="task" value="" />
    </div>
</form>
