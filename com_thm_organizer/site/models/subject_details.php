<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_Details
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2015 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/helpers/teacher.php';

/**
 * Class THM_OrganizerModeldetails for component com_thm_organizer
 *
 * Class provides methods to get details about modules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_Details extends JModelLegacy
{
    /**
     * Loads subject information from the database
     *
     * @return  object  filled with subject data on success, otherwise empty
     */
    public function getItem()
    {
        $subjectID = $this->resolveID();
        if (empty($subjectID))
        {
            return new stdClass;
        }

        $input = JFactory::getApplication()->input;
        $langTag = $input->getString('languageTag', THM_CoreHelper::getLanguageShortTag());
        $query = $this->_db->getQuery(true);

        $select = "s.id, externalID, name_$langTag AS name, description_$langTag AS description, ";
        $select .= "objective_$langTag AS objective, content_$langTag AS content, instructionLanguage, ";
        $select .= "preliminary_work_$langTag AS preliminary_work, literature, creditpoints, expenditure, ";
        $select .= "present, independent, proof_$langTag AS proof, frequency_$langTag AS frequency, ";
        $select .= "method_$langTag AS method, recommended_prerequisites_$langTag as recommended_prerequisites, ";
        $select .= "prerequisites_$langTag AS prerequisites, aids_$langTag AS aids, ";
        $select .= "evaluation_$langTag AS evaluation, sws, expertise, method_competence, self_competence, social_competence";

        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_frequencies AS f ON s.frequencyID = f.id');
        $query->where("s.id = '$subjectID'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $subject = $this->_db->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return new stdClass;
        }

        $this->setExpenditureText($subject);
        $this->setPrerequisiteOf($subject);
        $this->setTeachers($subject);
        return $subject;
    }

    /**
     * Attempts to determine the desired subject id
     *
     * @return  mixed  int on success, otherwise null
     */
    private function resolveID()
    {
        $input = JFactory::getApplication()->input;
        $requestID = $input->getInt('id', 0);
        if (!empty($requestID))
        {
            return $requestID;
        }

        $externalID = $input->getString('nrmni', '');
        if (empty($externalID))
        {
            return null;
        }

        $query = $this->_db->getQuery(true);
        $query->select('id')->from('#__thm_organizer_subjects')->where("externalID = '$externalID'");
        $this->_db->setQuery((string) $query);

        try
        {
            return $this->_db->loadResult();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return null;
        }
    }

    /**
     * Creates a textual output for the various expenditure values
     *
     * @param   object  &$subject  the object containing subject data
     *
     * @return  void  sets values in the references object
     */
    private function setExpenditureText(&$subject)
    {
        $useFullText = (!empty($subject->creditpoints) AND !empty($subject->expenditure) AND !empty($subject->present));
        if ($useFullText)
        {
            $subject->expenditureOutput = JText::sprintf('COM_THM_ORGANIZER_EXPENDITURE_FULL',
                $subject->creditpoints,
                $subject->expenditure,
                $subject->present
            );
            return;
        }

        $useMediumText = (!empty($subject->creditpoints) AND !empty($subject->expenditure));
        if ($useMediumText)
        {
            $subject->expenditureOutput = JText::sprintf('COM_THM_ORGANIZER_EXPENDITURE_MEDIUM',
                $subject->creditpoints,
                $subject->expenditure
            );
            return;
        }

        if (!empty($subject->creditpoints))
        {
            $subject->expenditureOutput = JText::sprintf('COM_THM_ORGANIZER_EXPENDITURE_SHORT', $subject->creditpoints);
        }
    }

    /**
     * Loads an array of names and links into the subject model for subjects for
     * which this subject is a prerequisite.
     *
     * @param   object  &$subject  the object containing subject data
     *
     * @return void
     */
    private function setTeachers(&$subject)
    {
        $teacherData = THM_OrganizerHelperTeacher::getDataBySubject($subject->id, null, true, false);
        if (empty($teacherData))
        {
            return;
        }

        $executors = array();
        $teachers = array();
        foreach ($teacherData as $teacher)
        {
            $teacher['name'] = THM_OrganizerHelperTeacher::getDefaultName($teacher);

            if ($teacher['teacherResp'] == '1')
            {
                $executors[$teacher['id']] = $teacher;
            }
            else
            {
                $teachers[$teacher['id']] = $teacher;
            }
        }
        $subject->executors = $executors;
        $subject->teachers = $teachers;
    }

    /**
     * Loads an array of names and links into the subject model for subjects for
     * which this subject is a prerequisite.
     *
     * @param   object  &$subject  the object containing subject data
     *
     * @return  void
     */
    private function setPrerequisiteOf(&$subject)
    {
        $menuID = JFactory::getApplication()->input->getInt('Itemid', 0);
        $langTag = THM_CoreHelper::getLanguageShortTag();

        $link = "index.php?option=com_thm_organizer&view=subject_details&languageTag={$langTag}&Itemid={$menuID}&id=";
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $parts = array("'$link'","subjectID");
        $query->select("name_$langTag AS name, " . $query->concatenate($parts, "") . " AS link");
        $query->from('#__thm_organizer_prerequisites AS p');
        $query->innerJoin('#__thm_organizer_subjects AS s ON p.subjectID = s.id');
        $query->where("p.prerequisite = '$subject->id'");
        $query->order('name');
        $dbo->setQuery((string) $query);
        
        try 
        {
            // Can be set to null by this
            $subject->prerequisiteOf = $dbo->loadAssocList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return;
        }
    }
}
