<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerTablePools
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 **/
defined('_JEXEC') or die;
jimport('joomla.application.component.table');
/**
 * Class representing the mapping table.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerTablePools extends JTable
{
    /**
     * Constructor function for the class representing the mapping table
     *
     * @param   JDatabase  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_pools', 'id', $dbo);
    }
 
    /**
     * moves entries within the table structure
     *
     * @param   Integer  $delta  Delta id
     * @param   String   $where  Where condition  (default: '')
     *
     * @return void
     */
    public function move($delta, $where = '')
    {
        /* get the major id */
        $major_id = $_SESSION['stud_id'];
 
        // If there is no ordering field set an error and return false.
        if (!property_exists($this, 'ordering'))
        {
            $error = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_ORDERING', get_class($this)));
            $this->setError($error);
            return false;
        }
 
        // If the change is none, do nothing.
        if (empty($delta))
        {
            return true;
        }
 
        // Initialise variables.
        $query    = $this->_db->getQuery(true);
 
        // Select the primary key and ordering values from the table.
        $query->select('assets_tree.' . $this->_tbl_key . ', ordering');
        $query->from(' #__thm_organizer_assets_tree as assets_tree');
        $query->innerJoin('#__thm_organizer_assets_semesters as assets_semesters ON assets_semesters.assets_tree_id = assets_tree.id');
        $query->innerJoin('#__thm_organizer_semesters_majors as semesters_majors ON assets_semesters.semesters_majors_id = semesters_majors.id');
        $query->where("semesters_majors.major_id =" . $major_id);

 
        // If the movement delta is negative move the row up.
        if ($delta < 0)
        {
            $query->where('ordering < ' . (int) $this->ordering);
            $query->where('parent_id = ' . (int) $this->parent_id);
            $query->order('ordering DESC');
        }
        // If the movement delta is positive move the row down.
        elseif ($delta > 0)
        {
            $query->where('ordering > ' . (int) $this->ordering);
            $query->where('parent_id = ' . (int) $this->parent_id);
            $query->order('ordering ASC');
        }
 
        // Add the custom WHERE clause if set.
        if ($where)
        {
            $query->where($where);
        }
 
        // Select the first row with the criteria.
        $this->_db->setQuery($query, 0, 1);
        $row = $this->_db->loadObject();
 
 
        // If a row is found, move the item.
        if (!empty($row))
        {
            // Update the ordering field for this instance to the row's ordering value.
            $query = $this->_db->getQuery(true);
            $query->update($this->_tbl);
            $query->set('ordering = ' . (int) $row->ordering);
            $query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->{$this->_tbl_key}));
            $this->_db->setQuery($query);
 
            // Check for a database error.
            if (!$this->_db->query())
            {
                $error = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
                $this->setError($error);
 
                return false;
            }
 
            // Update the ordering field for the row to this instance's ordering value.
            $query = $this->_db->getQuery(true);
            $query->update($this->_tbl);
            $query->set('ordering = ' . (int) $this->ordering);
            $query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->{$this->_tbl_key}));
            $this->_db->setQuery($query);
 
            // Check for a database error.
            if (!$this->_db->query())
            {
                $error = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
                $this->setError($error);
 
                return false;
            }
 
            // Update the instance value.
            $this->ordering = $row->ordering;
        }
        else
        {
            // Update the ordering field for this instance.
            $query = $this->_db->getQuery(true);
            $query->update($this->_tbl);
            $query->set('ordering = ' . (int) $this->ordering);
            $query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->{$this->_tbl_key}));
            $this->_db->setQuery($query);
 
            // Check for a database error.
            if (!$this->_db->query())
            {
                $error = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
                $this->setError($error);
 
                return false;
            }
        }
 
        return true;
    }
 
}
