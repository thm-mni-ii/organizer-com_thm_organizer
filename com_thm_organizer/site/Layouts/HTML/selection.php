<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Factory;
use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

$user = Factory::getUser();
$view = Input::getCMD('view');
require_once 'language_selection.php';
echo OrganizerHelper::getApplication()->JComponentTitle; ?>
<div id="j-main-container">
    <form action="index.php?" method="post" name="adminForm" id="adminForm" target="_blank">
		<?php foreach ($this->sets as $set) : ?>
			<?php $this->renderSet($set); ?>
		<?php endforeach; ?>
        <div class="toolbar">
            <a id="action-btn" class="btn" onclick="handleSubmit();">
				<?php echo Languages::_('ORGANIZER_DOWNLOAD') ?>
                <span class="icon-file-pdf"></span>
            </a>
        </div>
        <input type="hidden" name="option" value="com_thm_organizer"/>
        <input type="hidden" name="view" value="<?php echo $view; ?>"/>
    </form>
</div>
