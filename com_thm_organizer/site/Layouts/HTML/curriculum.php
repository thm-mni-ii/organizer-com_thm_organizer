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
use Organizer\Helpers\Languages;
use Organizer\Helpers\OrganizerHelper;

$resourceID = Input::getID();
$view       = Input::getView();

$action = '?';
$action .= OrganizerHelper::dynamic() ? "option=com_thm_organizer&view=$view&id=$resourceID" : ''; ?>
<form id="adminForm" name="adminForm" method="post" action="<?php echo $action; ?>"
      class="form-horizontal form-validate">
    <?php require_once 'language_selection.php'; ?>
</form>
<?php echo OrganizerHelper::getApplication()->JComponentTitle; ?>
<div class="resource-item">
    <div class="curriculum">
        <?php foreach ($this->item['children'] as $pool) : ?>
            <?php $this->renderPanel($pool); ?>
        <?php endforeach; ?>
        <?php echo $this->disclaimer; ?>
    </div>
    <div class="legend">
        <div class="panel-head">
            <div class="panel-title"><?php echo Languages::_('THM_ORGANIZER_LEGEND'); ?></div>
        </div>
        <?php foreach ($this->fields as $hex => $field) : ?>
            <div class="legend-item">
                <div class="item-color" style="background-color: <?php echo $hex; ?>;"></div>
                <div class="item-title"><?php echo $field; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>