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
$logoURL = 'administrator/components/com_thm_organizer/assets/images/THM-Organizer-Logo.png';
?>
<div id="thm_organizer_main" >
    <fieldset class="com_thm_organizer_fieldset">
        <legend>
<?php
    echo JHTML::_('image', $logoURL, JText::_('COM_THM_ORGANIZER'), array( 'class' => 'thm_organizer_main_image'));
?>     
        </legend>
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
                        <a href='<?php echo $view['url']; ?>'
                           class='hasTip' title='<?php echo $view['tooltip']; ?>' >
<?php
    echo JHTML::_('image', $view['image'],$view['title'], array( 'class' => 'thm_organizer_main_image'));
?>
                            <span><?php echo $view['title']; ?></span>
                        </a>
                    </div>
                </div>
            </div>
<?php
}
?>
        </div>
    </fieldset>
</div>
