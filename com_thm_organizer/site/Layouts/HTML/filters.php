<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

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
    // Checks if a selector (e.g. client_id) exists.
    if ($selectorField = $this->filterForm->getField($selectorFieldName)) {
        $showSelector = $selectorField->getAttribute('filtermode', '') == 'selector' ? true : $showSelector;

        // Checks if a selector should be shown in the current layout.
        if (isset($this->layout)) {
            $showSelector = $selectorField->getAttribute('layout', 'default') != $this->layout ? false : $showSelector;
        }

        // Unset the selector field from active filters group.
        unset($this->activeFilters[$selectorFieldName]);
    }

    // Checks if the filters button should exist.
    $filters          = $this->filterForm->getGroup('filter');
    $showFilterButton = isset($filters['filter_search']) && count($filters) === 1 ? false : true;

    // Checks if it should show the be hidden.
    $hideActiveFilters = empty($this->activeFilters);

    // Check if the no results message should appear.
    if (isset($this->total) && (int)$this->total === 0) {
        $noResults = $this->filterForm->getFieldAttribute('search', 'noresults', '', 'filter');
        if (!empty($noResults)) {
            $noResultsText = Languages::_($noResults);
        }
    }
}

// Set some basic options.
$customOptions = array(
    'filtersHidden'       => isset($data['options']['filtersHidden']) && $data['options']['filtersHidden'] ? $data['options']['filtersHidden'] : $hideActiveFilters,
    'filterButton'        => isset($data['options']['filterButton']) && $data['options']['filterButton'] ? $data['options']['filterButton'] : $showFilterButton,
    'defaultLimit'        => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : JFactory::getApplication()->get('list_limit',
        20),
    'searchFieldSelector' => '#filter_search',
    'selectorFieldName'   => $selectorFieldName,
    'showSelector'        => $showSelector,
    'orderFieldSelector'  => '#list_fullordering',
    'showNoResults'       => !empty($noResultsText) ? true : false,
    'noResultsText'       => !empty($noResultsText) ? $noResultsText : '',
    'formSelector'        => !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm',
);

// Merge custom options in the options array.
$data['options'] = array_merge($customOptions, $data['options']);

// Add class to hide the active filters if needed.
$filtersActiveClass = $hideActiveFilters ? '' : ' js-stools-container-filters-visible';

// Load search tools
HTML::_('searchtools.form', $data['options']['formSelector'], $data['options']);
?>
<div class="js-stools clearfix">
    <div class="clearfix">
        <?php if ($data['options']['showSelector']) : ?>
            <div class="js-stools-container-selector">
                <?php echo JLayoutHelper::render('joomla.searchtools.default.selector', $data); ?>
            </div>
        <?php endif; ?>
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
