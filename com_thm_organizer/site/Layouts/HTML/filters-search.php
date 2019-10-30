<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\Registry\Registry;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

// Receive overridable options
$options = empty($options) ? [] : $options;

if (is_array($options))
{
	$options = new Registry($options);
}

// Options
$filterButton = $options->get('filterButton', true);
$searchButton = $options->get('searchButton', true);

$filters = $this->filterForm->getGroup('filter');
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
<?php if ($filterButton) : ?>
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
