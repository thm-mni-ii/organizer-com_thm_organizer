<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;
use Organizer\Helpers\Languages;

// Receive overridable options
$options = empty($options) ? [] : $options;

if (is_array($options))
{
	$options = new Registry($options);
}

$filters          = $this->filterForm->getGroup('filter');
$searchButton     = $options->get('searchButton', true);
$showFilterButton = empty($filters['filter_search']) ? (bool) count($filters) : count($filters) > 1;

?>

<?php if (!empty($filters['filter_search'])) : ?>
	<?php if ($searchButton) : ?>
        <label for="filter_search" class="element-invisible">
			<?php echo Languages::_('THM_ORGANIZER_SEARCH'); ?>
        </label>
        <div class="btn-wrapper input-append">
			<?php echo $filters['filter_search']->input; ?>
			<?php if ($filters['filter_search']->description) : ?>
				<?php JHtmlBootstrap::tooltip('#filter_search',
					array('title' => Languages::_($filters['filter_search']->description))); ?>
			<?php endif; ?>
            <button type="submit" class="btn hasTooltip"
                    title="<?php echo Languages::tooltip('THM_ORGANIZER_SEARCH'); ?>"
                    aria-label="<?php echo Languages::_('THM_ORGANIZER_SEARCH'); ?>">
                <span class="icon-search" aria-hidden="true"></span>
            </button>
        </div>
	<?php endif; ?>
<?php endif; ?>
<?php if ($showFilterButton) : ?>
    <div class="btn-wrapper hidden-phone">
        <button type="button" class="btn hasTooltip js-stools-btn-filter"
                title="<?php echo Languages::tooltip('THM_ORGANIZER_SEARCH_TOOLS_DESC'); ?>">
			<?php echo Languages::_('THM_ORGANIZER_SEARCH_TOOLS'); ?> <span class="caret"></span>
        </button>
    </div>
<?php endif; ?>
<?php if (!empty($filters['filter_search'])) : ?>
    <div class="btn-wrapper">
        <button type="button" class="btn hasTooltip js-stools-btn-clear"
                title="<?php echo Languages::tooltip('THM_ORGANIZER_RESET'); ?>">
			<?php echo Languages::_('THM_ORGANIZER_RESET'); ?>
        </button>
    </div>
<?php endif;
