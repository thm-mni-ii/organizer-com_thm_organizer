<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        template for display of scheduled lessons on registered monitors
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$state = $this->state;
$viewPath = 'index.php?option=com_thm_organizer';
$isMenuItem = (!empty($state->menuID) AND is_numeric($state->menuID));
$action =  ($isMenuItem)? JUri::current() : JRoute::_($viewPath);
?>
<script type="application/javascript">
    var showFilterText = '<?php echo JText::_('COM_THM_ORGANIZER_SHOW_FILTERS'); ?>',
        hideFilterText = '<?php echo JText::_('COM_THM_ORGANIZER_HIDE_FILTERS'); ?>',
        selectedTypes = ["<?php echo implode('","', $this->state->types); ?>"],
        selectedRooms = ["<?php echo implode('","', $this->state->rooms); ?>"];
</script>
<div id="j-main-container">
    <div id="header-container" class="header-container">
    </div>
    <div id="form-container" class="form-container">
        <form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm">
            <div class="basic-filters">
                <?php echo $this->filters['template']; ?>
                <?php echo $this->filters['date']; ?>
                <button class="submit-button" onclick="showPostLoader();form.submit();">
                    <?php echo JText::_('COM_THM_ORGANIZER_ACTION_REFRESH'); ?>
                    <span class="icon-next"></span>
                </button>
            </div>
            <div class="advanced-filters">
                <div class="advanced-filters-toggle" onclick="toggleFilters();">
                    <span id="toggle-icon"class="icon-plus-2"></span>
                    <span id="toggle-text"><?php echo JText::_('COM_THM_ORGANIZER_SHOW_FILTERS'); ?></span>
                </div>
                <div id="additional-filters-container" class="advanced-filters-container toggle-closed">
                    <div class="filter-container">
                        <label for="jformtypes"><?php echo JText::_('COM_THM_ORGANIZER_ROOM_TYPES'); ?></label>
                        <?php echo $this->filters['types']; ?>
                    </div>
                    <div class="filter-container">
                        <label for="jformrooms"><?php echo JText::_('COM_THM_ORGANIZER_ROOMS'); ?></label>
                        <?php echo $this->filters['rooms']; ?>
                    </div>
                </div>
            </div>
            <input type="hidden" name="view" value="room_overview" />
        </form>
    </div>
    <div id="overview-container-container" class="overview-container-container">
        <div id="overview-container" class="overview-container">
<?php
if (!empty($this->model->data))
{
    $template = $this->state->get('template', 1);
    if ($template == DAY)
    {
        echo $this->loadTemplate('day');
    }
    if ($template == WEEK)
    {
        echo $this->loadTemplate('interval');
    }
}
else
{
    echo $this->loadTemplate('empty');
}
?>
        </div>
    </div>
</div>
