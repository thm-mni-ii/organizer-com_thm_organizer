<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        table virtual schedules
 * @description database table abstraction file
 * @author      Wolf Rost
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.table' );

class TableMembermanager extends JTable
{
    var $vid = null;
	var $vname=null;
	var $vtype =null;
	var $vresponsible = null;
	var $unittype = null;
	var $department = null;
	var $sid = null;

    function TableMembermanager( &$db ) {
        parent::__construct('#__thm_organizer_virtual_schedules', 'vid', $db);
    }

}
?>