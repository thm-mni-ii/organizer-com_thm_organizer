<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerTemplateEdit_Basic
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Ilja Michajlow, <Ilja.Michajlow@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides a template for basic views
 *
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerTemplateEdit_Basic
{
	/**
	 * Method to create a list output
	 *
	 * @param   object &$view the view context calling the function
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
		      class="form-horizontal">
			<fieldset class="adminform"
			">
			<?php echo $view->form->renderFieldset('details'); ?>
			</fieldset>
			<?php echo $view->form->getInput('id'); ?>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="task" value=""/>
		</form>
		<?php
	}

}
