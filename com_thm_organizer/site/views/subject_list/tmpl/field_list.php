<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateFieldList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Displays event information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateFieldList
{
	/**
	 * Renders subject information
	 *
	 * @param array  &$view  the view context
	 *
	 * @return  void
	 */
	public static function render(&$view)
	{
		if (empty($view->items) OR empty($view->teachers))
		{
			return;
		}

		foreach ($view->fields AS $fieldID => $field)
		{
			$rows = array();

			foreach ($view->items as $subject)
			{
				if ($subject->fieldID == $fieldID)
				{
					$rows[] = $view->getItemRow($subject, 'field');
				}

			}

			if (!empty($rows))
			{
?>
				<fieldset class="teacher-group">
					<legend>
						<span class="pool-title"><?php echo $field['name']; ?></span>
					</legend>
					<table>
<?php
						foreach ($rows as $row)
						{
							echo $row;
						}
?>
					</table>
				</fieldset>
<?php
			}
		}
	}
}