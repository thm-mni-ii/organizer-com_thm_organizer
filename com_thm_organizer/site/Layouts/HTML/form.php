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

$isSite = OrganizerHelper::getApplication()->isClient('site');
$query  = Uri::getInstance()->getQuery();

if ($isSite)
{
	echo OrganizerHelper::getApplication()->JComponentTitle;
	echo $this->subtitle;
	echo $this->supplement;
}
?>
<div id="j-main-container" class="span10">
    <form action="?<?php echo $query; ?>" id="adminForm" method="post" name="adminForm"
          class="form-horizontal form-validate" enctype="multipart/form-data">
		<?php if ($isSite) : ?>
			<?php echo Toolbar::getInstance()->render(); ?>
		<?php endif; ?>
		<?php echo $this->form->renderFieldset('details'); ?>
		<?php echo HTML::_('form.token'); ?>
        <input type="hidden" name="task" value=""/>
    </form>
</div>
