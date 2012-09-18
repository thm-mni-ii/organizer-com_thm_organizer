<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        assets_tree table class
 *@author      Markus Baier markusDOTbaierATmniDOTthmDOTde
 *@author      Wolf Rost wolfDOTrostATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.table');
/**
 * Class representing the assets_tree table. 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class THM_OrganizerTableAssets_Tree extends JTable
{
    /**
     * Constructor function for the class representing the assets_tree table
     * 
     * @param   JDatabase  &$dbo  A database connector object
     */
    public function __construct(&$dbo)
    {
        parent::__construct('#__thm_organizer_assets_tree', 'id', $dbo);
    }

    /**
     * Method to move
     *
     * @param   Integer  $delta  Delta id
     * @param   String   $where  Where condition  (default: '')
     *
     * @return void
     */
    public function move($delta, $where = '')
    {
        // Get the major id
        $major_id = $_SESSION['stud_id'];

        // If there is no ordering field set an error and return false
        if (!property_exists($this, 'ordering'))
        {
            $exception = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_ORDERING', get_class($this)));
            $this->setError($exception);
            return false;
        }

        // If the change is none, do nothing
        if (empty($delta))
        {
                return true;
        }

        // Initialise variables
        $key		= $this->_tbl_key;
        $row	= null;
        $query	= $this->_db->getQuery(true);

        // Select the primary key and ordering values from the table
        $query->select('assets_tree.' . $this->_tbl_key . ' , ordering');
        $query->from(' #__thm_organizer_assets_tree as assets_tree');
        $query->join('inner', '#__thm_organizer_assets_semesters as assets_semesters ON assets_semesters.assets_tree_id = assets_tree.id');
        $query->join('inner', '#__thm_organizer_semesters_assets_tree as semesters_assets_tree ON assets_semesters.semesters_assets_tree_id = semesters_assets_tree.id');
        $query->where("semesters_assets_tree.major_id =" . $major_id);

        // If the movement delta is negative move the row up
        if ($delta < 0)
        {
            $query->where('ordering < ' . (int) $this->ordering);
            $query->where('parent_id = ' . (int) $this->parent_id);
            $query->order('ordering DESC');
        }
        // If the movement delta is positive move the row down
        elseif ($delta > 0)
        {
            $query->where('ordering > ' . (int) $this->ordering);
            $query->where('parent_id = ' . (int) $this->parent_id);
            $query->order('ordering ASC');
        }

        // Add the custom WHERE clause if set
        if ($where)
        {
            $query->where($where);
        }

        // Select the first row with the criteria
        $this->_db->setQuery($query, 0, 1);
        $row = $this->_db->loadObject();

        // If a row is found, move the item
        if (!empty($row))
        {
            // Update the ordering field for this instance to the row's ordering value
            $query = $this->_db->getQuery(true);
            $query->update($this->_tbl);
            $query->set('ordering = ' . (int) $row->ordering);
            $query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->$key));
            $this->_db->setQuery($query);

            // Check for a database error
            if (!$this->_db->query())
            {
                $exception = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
                $this->setError($exception);

                return false;
            }

            // Update the ordering field for the row to this instance's ordering value
            $query = $this->_db->getQuery(true);
            $query->update($this->_tbl);
            $query->set('ordering = ' . (int) $this->ordering);
            $query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->$key));
            $this->_db->setQuery($query);

            // Check for a database error
            if (!$this->_db->query())
            {
                $exception = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
                $this->setError($exception);

                return false;
            }

            // Update the instance value
            $this->ordering = $row->ordering;
        }
        else
        {
            // Update the ordering field for this instance
            $query = $this->_db->getQuery(true);
            $query->update($this->_tbl);
            $query->set('ordering = ' . (int) $this->ordering);
            $query->where($this->_tbl_key . ' = ' . $this->_db->quote($this->$key));
            $this->_db->setQuery($query);

            // Check for a database error
            if (!$this->_db->query())
            {
                $exception = new JException(JText::sprintf('JLIB_DATABASE_ERROR_MOVE_FAILED', get_class($this), $this->_db->getErrorMsg()));
                $this->setError($exception);

                return false;
            }
        }
        return true;
    }

    /**
     * Method to compact the ordering values of rows in a group of rows
     * defined by an SQL WHERE clause.
     *
     * @param   String  $where  WHERE clause to use for limiting the selection of rows to
     *                          compact the ordering values.
     *
     * @return  mixed   Boolean true on success.
     *
     * @link    http://docs.joomla.org/JTable/reorder
     * @since   11.1
     */
    public function reorder($where = '')
    {
        $major_id = $_SESSION['stud_id'];

        // If there is no ordering field set an error and return false.
        if (!property_exists($this, 'ordering'))
        {
            $exception = new JException(JText::sprintf('JLIB_DATABASE_ERROR_CLASS_DOES_NOT_SUPPORT_ORDERING', get_class($this)));
            $this->setError($exception);
            return false;
        }

        // Initialise variables.
        $key = $this->_tbl_key;

        // Get the primary keys and ordering values for the selection.
        $query = $this->_db->getQuery(true);

        // Select the primary key and ordering values from the table.
        $query->select('assets_tree.' . $this->_tbl_key . ', ordering');
        $query->from(' #__thm_organizer_assets_tree as assets_tree');
        $query->join('inner', '#__thm_organizer_assets_semesters as assets_semesters ON assets_semesters.assets_tree_id = assets_tree.id');
        $query->join('inner', '#__thm_organizer_semesters_assets_tree as semesters_assets_tree ON assets_semesters.semesters_assets_tree_id = semesters_assets_tree.id');
        $query->where("semesters_assets_tree.major_id =" . $major_id);
        $query->where('ordering >= 0');
        $query->where('depth != "NULL"');
        $query->order('ordering');

        // Setup the extra where and ordering clause data.
        if ($where)
        {
            $query->where($where);
        }

        echo $query;
        $this->_db->setQuery($query);
        $rows = $this->_db->loadObjectList();

        // Check for a database error
        if ($this->_db->getErrorNum())
        {
            $exception = new JException(JText::sprintf('JLIB_DATABASE_ERROR_REORDER_FAILED', get_class($this), $this->_db->getErrorMsg()));
            $this->setError($exception);

            return false;
        }

        // Compact the ordering values
        foreach ($rows as $i => $row)
        {
            // Make sure the ordering is a positive integer
            if ($row->ordering >= 0)
            {
                // Only update rows that are necessary
                if ($row->ordering != $i + 1)
                {
                    // Update the row ordering field
                    $query = $this->_db->getQuery(true);
                    $query->update($this->_tbl);
                    $query->set('ordering = ' . ($i + 1));
                    $query->where($this->_tbl_key . ' = ' . $this->_db->quote($row->$key));
                    $this->_db->setQuery($query);

                    // Check for a database error
                    if (!$this->_db->query())
                    {
                        $exception = new JException(
                            JText::sprintf(
                                            'JLIB_DATABASE_ERROR_REORDER_UPDATE_ROW_FAILED',
                                            get_class($this),
                                            $i,
                                            $this->_db->getErrorMsg()
                                          )
                                                   );
                        $this->setError($exception);

                        return false;
                    }
                }
            }
        }
        return true;
    }
}
