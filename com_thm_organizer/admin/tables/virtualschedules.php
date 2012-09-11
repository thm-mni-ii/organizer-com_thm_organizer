<?php
/**
 * @version     v0.0.1
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin.table
 * @name        TableMemberManager
 * @description database table abstraction file
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.table');

/**
 * Class TableMemberManager for component com_thm_organizer
 *
 * Class provides methods to abstract the database table
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin.table
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class TableMemberManager extends JTable
{
	/**
	 * Virtual schedule id
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
    public $vid = null;
    
    /**
     * Virtual schedule name
     *
     * @var    String
     * @since  v0.0.1
     */
	public $vname = null;
	
	/**
	 * Virtual schedule type
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
	public $vtype = null;
	
	/**
	 * Virtual schedule responsible
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
	public $vresponsible = null;
	
	/**
	 * Virtual schedule unit tpye
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
	public $unittype = null;
	
	/**
	 * Virtual schedule department
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
	public $department = null;
	
	/**
	 * Semester id
	 *
	 * @var    Integer
	 * @since  v0.0.1
	 */
	public $sid = null;

	/**
	 * Constructor to call the parent constructor
	 *
	 * @param   Object  &$db  Reference of the database object
	 *
	 * @since   v0.0.1
	 *
	 */
    public function TableMembermanager(&$db)
    {
        parent::__construct('#__thm_organizer_virtual_schedules', 'vid', $db);
    }
}
