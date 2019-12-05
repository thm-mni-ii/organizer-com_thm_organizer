<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

// Receive overridable options

$hideActiveFilters = false;
$noResultsText     = '';
$options           = [];
$showFilterButton  = false;
$showSelector      = false;

// If a filter form exists.
if (isset($this->filterForm) && !empty($this->filterForm))
{
	// Checks if the filters button should exist.
	$filters          = $this->filterForm->getGroup('filter');
	$showFilterButton = empty($filters['filter_search']) ? (bool) count($filters) : count($filters) > 1;

	// Checks if it should show the be hidden.
	$hideActiveFilters = empty($this->activeFilters);
}

// Set some basic options.
$options['filtersHidden']       = $hideActiveFilters;
$options['filterButton']        = $showFilterButton;
$options['defaultLimit']        = isset($options['defaultLimit']) ?
	$options['defaultLimit'] : $this->state->get('list.limit');
$options['searchFieldSelector'] = '#filter_search';
$options['orderFieldSelector']  = '#list_fullordering';

// Add class to hide the active filters if needed.
$filtersActiveClass = $hideActiveFilters ? '' : ' js-stools-container-filters-visible';

// Load search tools
HTML::_('searchtools.form', '#adminForm', $options);
?>
<div class="js-stools clearfix">
    <div class="clearfix">
        <div class="js-stools-container-bar">
			<?php require_once 'filters-search.php'; ?>
        </div>
        <div class="js-stools-container-list hidden-phone hidden-tablet">
			<?php require_once 'filters-list.php'; ?>
        </div>
    </div>
    <!-- Filters div -->
	<?php if ($options['filterButton']) : ?>
        <div class="js-stools-container-filters hidden-phone clearfix<?php echo $filtersActiveClass; ?>">
			<?php require_once 'filters-filter.php'; ?>
        </div>
	<?php endif; ?>
</div>
