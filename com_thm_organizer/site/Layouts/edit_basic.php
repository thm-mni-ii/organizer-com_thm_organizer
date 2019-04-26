<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */


/**
 * Class provides a standardized display of basic item edit forms.
 */
class THM_OrganizerLayoutEdit
{
    /**
     * Method to create a list output
     *
     * @param object &$view the view context calling the function
     *
     * @return void
     */
    public static function render(&$view)
    {
        ?>
        <form action="index.php?option=com_thm_organizer"
              enctype="multipart/form-data"
              method="post"
              name="adminForm"
              id="item-form"
              class="form-horizontal form-validate">
            <fieldset class="adminform">
                <?php echo $view->form->renderFieldset('details'); ?>
            </fieldset>
            <?php echo $view->form->getInput('id'); ?>
            <?php echo HTML::_('form.token'); ?>
            <input type="hidden" name="task" value=""/>
        </form>
        <?php
    }
}
