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

defined('JPATH_BASE') or die;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

$noResultsText     = '';
$hideActiveFilters = false;
$showFilterButton  = false;
$showSelector      = false;
$selectorFieldName = isset($data['options']['selectorFieldName']) ? $data['options']['selectorFieldName'] : 'client_id';

// If a filter form exists.
if (isset($this->filterForm) && !empty($this->filterForm)) {
    // Checks if the filters button should exist.
    $filters          = $this->filterForm->getGroup('filter');
    $showFilterButton = isset($filters['filter_search']) && count($filters) === 1 ? false : true;

    // Checks if it should show the be hidden.
    $hideActiveFilters = empty($this->activeFilters);
}

// Set some basic options.
$customOptions = [
    'filtersHidden'       => isset($data['options']['filtersHidden']) && $data['options']['filtersHidden'] ? $data['options']['filtersHidden'] : $hideActiveFilters,
    'filterButton'        => isset($data['options']['filterButton']) && $data['options']['filterButton'] ? $data['options']['filterButton'] : $showFilterButton,
    'defaultLimit'        => isset($data['options']['defaultLimit']) ?
        $data['options']['defaultLimit'] : OrganizerHelper::getApplication()->get('list_limit', 50),
    'searchFieldSelector' => '#filter_search',
    'selectorFieldName'   => $selectorFieldName,
    'orderFieldSelector'  => '#list_fullordering',
    'formSelector'        => '#adminForm'
];

// Merge custom options in the options array.
$data['options'] = array_merge($customOptions, $data['options']);

// Add class to hide the active filters if needed.
$filtersActiveClass = $hideActiveFilters ? '' : ' js-stools-container-filters-visible';

// Load search tools
HTML::_('searchtools.form', $data['options']['formSelector'], $data['options']);
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
    <?php if ($data['options']['filterButton']) : ?>
        <div class="js-stools-container-filters hidden-phone clearfix<?php echo $filtersActiveClass; ?>">
            <?php require_once 'filters-filter.php'; ?>
        </div>
    <?php endif; ?>
</div>
