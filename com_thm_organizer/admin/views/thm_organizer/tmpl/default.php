<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use THM_OrganizerHelperHTML as HTML;

$logoURL = 'components/com_thm_organizer/images/thm_organizer.png';
$logo    = HTML::_('image', $logoURL, Languages::_('THM_ORGANIZER'), ['class' => 'thm_organizer_main_image']);
?>
<div id="j-sidebar-container" class="span2">
    <?php echo OrganizerHelper::adminSideBar($this->getName()); ?>
</div>
<div id="j-main-container" class="span10">
    <div class="organizer-header">
        <div class="organizer-logo">
            <?php echo $logo; ?>
        </div>
    </div>
</div>
