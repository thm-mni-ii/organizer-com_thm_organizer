<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateTeacherList
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
class THM_OrganizerTemplateTeacherList
{
	/**
	 * Renders subject information
	 *
	 * @param array &$view the view context
	 *
	 * @return  void
	 */
	public static function render(&$view)
	{
		if (empty($view->items) OR empty($view->teachers))
		{
			return;
		}

		foreach ($view->teachers AS $teacherID => $teacher)
		{
			$rows = [];

			foreach ($view->items as $subjectKey => $subject)
			{
				$isResponsible = (isset($subject->teachers[1]) AND array_key_exists($teacherID, $subject->teachers[1]));
				$isTeacher     = (isset($subject->teachers[2]) AND array_key_exists($teacherID, $subject->teachers[2]));

				switch ($view->params->get('teacherResp', 0))
				{
					case 1:

						$relevant = $isResponsible;
						break;

					case 2:

						$relevant = $isTeacher;
						break;

					default:
						$relevant = ($isResponsible OR $isTeacher);
						break;
				}

				if ($relevant)
				{
					$rows[] = $view->getItemRow($view->items[$subjectKey], 'teacher', $teacherID);
				}

			}

			if (!empty($rows))
			{
				?>
				<fieldset class="teacher-group">
					<legend>
						<span class="teacher-title"><?php echo $view->getTeacherText($teacherID); ?></span>
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