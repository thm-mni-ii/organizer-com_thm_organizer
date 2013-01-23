<?php
/**
 * @version     v0.0.1
 * @category	Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        reservation ajax response model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2011 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

class THM_OrganizerModelbooking extends JModel
{
    private $resource_tables = null;
    private $joomla_tables = null;

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->resource_tables = array( 'classes', 'departments', 'descriptions', 'periods', 'rooms', 'subjects', 'teachers');
        $this->joomla_tables = array( 'users', 'usergroups');
    }

    public function prepareData($suffix, $what, $where)
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select($what);

        $query->where($where);
        if(in_array($suffix, $this->resource_tables))
        {

        }
        else if(in_array($suffix, $this->joomla_tables))
        {

        }
    }
}