<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableLecturer
 * @description lecturer table class
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 **/

defined('_JEXEC') or die('Restricted access');

/**
 * Class representing the lecturer table.
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerTableLecturer extends JTable
{
	/**
	 * Constructor to call the parent constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
    public function __construct(&$db)
    {
        parent::__construct('#__thm_organizer_lecturers', 'id', $db);
    }
}
