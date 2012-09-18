<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.admin
 * @name		THM_OrganizerTableLecturer
 * @description THM_OrganizerTableLecturer component admin table
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

/**
 * Class THM_OrganizerTableLecturer for component com_thm_organizer
 *
 * Class provides methods to mapping the lecturer database table
 *
 * @category	Joomla.Component.Admin
 * @package     thm_curriculum
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
 */
class THM_OrganizerTableLecturer extends JTable
{
	/**
	 * Constructor to call the parent constructor
	 *
	 * @param   Object  &$db  Database
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__thm_curriculum_lecturers', 'id', $db);
	}
}
