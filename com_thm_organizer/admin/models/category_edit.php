<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        category edit model
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen 
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class retrieving category item information 
 * 
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelCategory_Edit extends JModelAdmin
{
    /**
     * Retrieves the jform object for this view
     * 
     * @param   array    $data      unused
     * @param   boolean  $loadData  if the form data should be pulled dynamically
     *
     * @return  mixed    A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.category_edit', 'category_edit', array('control' => 'jform', 'load_data' => $loadData));
		
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
        $categoryIDs = JRequest::getVar('cid',  null, '', 'array');
        $categoryID = (empty($categoryIDs))? JRequest::getVar('categoryID') : $categoryIDs[0];
        return $this->getItem($categoryID);
    }

    /**
	 * Method to get the table
	 *
	 * @param   String  $type    Type  			(default: 'categories')
	 * @param   String  $prefix  Prefix  		(default: 'THM_OrganizerTable')
	 * @param   Array   $config  Configuration  (default: 'Array')
	 *
	 * @return  JTable object
	 */
    public function getTable($type = 'categories', $prefix = 'thm_organizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
