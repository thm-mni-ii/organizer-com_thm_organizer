<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

$logoURL = 'components/com_thm_organizer/images/thm_organizer.png';
$logo    = HTML::_('image', $logoURL, Languages::_('THM_ORGANIZER'), ['class' => 'thm_organizer_main_image']);
$query   = Uri::getInstance()->getQuery();
?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->submenu; ?>
</div>
<div id="j-main-container" class="span10">
    <form action="<?php echo Uri::base() . "?$query"; ?>" id="adminForm" method="post"
          name="adminForm">
        <div class="organizer-header">
            <div class="organizer-logo">
				<?php echo $logo; ?>
            </div>
        </div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="option" value="com_thm_organizer"/>
        <input type="hidden" name="view" value="organizer"/>
    </form>
</div>
