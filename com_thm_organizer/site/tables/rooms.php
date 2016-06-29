<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerTableRooms
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.table');

/**
 * Class representing the rooms table.
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerTableRooms extends JTable
{
	/**
	 * Constructor to call the parent constructor
	 *
	 * @param   object  &$dbo  a database connector object
	 */
	public function __construct(&$dbo)
	{
		parent::__construct('#__thm_organizer_rooms', 'id', $dbo);
	}
}
