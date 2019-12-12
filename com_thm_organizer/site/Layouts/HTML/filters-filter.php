<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
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
