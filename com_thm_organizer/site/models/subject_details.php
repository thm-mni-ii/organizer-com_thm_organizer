<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModeldetails
 * @description THM_OrganizerModeldetails component site model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');
require_once JPATH_COMPONENT . '/helper/teacher.php';

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
    public $subjectID = null;

    public $languageTag = null;

    public $subject = null;

    /**
     * Builds the data model of the requested subject
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->menuID = JRequest::getInt('Itemid');
        $this->subjectID = JRequest::getInt('id');
        $externalID = JRequest::getString('nrmni');
        if (empty($this->subjectID) AND !empty($externalID))
        {
            $this->subjectID = $this->resolveExternalID($externalID);
        }
        $this->languageTag = JRequest::getString('languageTag', 'de');

        if (!empty($this->subjectID))
        {
            $this->subject = $this->getSubject();
            if (empty($this->subject))
            {
                return;
            }
            if (!empty($this->subject['creditpoints']))
            {
                $this->subject['expenditureOutput'] = "{$this->subject['creditpoints']} CrP";
                if (!empty($this->subject['expenditure']) AND !empty($this->subject['present']))
                {
                    if ($this->languageTag == 'de')
                    {
                        $this->subject['expenditureOutput'] .= "; {$this->subject['expenditure']} Stunden, ";
                        $this->subject['expenditureOutput'] .= "davon etwa {$this->subject['present']} Stunden Präsenzzeit.";
                    }
                    else
                    {
                        $this->subject['expenditureOutput'] .= "; {$this->subject['expenditure']} hours, ";
                        $this->subject['expenditureOutput'] .= "of which {$this->subject['present']} hours are present in class.";
                    }
                }
            }
            $this->setPrerequisiteOf();
            $this->setTeachers();
        }
    }

    /**
     * Resolves the external id to the internal table id
     *
     * @param   string  $externalID  the external id
     *
     * @return  int  the id of the subject
     */
    private function resolveExternalID($externalID)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')->from('#__thm_organizer_subjects')->where("externalID = '$externalID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $subjectID = $dbo->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SUBJECT_DATA"), 500);
        }
        
        return $subjectID;
    }

    /**
     * Loads subject information from the database
     *
     * @return  array  an array of information about the subject
     */
    private function getSubject()
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = "s.id, externalID, name_$this->languageTag AS name, description_$this->languageTag AS description, ";
        $select .= "objective_$this->languageTag AS objective, content_$this->languageTag AS content, instructionLanguage, ";
        $select .= "preliminary_work_$this->languageTag AS preliminary_work, literature, creditpoints, expenditure, ";
        $select .= "present, independent, proof_$this->languageTag AS proof, frequency_$this->languageTag AS frequency, ";
        $select .= "method_$this->languageTag AS method, pform_$this->languageTag AS pform, ";
        $select .= "prerequisites_$this->languageTag AS prerequisites, aids_$this->languageTag AS aids, ";
        $select .= "evaluation_$this->languageTag AS evaluation, sws, expertise, method_competence, self_competence, social_competence";

        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_frequencies AS f ON s.frequencyID = f.id');
        $query->leftJoin('#__thm_organizer_methods AS m ON s.methodID = m.id');
        $query->leftJoin('#__thm_organizer_proof AS p ON s.proofID = p.id');
        $query->leftJoin('#__thm_organizer_pforms AS form ON s.pformID = form.id');
        $query->where("s.id = '$this->subjectID'");
        $dbo->setQuery((string) $query);
        
        try 
        {
            $subject =  $dbo->loadAssoc();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_SUBJECT_DATA"), 500);
        }
        
        return $subject;
    }

    /**
     * Loads an array of names and links into the subject model for subjects for
     * which this subject is a prerequisite.
     *
     * @return void
     */
    private function setTeachers()
    {
        $teacherData = THM_OrganizerHelperTeacher::getDataBySubject($this->subjectID, null, true);
        if (empty($teacherData))
        {
            return;
        }
        $teachers = array();
        foreach ($teacherData as $teacher)
        {
            $defaultName = THM_OrganizerHelperTeacher::getDefaultName($teacher);
 
            if (!empty($teacher['userID']))
            {
                $teacherName = THM_OrganizerHelperTeacher::getNameFromTHMGroups($teacher['userID']);
                $teacher['link'] = THM_OrganizerHelperTeacher::getLink($teacher['userID'], $teacher['surname']);
            }
            $teacher['name'] = empty($teacherName)? $defaultName : $teacherName;
            if ($teacher['teacherResp'] == '1')
            {
                $teacher['name'] .= $this->languageTag == 'de'? ' (Modulverantwortliche)' : ' (Responsible)';
                $teachers[$teacher['id']] = $teacher;
            }
            elseif (empty($teachers[$teacher['id']]))
            {
                $teachers[$teacher['id']] = $teacher;
            }
        }
        $this->subject['teachers'] = $teachers;
    }

    /**
     * Loads an array of names and links into the subject model for subjects for
     * which this subject is a prerequisite.
     *
     * @return void
     */
    private function setPrerequisiteOf()
    {
        $link = "index.php?option=com_thm_organizer&view=subject_details&languageTag={$this->languageTag}&Itemid={$this->menuID}&id=";
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);        
        $query->select("name_$this->languageTag AS name, " . $query->concatenate(["'$link'","subjectID"],"") . " AS link");
        $query->from('#__thm_organizer_prerequisites AS p');
        $query->innerJoin('#__thm_organizer_subjects AS s ON p.subjectID = s.id');
        $query->where("p.prerequisite = '$this->subjectID'");
        $query->order('name');
        $dbo->setQuery((string) $query);
        
        try 
        {
            $prerequisiteOf = $dbo->loadAssocList();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_EXCEPTION_DATABASE_PREREQUISITES"), 500);
        }

        if (!empty($prerequisiteOf))
        {
            $this->subject['prerequisiteOf'] = $prerequisiteOf;
        }
    }
}
