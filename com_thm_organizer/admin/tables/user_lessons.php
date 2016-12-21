<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableUser_Lessons
 * @author      Franciska Perisa, <franciska.perisa@mni.thm.de>
 * @copyright   2017 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.database.table');

/**
 * Class representing the user_lesson table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTableUser_Lessons extends JTable
{
	/**
	 * fields get encoded by binding, when values are arrays
	 * 
	 * @var array
	 */
	protected $_jsonEncode = array('configuration');

	/**
	 * Constructor for the user_lesson table, makes 'id' the primary key.
	 *
	 * @param JDatabaseDriver &$dbo A database connector object
	 */
	public function __construct(&$dbo)
	{
		parent::__construct('#__thm_organizer_user_lessons', 'id', $dbo);
	}

	/**
	 * defines the nullable columns
	 *
	 * @return boolean true
	 */
	public function check()
	{
		$this->status_date  = null;

		return true;
	}
}
