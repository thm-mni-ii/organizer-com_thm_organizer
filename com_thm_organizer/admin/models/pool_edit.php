<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
require_once 'mapping.php';
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Creates form data for a subject pool from information in the database.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Edit extends JModelAdmin
{
    public $children = null;

    /**
     * Method to get the form
     *
     * @param   Array    $data      Data         (default: Array)
     * @param   Boolean  $loadData  Load data  (default: true)
     *
     * @return  A Form object
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.pool_edit', 'pool_edit', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form))
        {
            return false;
        }
        return $form;
    }

    /**
     * Method to load the form data
     *
     * @return  Object
     */
    protected function loadFormData()
    {
        $poolIDs = JFactory::getApplication()->input->get('cid',  null, 'array');
        $poolID = (empty($poolIDs))? JFactory::getApplication()->input->get('id') : $poolIDs[0];
        $this->getChildren($poolID);
        return $this->getItem($poolID);
    }

    /**
     * Method to get the table
     *
     * @param   String  $type    Type              (default: 'mapping')
     * @param   String  $prefix  Prefix          (default: 'THM_OrganizerTable')
     * @param   Array   $config  Configuration  (default: 'Array')
     *
     * @return  JTable object
     */
    public function getTable($type = 'pools', $prefix = 'THM_OrganizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Retrieves the programs existent children and loads them into the object
     * variable
     *
     * @param   int  $poolID  the id of the program
     *
     * @return  void
     */
    private function getChildren($poolID)
    {
        $mappingModel = new THM_OrganizerModelMapping;
        $children = $mappingModel->getChildren($poolID, 'pool', false);
        THM_OrganizerHelperMapping::setChildren($this, $children);
    }
}
