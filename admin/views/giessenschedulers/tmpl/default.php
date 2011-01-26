<?php
/** *
 * PHP version 5
 *
 * @category Joomla Programming Weeks SS2008: FH Giessen-Friedberg
 * @package  com_thm_organizer
 * @author   James Antrim <james.antrim@yahoo.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @link     http://www.mni.fh-giessen.de
 **/
// no direct access
defined('_JEXEC') or die('Restricted access');

global $option;
$document =& JFactory::getDocument();
$document->addStyleSheet("templates/giessenstyle/css/extension.css");
?>

<div id="thm_organizer_main" >
    <div id="thm_organizer_main_logo">
        <?php echo $this->logo; ?>
    </div>
    <div id="thm_organizer_main_title">
        Giessen Scheduler - Main Menu
    </div>
    <table id="thm_organizer_main_submenus" class="adminlist">
        <tr>
            <td>
                <div id="cpanel">
<?php

$link = 'index.php?option='.$option.'&amp;view=category_list';
thm_organizersViewthm_organizers::quickiconButton( $link, 'category.png', JText::_( 'Category Manager' ) );

$link = 'index.php?option='.$option.'&amp;view=room_ip_list';
thm_organizersViewthm_organizers::quickiconButton( $link, 'monitor-settings.png', JText::_( 'Monitor Manager' ) );

$link = 'index.php?option='.$option.'&amp;view=semester_list';
thm_organizersViewthm_organizers::quickiconButton( $link, 'semesters.png', JText::_( 'Semester Manager' ) );

$link = 'index.php?option='.$option.'&amp;view=scheduler_application_settings';
thm_organizersViewthm_organizers::quickiconButton( $link, 'scheduler-settings.jpg', JText::_( 'Scheduler Application Settings' ) );

?>
                </div>
            </td>
        </tr>
    </table>
</div>
