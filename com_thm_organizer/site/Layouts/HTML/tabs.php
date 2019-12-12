<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2019 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;
use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;
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
<form action="?<?php echo $query; ?>" id="adminForm" method="post" name="adminForm"
      class="form-horizontal form-validate" enctype="multipart/form-data">
	<?php if ($isSite) : ?>
		<?php echo Toolbar::getInstance()->render(); ?>
	<?php endif; ?>
	<?php
	echo HTML::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']);

	foreach ($this->form->getFieldSets() as $set)
	{
		$isInitialized  = (bool) $this->form->getValue('id');
		$displayInitial = isset($set->displayinitial) ? $set->displayinitial : true;

		if ($displayInitial or $isInitialized)
		{
			echo HTML::_('bootstrap.addTab', 'myTab', $set->name, Languages::_('THM_ORGANIZER_' . $set->label, true));
			echo $this->form->renderFieldset($set->name);
			echo HTML::_('bootstrap.endTab');
		}
	}
	echo HTML::_('bootstrap.endTabSet');
	?>
	<?php echo HTML::_('form.token'); ?>
    <input type="hidden" name="task" value=""/>
</form>