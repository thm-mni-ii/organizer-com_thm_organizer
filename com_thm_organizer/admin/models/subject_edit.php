<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject_Edit
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/referrer.php';

/**
 * Class THM_OrganizerModelSubject_Edit for component com_thm_organizer
 * Class provides methods to deal with course
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject_Edit extends JModelAdmin
{
    /**
     * Method to get the form
     *
     * @param   Array    $data      Type  (default: Array)
     * @param   Boolean  $loadData  Type  (default: true)
     *
     * @return  A Form object
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_thm_organizer.subject_edit', 'subject_edit', array('control' => 'jform', 'load_data' => $loadData));

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
        $subjectIDs = $input->get('cid', null, 'array');
        $subjectID = (empty($subjectIDs))? $input->getInt('subjectID', 0) : $subjectIDs[0];
        $item = $this->getItem($subjectID);
        if (!empty($item->id))
        {
            $item->responsible = $this->getResponsible($item->id);
        }
        THM_OrganizerHelperReferrer::setReferrer('subject');
        return $item;
    }

    /**
     * Retrieves the teacher responsible for the subject's development
     *
     * @param   int  $subjectID  the id of the subject
     *
     * @return  int  the id of the teacher responsible for the subject
     */
    private function getResponsible($subjectID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('teacherID')->from('#__thm_organizer_subject_teachers');
        $query->where("subjectID = '$subjectID'")->where("teacherResp = '1'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $respID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return empty($respID)? 0 : $respID;
    }

    /**
     * Method to get the table
     *
     * @param   String  $type    Type  (default: 'assets')
     * @param   String  $prefix  Type  (default: 'THM_OrganizerTable')
     * @param   Array   $config  Type  (default: 'Array')
     *
     * @return  JTable object
     */
    public function getTable($type = 'subjects', $prefix = 'THM_OrganizerTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }
}
