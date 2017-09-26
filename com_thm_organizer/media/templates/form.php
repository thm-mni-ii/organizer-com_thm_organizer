<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerTemplateForm
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Ilja Michajlow, <Ilja.Michajlow@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

/**
 * Class provides a template for non-item-specific forms
 *
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerTemplateForm
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
			  class="form-horizontal">
			<div class="form-horizontal">
				<?php echo $view->form->renderFieldset('details'); ?>
			</div>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="task" value=""/>
		</form>
		<?php
	}

}
