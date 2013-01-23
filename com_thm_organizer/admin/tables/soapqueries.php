<?php
/**
 * @version     v0.1.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTableSoapqueries
 * @description soap_queries table class
 * @author      Markus Baier markusDOTbaierATmniDOTthmDOTde
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 **/

defined('_JEXEC') or die('Restricted access');

/**
 * Class representing the soap_queries table.
 *
 * @category	Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v0.1.0
 */
class THM_OrganizerTableSoapqueries extends JTable
{    
	/**
	 * Constructor function for the class representing the assets_tree table
	 *
	 * @param   JDatabase  &$dbo  A database connector object
	 */
    public function __construct(&$db)
    {
        parent::__construct('#__thm_organizer_soap_queries', 'id', $db);
    }
}
