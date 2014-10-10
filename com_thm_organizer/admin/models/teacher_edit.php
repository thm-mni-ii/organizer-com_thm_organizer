<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelTeacher_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Class THM_OrganizerModelTeacher for component com_thm_organizer
 *
 * Class provides methods to deal with teacher
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelTeacher_Edit extends JModelAdmin
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
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.teacher_edit', 'teacher_edit', array('control' => 'jform', 'load_data' => $loadData));
 
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
        $task = $input->getCmd('task', 'teacher.add');
        $teacherID = $input->getInt('id', 0);

        // Edit can only be explicitly called from the list view or implicitly with an id over a URL
        $edit = (($task == 'teacher.edit')  OR $teacherID > 0);
        if ($edit)
        {
            if (!empty($teacherID))
            {
                return $this->getItem($teacherID);
            }

            $teacherIDs = $input->get('cid',  null, 'array');
            return $this->getItem($teacherIDs[0]);
        }
        return $this->getItem(0);
    }

    /**
     * Method to get the table
     *
     * @param   String  $type    Type              (default: 'Teacher')
     * @param   String  $prefix  Prefix          (default: 'THM_OrganizerTable')
     * @param   Array   $config  Configuration  (default: 'Array')
     *
     * @return  JTable object
     */
    public function getTable($type = 'teachers', $prefix = 'THM_OrganizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
