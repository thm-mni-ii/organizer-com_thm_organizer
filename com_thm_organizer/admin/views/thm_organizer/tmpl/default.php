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

defined('_JEXEC') or die;?>
<div id="thm_organizer_main" >
    <div id="thm_organizer_main_description">
        <?php echo JText::_("COM_THM_ORGANIZER_MAIN_DESC"); ?>
    </div>
    <div id="cpanel">
<?php foreach ($this->views as $view)
{
?>
        <div class="thm_organizer_main_submenu" >
            <div class="thm_organizer_main_linkdiv" >
                <div class="icon">
                    <?php echo $view['link_start']; ?>
                        <?php echo $view['image']; ?>
                        <?php echo $view['text']; ?>
                    <?php echo $view['link_end']; ?>
                </div>
            </div>
        </div>
<?php
}
?>
    </div>
</div>
