<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        mapping template
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
?>
<fieldset class="adminform">
    <legend><?php echo JText::_('COM_THM_ORGANIZER_POM_PROPERTIES_MAPPING'); ?></legend>
    <ul class="adminformlist">
        <li>
            <?php echo $this->form->getLabel('programID'); ?>
            <?php echo $this->form->getInput('programID'); ?>
        </li>
        <li>
            <?php echo $this->form->getLabel('parentID'); ?>
            <?php echo $this->form->getInput('parentID'); ?>
        </li>
    </ul>
</fieldset>

