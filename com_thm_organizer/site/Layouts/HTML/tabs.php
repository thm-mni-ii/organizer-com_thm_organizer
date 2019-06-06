<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Organizer\Helpers\HTML;
use Organizer\Helpers\Languages;

?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-horizontal form-validate">
    <?php
    echo HTML::_('bootstrap.startTabSet', 'myTab', ['active' => 'details']);

    foreach ($this->form->getFieldSets() as $set) {
        $isInitialized  = (bool)$this->form->getValue('id');
        $displayInitial = isset($set->displayinitial) ? $set->displayinitial : true;

        if ($displayInitial or $isInitialized) {
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