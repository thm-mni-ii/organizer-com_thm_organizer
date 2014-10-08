<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @description default template for the thm organizer main menu view
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
$logoURL = 'media/com_thm_organizer/images/thm_organizer.png';
echo '<div id="j-sidebar-container" class="span2">' . $this->sidebar . '</div>';
?>
<div id="j-main-container" class="span10">
    <?php echo JHtml::_('image', $logoURL, JText::_('COM_THM_ORGANIZER'), array( 'class' => 'thm_organizer_main_image')); ?>
    <div id="thm_organizer_main_description" class='thm_organizer_main_description'>
        <?php echo JText::_("COM_THM_ORGANIZER_DESCRIPTION"); ?>
    </div>
</div>
