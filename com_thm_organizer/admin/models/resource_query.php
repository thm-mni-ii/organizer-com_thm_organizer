<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        reservation ajax response model
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     0.0.1
 */
defined('_JEXEC') or die;
jimport( 'joomla.application.component.model' );
class thm_organizerModelbooking extends JModel
{
    private $resource_tables = null;
    private $joomla_tables = null;
    private

    public function  __construct($config = array())
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