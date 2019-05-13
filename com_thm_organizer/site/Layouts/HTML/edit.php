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

use Organizer\Helpers\HTML;

?>
<form action="index.php?option=com_thm_organizer"
      enctype="multipart/form-data"
      method="post"
      name="adminForm"
      id="item-form"
      class="form-horizontal form-validate">
        <?php echo $this->form->renderFieldset('details'); ?>
    <?php echo $this->form->getInput('id'); ?>
    <?php echo HTML::_('form.token'); ?>
    <input type="hidden" name="task" value=""/>
</form>