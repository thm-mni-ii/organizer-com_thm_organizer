<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSchedule_Ajax
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Class provides methods for retrieving program data
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSchedule_Ajax extends JModelLegacy
{
	/**
	 * Constructor to set up the class variables and call the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Getter method for all grids in database
	 *
	 * @return string all grids in JSON format
	 *
	 * @throws RuntimeException
	 * @throws Exception
	 */
	public function grids()
	{
		$dbo = JFactory::getDbo();
		$languageTag = explode('-', JFactory::getLanguage()->getTag())[0];
		$query = $dbo->getQuery(true);
		$query->select("name_$languageTag, grid");
		$query->from('#__thm_organizer_grids');
		$dbo->setQuery((string) $query);

		try
		{
			$grids = $dbo->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR', 'error');
			return '{}';
		}

		return json_encode($grids);
	}
}
