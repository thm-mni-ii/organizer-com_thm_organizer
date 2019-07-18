<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Organizer\Helpers\Input;
use Organizer\Helpers\OrganizerHelper;

$resourceID = Input::getID();
$view       = Input::getView();

$action = "?view=$view&id=$resourceID";

echo OrganizerHelper::getApplication()->JComponentTitle;
?>
<div id="j-main-container" class="span10">
    <form id="adminForm" name="adminForm" method="post" action="<?php echo $action; ?>"
          class="form-horizontal form-validate">
        <?php require_once 'language_selection.php'; ?>
    </form>
    <?php foreach ($this->item as $key => $data) : ?>
        <?php if (is_array($data)) : ?>
            <?php $this->renderAttribute($key, $data); ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php echo $this->disclaimer; ?>
</div>
