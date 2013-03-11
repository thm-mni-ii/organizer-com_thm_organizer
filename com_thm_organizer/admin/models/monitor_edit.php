<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        monitor edit model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @author      Daniel Kirsten, <daniel.kirsten@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class retrieving a monitor entry to be edited 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelMonitor_Edit extends JModelAdmin
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
     * @return    mixed    A JForm object on success, false on failure
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
     * retrieves the data that should be injected in the form the loading is
     * done in jmodel admin
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        $monitorIDs = JRequest::getVar('cid',  null, '', 'array');
        $monitorID = (empty($monitorIDs))? JRequest::getInt('monitorID') : $monitorIDs[0];
        return $this->getItem($monitorID);
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $monitorID  The id of the primary key.
     *
     * @return mixed    Object on success, false on failure.
     */
    public function getItem($monitorID = null)
    {
        return ($monitorID)? parent::getItem($monitorID) : $this->getTable();
    }

    /**
     * returns a table object the parameters are completely superfluous in the
     * implementing classes since they are always set by default
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return    JTabl        A database object
    */
    public function getTable($type = 'monitors', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
