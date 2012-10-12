<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        monitor edit model
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *@author      Daniel Kirsten danielDOTkirstenATmniDOTthmDOTde
 * 
 *@copyright   2012 TH Mittelhessen
 * 
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
/**
 * Class retrieving a monitor entry to be edited 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class thm_organizersModelmonitor_edit extends JModelAdmin
{
    public $behaviours = null;

    /**
     * constructor
     * 
     * @param   type  $config  an optional array of configuration information
     */
    public function  __construct($config = array())
    {
        parent::__construct($config);
        $this->behaviours = array(
                                  1 => JText::_('COM_THM_ORGANIZER_MON_SCHEDULE'),
                                  2 => JText::_('COM_THM_ORGANIZER_MON_MIXED'),
                                  3 => JText::_('COM_THM_ORGANIZER_MON_CONTENT'),
                                  4 => JText::_('COM_THM_ORGANIZER_MON_EVENTS')
                                 );
    }

    /**
     * retrieves the jform object for this view
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return	mixed	A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.monitor_edit',
                                'monitor_edit',
                                array('control' => 'jform', 'load_data' => $loadData)
                               );
        if (empty($form))
        {
            return false;
        }
        else
        {
            return $form;
        }
    }

    /**
     * Method to get a single record.
     *
     * @param	integer  $primaryKey  The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($primaryKey = null)
    {
        $monitorIDs = JRequest::getVar('cid',  null, '', 'array');
        $monitorID = (empty($monitorIDs))? JRequest::getInt('monitorID') : $monitorIDs[0];
        return ($monitorID)? parent::getItem($monitorID) : $this->getTable();;
    }

    /**
     * returns a table object the parameters are completely superfluous in the
     * implementing classes since they are always set by default
     *
     * @param	string  $type    The table type to instantiate
     * @param	string  $prefix  A prefix for the table class name. Optional.
     * @param	array   $config  Configuration array for model. Optional.
     *
     * @return	JTable	A database object
    */
    public function getTable($type = 'monitors', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * retrieves the data that should be injected in the form the loading is
     * done in jmodel admin
     *
     * @return	mixed	The data for the form.
     */
    protected function loadFormData()
    {
        return $this->getItem();
    }

}
