<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelMajor
 * @description THM_OrganizerModelMajor component admin model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
require_once 'mapping.php';
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Class THM_OrganizerModelMajor for component com_thm_organizer
 *
 * Class provides methods to deal with major
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram_Edit extends JModelAdmin
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
        $form = $this->loadForm('com_thm_organizer.program_edit', 'program_edit', array('control' => 'jform', 'load_data' => $loadData));

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
        $programIDs = JFactory::getApplication()->input->get('cid',  null, 'array');
        $programID = (empty($programIDs))? JFactory::getApplication()->input->getInt('id') : $programIDs[0];
        $this->getChildren($programID);
        return $this->getItem($programID);
    }

    /**
     * Retrieves the programs existent children and loads them into the object
     * variable
     *
     * @param   int  $programID  the id of the program
     *
     * @return  void
     */
    private function getChildren($programID)
    {
        $mappingModel = new THM_OrganizerModelMapping;
        $children = $mappingModel->getChildren($programID, 'program', false);
        THM_OrganizerHelperMapping::setChildren($this, $children);
    }

    /**
     * Method to get the table
     *
     * @param   String  $type    Type              (default: 'Majors')
     * @param   String  $prefix  Prefix          (default: 'THM_OrganizerTable')
     * @param   Array   $config  Configuration  (default: 'Array')
     *
     * @return  JTable object
     */
    public function getTable($type = 'Programs', $prefix = 'THM_OrganizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
