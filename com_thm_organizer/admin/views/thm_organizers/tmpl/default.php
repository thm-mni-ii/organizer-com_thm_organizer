<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        default template for the thm organizer main menu view
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;?>
<div id="thm_organizer_main" >
    <div id="thm_organizer_main_description">
        <?php echo JText::_( "COM_THM_ORGANIZER_MAIN_DESC" ); ?>
    </div>
    <div id="cpanel">
<?php foreach($this->views as $view) : ?>
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
<?php endforeach; ?>
    </div>
</div>
