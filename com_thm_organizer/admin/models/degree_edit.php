<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelDegree_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class loads form data to edit an entry.
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelDegree_Edit extends JModelAdmin
{
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
        $form = $this->loadForm('com_thm_organizer.degree_edit', 'degree_edit', array('control' => 'jform', 'load_data' => $loadData));

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
        $input = JFactory::getApplication()->input;
        $task = $input->getCmd('task', 'degree.add');
        $degreeID = $input->getInt('id', 0);

        // Edit can only be explicitly called from the list view or implicitly with an id over a URL
        $edit = (($task == 'degree.edit')  OR $degreeID > 0);
        if ($edit)
        {
            if (!empty($degreeID))
            {
                return $this->getItem($degreeID);
            }

            $degreeIDs = $input->get('cid',  null, 'array');
            return $this->getItem($degreeIDs[0]);
        }
        return $this->getItem(0);
    }

    /**
     * Method to get the table
     *
     * @param   String  $type    Type              (default: 'degrees')
     * @param   String  $prefix  Prefix          (default: 'THM_OrganizerTable')
     * @param   Array   $config  Configuration  (default: 'Array')
     *
     * @return  JTable object
     */
    public function getTable($type = 'degrees', $prefix = 'THM_OrganizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
