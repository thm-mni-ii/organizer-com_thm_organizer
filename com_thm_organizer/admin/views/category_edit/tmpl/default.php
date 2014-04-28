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
      enctype="multipart/form-data" method="post" name="adminForm" id="adminForm">
    <div id="thm_organizer_cat" class="width-60 fltlft thm_organizer_cat tab-content">
        <fieldset class="adminform">
            <legend><?php echo ($this->form->getValue('id'))? JText::_('JTOOLBAR_EDIT') : JText::_('JTOOLBAR_NEW'); ?></legend>
            <ul class="tab-pane [active] adminformlist" id="adminformlist">
                <li>
                    <?php echo $this->form->getLabel('title'); ?>
                    <?php echo $this->form->getInput('title'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('description'); ?>
                    <?php echo $this->form->getInput('description'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('contentCatID'); ?>
                    <?php echo $this->form->getInput('contentCatID'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('global'); ?>
                    <?php echo $this->form->getInput('global'); ?>
                </li>
                <li>
                    <?php echo $this->form->getLabel('reserves'); ?>
                    <?php echo $this->form->getInput('reserves'); ?>
                </li>
            </ul>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="" />
    <?php echo $this->form->getInput('id'); ?>
</form>
