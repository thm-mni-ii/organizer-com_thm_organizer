<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('JPATH_BASE') or die;

// Load the form list fields
$list = $this->filterForm->getGroup('list');
?>
<?php if ($list) : ?>
    <div class="ordering-select hidden-phone">
		<?php foreach ($list as $fieldName => $field) : ?>
            <div class="js-stools-field-list">
				<?php echo $field->input; ?>
            </div>
		<?php endforeach; ?>
    </div>
<?php endif; ?>
