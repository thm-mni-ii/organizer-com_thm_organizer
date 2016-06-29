<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organiezr.site
 * @name        THM_OrganizerTemplateUngroupedList
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
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
class THM_OrganizerTemplateItem
{
	/**
	 * Renders subject information
	 *
	 * @param   array  &$item the item to be displayed
	 * @param   string $type  the type of group
	 *
	 * @return  string  the HTML for the item to be displayed
	 */
	public static function render(&$item, $type = '')
	{
		$displayItem = '';
		$moduleNr    = empty($item->externalID) ? '' : '<span class="module-id" >(' . $item->externalID . ')';
		$link        = empty($item->subjectLink) ? 'XXXX' : '<a href="' . $item->subjectLink . '">XXXX</a>';

		$borderStyle = ' style="border-left: 8px solid ';
		$borderStyle .= empty($item->subjectColor) ? 'transparent' : $item->subjectColor;
		$borderStyle .= ';."';

		$field      = empty($item->field) ? '' : $item->field;
		$fieldStyle = ' style="height: 19px; width: 12px !important; position: relative; left: -29px;';
		$fieldStyle .= empty($item->field) ? ' cursor: default;"' : ' cursor: help;"';

		$displayItem .= '<li ' . $borderStyle . '>';
		$displayItem .= '<span class="subject-field hasTooltip" ';
		$displayItem .= $fieldStyle . ' title="' . $field . '">&nbsp;&nbsp;&nbsp;</span>';
		$displayItem .= '<span class="subject-name">' . str_replace('XXXX', $item->subject . $moduleNr, $link) . '</span>';
		if ($type != 'teacher' && !empty($item->teacherName))
		{
			$displayItem .= '<span class="subject-teacher">' . $item->teacherName . '</span>';
		}
		if (!empty($item->creditpoints))
		{
			$displayItem .= '<span class="subject-crp">' . str_replace('XXXX', $item->creditpoints, $link) . ' CrP</span>';
		}
		$displayItem .= '</li>';

		return $displayItem;
	}
}