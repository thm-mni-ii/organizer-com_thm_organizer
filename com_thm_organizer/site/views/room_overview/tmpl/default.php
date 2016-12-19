<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

$state      = $this->state;
$viewPath   = 'index.php?option=com_thm_organizer';
$isMenuItem = (!empty($state->menuID) AND is_numeric($state->menuID));
$action     = ($isMenuItem) ? JUri::current() : JRoute::_($viewPath);
?>
<script type="application/javascript">
    var showFilterText = '<?php echo JText::_('COM_THM_ORGANIZER_SHOW_FILTERS'); ?>',
        hideFilterText = '<?php echo JText::_('COM_THM_ORGANIZER_HIDE_FILTERS'); ?>',
        selectedTypes = ["<?php echo implode('","', $this->state->types); ?>"],
        selectedRooms = ["<?php echo implode('","', $this->state->rooms); ?>"];
</script>
<div id="j-main-container">
    <div id="header-container" class="header-container">
		<?php echo JText::_('COM_THM_ORGANIZER_ROOM_OVERVIEW_TITLE'); ?>
    </div>
    <div id="form-container" class="form-container">
        <form action="<?php echo $action; ?>" method="post" name="adminForm" id="adminForm">
            <div class="short-item-container">
                <div class="filter-item short-item">
                    <label for="jformtemplate"><?php echo JText::_('COM_THM_ORGANIZER_DISPLAY_RANGE'); ?></label>
					<?php echo $this->filters['template']; ?>
                </div>
                <div class="filter-item short-item">
                    <label for="date"><?php echo JText::_('COM_THM_ORGANIZER_DATE'); ?></label>
					<?php echo $this->filters['date']; ?>
                </div>
            </div>
            <div class="filter-item type-container">
                <label for="jformtypes"><?php echo JText::_('COM_THM_ORGANIZER_ROOM_TYPES'); ?></label>
				<?php echo $this->filters['types']; ?>
            </div>
            <div class="filter-item room-container">
                <label for="jformrooms"><?php echo JText::_('COM_THM_ORGANIZER_ROOMS'); ?></label>
				<?php echo $this->filters['rooms']; ?>
            </div>
            <button class="submit-button" onclick="showPostLoader();form.submit();">
				<?php echo JText::_('COM_THM_ORGANIZER_ACTION_REFRESH'); ?>
                <span class="icon-loop"></span>
            </button>
            <input type="hidden" name="view" value="room_overview"/>
        </form>
        <div class="clear"></div>
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
