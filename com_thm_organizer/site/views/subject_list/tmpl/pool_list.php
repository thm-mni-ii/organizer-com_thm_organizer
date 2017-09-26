<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplatePoolList
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
class THM_OrganizerTemplatePoolList
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
		if (empty($view->items) OR empty($view->pools))
		{
			return;
		}

		foreach ($view->pools AS $pool)
		{
			if (empty($pool['subjects']) AND empty($pool['pools']))
			{
				continue;
			}

			$crpText = $view->getCreditPointText($pool);

			?>
			<a name="pool<?php echo $pool['id']; ?>" class="pool-anchor"></a>
			<fieldset class="pool-group">
				<legend>
					<span class="pool-title"><?php echo $pool['name']; ?></span>
					<span class="pool-crp"><?php echo $crpText; ?></span>
				</legend>
				<table>
					<?php
					foreach (array_keys($pool['subjects']) as $subjectKey)
					{
						echo $view->getItemRow($view->items[$subjectKey]);
					}
					foreach (array_keys($pool['pools']) as $poolKey)
					{
						echo $view->getItemRow($view->pools[$poolKey], 'pool');
					}
					?>
				</table>
			</fieldset>
			<?php
		}
	}
}