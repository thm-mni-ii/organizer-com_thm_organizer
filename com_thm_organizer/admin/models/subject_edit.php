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
jimport('thm_core.edit.model');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/referrer.php';

/**
 * Class THM_OrganizerModelSubject_Edit for component com_thm_organizer
 * Class provides methods to deal with course
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject_Edit extends THM_CoreModelEdit
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
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
}
