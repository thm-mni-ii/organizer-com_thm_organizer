<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateBasicList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/**
 * Displays event information
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTemplateBasicList
{
	/**
	 * Renders subject information
	 *
	 * @param array &$view the view context
	 *
	 * @return  void
	 */
	public static function render(&$view, $sort)
	{
		echo '<div class="subject-list-container">';
		if (count($view->items))
		{
			$displayItems = $view->items;

			if ($sort == 'number')
			{
				usort($displayItems, function ($a, $b) {
					return $a->externalID > $b->externalID;
				});
			}

			echo '<table class="subject-list">';
			foreach ($displayItems AS $item)
			{
				echo $view->getItemRow($item, $sort);
			}
			echo '</table>';
		}
		echo '</div>';
	}
}
 