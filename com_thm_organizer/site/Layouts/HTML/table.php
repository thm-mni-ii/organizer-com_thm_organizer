<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2020 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\OrganizerHelper;

$isSite    = OrganizerHelper::getApplication()->isClient('site');
$rows      = $this->rows;
$iteration = 0;
$query     = Uri::getInstance()->getQuery();

echo OrganizerHelper::getApplication()->JComponentTitle;
echo $this->subtitle;
echo $this->supplement;

?>
<div id="j-main-container" class="span10">
	<?php if ($isSite) : ?>
		<?php echo Toolbar::getInstance()->render(); ?>
	<?php endif; ?>
    <form action="<?php echo Uri::base() . "?$query"; ?>" id="adminForm" method="post" name="adminForm">
		<?php require_once 'filters.php'; ?>
        <table class="table table-striped organizer-table">
            <thead><?php echo $this->renderHeaders(); ?></thead>
            <tbody><?php echo $this->renderRows(); ?></tbody>
            <tfoot>
            <tr>
                <td colspan="<?php echo $this->labelCount + $this->columnCount; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
            </tr>
            </tfoot>
        </table>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="option" value="com_thm_organizer"/>
        <input type="hidden" name="view" value="<?php echo $this->get('name'); ?>"/>
		<?php echo HTML::_('form.token'); ?>
    </form>
	<?php echo $this->disclaimer; ?>
</div>


