<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.media
 * @name        THM_OrganizerModelCampuses
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

require_once 'language.php';

/**
 * Provides methods for room type objects
 *
 * @category    Joomla.Component.Media
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.media
 */
class THM_OrganizerHelperCampuses
{
	/**
	 * Creates a link to the campus' location
	 *
	 * @param int $campusID the id of the campus
	 *
	 * @return string the HTML for the location link
	 *
	 * @since 2017-12-05
	 */
	public static function getLocation($campusID)
	{
		$table  = JTable::getInstance('campuses', 'thm_organizerTable');
		$table->load($campusID);

		if (!empty($table->location))
		{
			$coordinates = str_replace(' ', '', $table->location);
			$location    = '<a target="_blank" href="https://www.google.de/maps/place/' . $coordinates . '">';
			$location    .= '<span class="icon-location"></span>';
			$location    .= '</a>';
			return $location;
		}

		return '';
	}

	/**
	 * Gets the qualified campus name
	 *
	 * @param string $campusID the campus' id
	 *
	 * @return  string the name if the campus could be resolved, otherwise empty
	 */
	public static function getName($campusID)
	{
		$languageTag = THM_OrganizerHelperLanguage::getShortTag();
		$dbo         = JFactory::getDbo();
		$query       = $dbo->getQuery(true);
		$query->select("c1.name_$languageTag as name, c2.name_$languageTag as parentName")
			->from('#__thm_organizer_campuses as c1')
			->leftJoin('#__thm_organizer_campuses as c2 on c1.parentID = c2.id')
			->where("c1.id = $campusID");
		$dbo->setQuery($query);

		try
		{
			$names = $dbo->loadAssoc();
		}
		catch (Exception $exc)
		{
			JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');

			return '';
		}

		if (empty($names))
		{
			return '';
		}

		return empty($names['parentName']) ? $names['name'] : "{$names['parentName']} / {$names['name']}";
	}
}
