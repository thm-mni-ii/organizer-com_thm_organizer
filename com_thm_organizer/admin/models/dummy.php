<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelAsset
 * @description THM_OrganizerModelAsset component admin model
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelAsset for component com_thm_organizer
 *
 * Class provides methods to deal with asset
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 * @link        www.mni.thm.de
 * @since       v1.5.0
*/
class THM_OrganizerModelDummy extends JModelAdmin
{
    /**
     * Method to get the table
     *
     * @param   String  $type    Type  			(default: 'assets')
     * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
     * @param   Array   $config  Configuration  (default: 'Array')
     *
     * @return  JTable object
     */
    public function getTable($type = 'assets', $prefix = 'THM_OrganizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the form
     *
     * @param   Array    $data      Data  	   (default: Array)
     * @param   Boolean  $loadData  Load data  (default: true)
     *
     * @return  A Form object
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.dummy', 'dummy', array('control' => 'jform', 'load_data' => $loadData));

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
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_thm_organizer.edit.dummy.data', array());

        if (empty($data))
        {
            $data = $this->getItem();
        }
        return $data;
    }

    /**
     * Method to load the form data
     *
     * @param   Array  $data  Data
     *
     * @return  Boolean
     */
    public function save($data)
    {
        // Save the dummy to the database
        if (parent::save($data))
        {
            return true;
        }
    }
}
