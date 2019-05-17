<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

// Sets page configuration and component option
$backURL = empty($this->menu) ? Uri::base() . '?option=com_thm_organizer&' : $this->menu['route'];

// Accessed from subject_details
$backURL .= empty($this->lessonID) ?
    "&view=subject_details&id={$this->subjectID}" : "&view=courses&lessonID={$this->lessonID}";

$nameProperty = 'name_' . $this->languageTag;
?>
<div class="toolbar">
    <?php echo $this->languageLinks->render($this->languageParams); ?>
</div>
<div class="subject-edit-view">
    <h1>
        <?php echo $this->form->getValue($nameProperty) . ': ' . Languages::_('THM_ORGANIZER_EDIT'); ?>
    </h1>
    <form action="?" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm"
          class="form-horizontal">

        <button type="submit" class="validate btn btn-primary">
            <?php echo Languages::_('JSAVE'); ?>
        </button>

        <a href="<?php echo Route::_($backURL, false); ?>"
           class="btn" type="button"><?php echo Languages::_('JCANCEL') ?></a>
        <hr>
        <div class="form-horizontal">
            <?php
            echo HTML::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']);

    foreach ($this->form->getFieldSets() as $set) {
        $isInitialized  = (bool)$this->form->getValue('id');
        $displayInitial = isset($set->displayinitial) ? $set->displayinitial : true;

        if ($displayInitial or $isInitialized) {
            echo HTML::_('bootstrap.addTab', 'myTab', $set->name, Languages::_($set->label, true));
            echo $this->form->renderFieldset($set->name);
            echo HTML::_('bootstrap.endTab');
        }
    }
    echo HTML::_('bootstrap.endTabSet');
    ?>
    </div>
    <?php echo HTML::_('form.token'); ?>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="lessonID" value="<?php echo $this->lessonID; ?>"/>
    <input type="hidden" name="languageTag" value="<?php echo $this->languageTag; ?>"/>
</form>
</div>