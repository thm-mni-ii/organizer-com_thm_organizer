<?php
/**
 *@category    component
 * 
 *@package     THM_Organizer
 * 
 *@subpackage  com_thm_organizer
 *@name        category edit model
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
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
 * Class retrieving category item information 
 * 
 * @package  Admin
 * 
 * @since    2.5.4
 */
class THM_OrganizerModelcategory_edit extends JModelAdmin
{
    /**
     * retrieves the jform object for this view
     * 
     * @param   array    $data      unused
     * @param   boolean  $loadData  if the form data should be pulled dynamically
     *
     * @return	mixed	A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.category_edit',
                                'category_edit',
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
     * @param	integer	 $key  not used
     *
     * @return	mixed	Object on success, false on failure.
     */
    public function getItem($key = null)
    {
        $categoryIDs = JRequest::getVar('cid',  null, '', 'array');
        $categoryID = (empty($categoryIDs))? JRequest::getVar('categoryID') : $categoryIDs[0];
        return ($categoryID)? parent::getItem($categoryID) : $this->getTable();
    }

    /**
     * returns a table object the parameters are completely superfluous in the
     * implementing classes since they are always set by default
     *
     * @param	string  $type    the table type to instantiate
     * @param	string	$prefix  a prefix for the table class name. optional.
     * @param	array	$config  configuration array for model. optional.
     *
     * @return	JTable	A database object
    */
    public function getTable($type = 'categories', $prefix = 'thm_organizerTable', $config = array())
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
