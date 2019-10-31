<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Organizer\Helpers\HTML;

defined('JPATH_BASE') or die;

// Load the form filters
$filters = $this->filterForm->getGroup('filter');
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php $showON = JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group); ?>
		<?php if ($fieldName !== 'filter_search') : ?>
			<?php $dataShowOn = ''; ?>
			<?php if ($field->showon) : ?>
				<?php HTML::_('bootstrap.framework'); ?>
				<?php HTML::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true)); ?>
				<?php $dataShowOn = " data-showon='" . json_encode($showON, JSON_UNESCAPED_UNICODE) . "'"; ?>
			<?php endif; ?>
            <div class="js-stools-field-filter"<?php echo $dataShowOn; ?>>
				<?php echo $field->input; ?>
            </div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
