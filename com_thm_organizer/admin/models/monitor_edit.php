<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @name        monitor editor model
 * @description database abstraction file for monitors
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     1.7.0
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
class thm_organizersModelmonitor_edit extends JModelAdmin
{
    public $behaviours = null;

    public function  __construct($config = array())
    {
        parent::__construct($config);
        $this->behaviours = $this->getDisplayBehaviours();
    }

    /**
     * getDisplayBehaviours
     *
     * builds an array of display behaviours
     *
     * @return array
     */
    private function getDisplayBehaviours()
    {
        $dbo = $this->getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id, behaviour');
        $query->from("#__thm_organizer_display_behaviours");
        $dbo->setQuery((string)$query);
        $results = $dbo->loadAssocList();
        $behaviours = array();
        if(count($results))
            foreach($results as $result)$behaviours[$result['id']]= JText::_($result['behaviour']);
        return $behaviours;
    }

    /**
     * getForm
     *
     * retrieves the jform object for this view
     *
     * @return	mixed	A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.monitor_edit', 'monitor_edit', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;
        else return $form;
    }

    /**
     * Method to get a single record.
     *
     * @param	integer	The id of the primary key.
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $monitorIDs = JRequest::getVar('cid',  null, '', 'array');
        $monitorID = (empty($monitorIDs))? JRequest::getInt('monitorID') : $monitorIDs[0];
        return parent::getItem($monitorID);
    }

    /**
     * getTable
     *
     * returns a table object the parameters are completely superfluous in the
     * implementing classes since they are always set by default
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     *
     * @return	JTable	A database object
    */
    public function getTable($type = 'monitors', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * loadFormData
     *
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
