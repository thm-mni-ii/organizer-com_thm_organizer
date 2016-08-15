<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableLesson_Pools
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.database.table');

/**
 * Class representing the lesson_pools table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTableLesson_Pools extends JTable
{
	/**
	 * Constructor function for the class representing the lesson_pools table
	 *
	 * @param   JDatabaseDriver &$dbo A database connector object
	 */
	public function __construct(&$dbo)
	{
		parent::__construct('#__thm_organizer_lesson_pools', 'id', $dbo);
	}
}
