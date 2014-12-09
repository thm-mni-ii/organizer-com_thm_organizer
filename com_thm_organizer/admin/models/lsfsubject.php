<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelLSFSubject
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');
require_once JPATH_COMPONENT_ADMINISTRATOR . '/assets/helpers/lsfapi.php';
defined('RESPONSIBLE') OR define('RESPONSIBLE', 1);
defined('TEACHER') OR define('TEACHER', 2);
/**
 * Provides persistence handling for subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLSFSubject extends JModelLegacy
{
    /**
     * Method to import data associated with subjects from LSF
     *
     * @return  bool  true on success, otherwise false
     */
    public function importBatch()
    {
        $subjectIDs = JFactory::getApplication()->input->get('cid', array(), 'array');
        $this->_db->transactionStart();
        foreach ($subjectIDs as $subjectID)
        {
            $subjectImported = $this->importSingle($subjectID);
            if (!$subjectImported)
            {
                $this->_db->transactionRollback();
                return false;
            }
        }
        $this->_db->transactionCommit();
        return true;
    }

    /**
     * Creates a subject entry if none exists and imports data to fill it
     *
     * @param   object  &$stub  a simplexml object containing rudimentary subject data
     *
     * @return  boolean true on success, otherwise false
     */
    public function processStub(&$stub)
    {
        $lsfID = (string) (empty($stub->modulid)?  $stub->pordid : $stub->modulid);
        if (empty($lsfID))
        {
            return false;
        }

        $unwanted = !empty($stub->sperrmh) AND strtolower((string) $stub->sperrmh) == 'x';
        if ($unwanted)
        {
            return true;
        }

        $table = JTable::getInstance('subjects', 'thm_organizerTable');
        $table->load(array('lsfID' => $lsfID));
        if (empty($table->id))
        {
            $data = array('lsfID' => $lsfID);
            $stubSaved = $table->save($data);
            if (!$stubSaved)
            {
                return false;
            }
        }

        return $this->importSingle($table->id);
    }

    /**
     * Method to import data associated with a subject from LSF
     *
     * @param   int  $subjectID  the id of the subject entry
     *
     * @return  boolean  true on success, otherwise false
     */
    public function importSingle($subjectID)
    {
        $subject = JTable::getInstance('subjects', 'thm_organizerTable');
        $entryExists = $subject->load($subjectID);
        $badEntry = ((empty($subject->lsfID) AND empty($subject->externalID)) OR !$entryExists);
        if ($badEntry)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_BAD_ENTRY'), 'error');
            return false;
        }

        $client = new THM_OrganizerLSFClient;
        $lsfData = !empty($subject->lsfID)?
            $client->getModuleByModulid($subject->lsfID) : $client->getModuleByNrMni($subject->externalID);

        $blocked = strtolower((string) $lsfData->modul->sperrmh) == 'x';
        if ($blocked)
        {
            $subjectModel = JModelLegacy::getInstance('subject', 'THM_OrganizerModel');
            return $subjectModel->deleteEntry($subject->id);
        }

        return $this->parseAttributes($subject, $lsfData->modul);
    }

    /**
     * Parses the object and sets subject attributes
     * 
     * @param   object  &$subject     the subject table object
     * @param   object  &$dataObject  an object representing the data from the
     *                                LSF response
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function parseAttributes(&$subject, &$dataObject)
    {
        $teachersSet = $this->setTeachers($subject->id, $dataObject);
        if (!$teachersSet)
        {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL'), 'error');
            return false;
        }

        $this->setAttribute($subject, 'hisID', (int) $dataObject->nrhis);
        $this->setAttribute($subject, 'externalID', (string) $dataObject->modulecode);
        $this->setAttribute($subject, 'abbreviation_de', (string) $dataObject->kuerzel);
        $this->setAttribute($subject, 'abbreviation_en', (string) $dataObject->kuerzelen, $subject->abbreviation_de);
        $this->setAttribute($subject, 'short_name_de', (string) $dataObject->kurzname);
        $this->setAttribute($subject, 'short_name_en', (string) $dataObject->kurznameen, $subject->short_name_de);
        $this->setAttribute($subject, 'name_de', (string) $dataObject->titelde);
        $this->setAttribute($subject, 'name_en', (string) $dataObject->titelen, $subject->name_de);
        $this->setAttribute($subject, 'instructionLanguage', (string) $dataObject->sprache, 'D');
        $this->setAttribute($subject, 'frequencyID', (string) $dataObject->turnus);
        foreach ($dataObject->beschreibungen AS $textNode)
        {
            $this->setObjectProperty($subject, $textNode);
        }

        $this->checkProofAndMethod($subject);

        // Attributes that can be set by text or individual fields
        $this->processSpecialFields($dataObject, $subject);

        return $subject->store();
    }

    /**
     * Sets the value of a generic attribute if available
     * 
     * @param   object  &$subject  the array where subject data is being stored
     * @param   string  $key       the key where the value should be put
     * @param   string  $value     the value string
     * @param   string  $default   the default value
     * 
     * @return  void
     */
    private function setAttribute(&$subject, $key, $value, $default = '')
    {
        if (empty($value))
        {
            $subject->$key = empty($subject->$key)?
                $default : $subject->$key;
        }
        else
        {
            $subject->$key = $value;
        }
    }

    /**
     * Sets subject properties according to those of the dynamic lsf properties
     *
     * @param   object  &$subject   the subject object
     * @param   object  &$textNode  the object containing lsf texts
     *
     * @return  void
     */
    private function setObjectProperty(&$subject, &$textNode)
    {
        $shortTag = THM_CoreHelper::getLanguageShortTag();
        $nameAttribute = "name_$shortTag";
        $name = $subject->$nameAttribute;
        $category = (string) $textNode->kategorie;
        $language = (string) $textNode->sprache;
        $text = (string) $textNode->txt;
        switch ($category)
        {
            case 'Creditpoints/Arbeitsaufwand':
                if ($language == 'de' AND !empty($text))
                {
                    $this->setExpendituresFromText($subject, $text);
                }
                break;
            case 'Lehrformen':
                $this->setAttribute($subject, "method_$language", $text);
                break;
            case 'Voraussetzungen für die Vergabe von Creditpoints':
                $this->setAttribute($subject, "proof_$language", $text);
                break;
            case 'Kurzbeschreibung':
                $this->setAttribute($subject, "description_$language", $text);
                break;
            case 'Literatur':
                $this->setAttribute($subject, 'literature', $text);
                break;
            case 'Qualifikations und Lernziele':
                $this->setAttribute($subject, "objective_$language", $text);
                break;
            case 'Inhalt':
                $this->setAttribute($subject, "content_$language", $text);
                break;
            case 'Voraussetzungen':
                $prerequisites = $this->setPrerequisites($subject, $text, $language);
                $prerequisitesSaved = $this->savePrerequisites($subject->id, $prerequisites);
                if (!$prerequisitesSaved)
                {
                    $msg = JText::sprintf('COM_THM_ORGANIZER_MESSAGE_ATTRIBUTE_SAVE_FAIL', $category, $name);
                    JFactory::getApplication()->enqueueMessage($msg, 'warning');
                }
                $this->setAttribute($subject, "content_$language", $text);
                break;
            case 'Verwendbarkeit des Moduls':
                $prerequisites = $this->getPostrequisites($text);
                $postrequisitesSaved = $this->savePostrequisites($subject->id, $prerequisites);
                if (!$postrequisitesSaved)
                {
                    $msg = JText::sprintf('COM_THM_ORGANIZER_MESSAGE_ATTRIBUTE_SAVE_FAIL', $category, $name);
                    JFactory::getApplication()->enqueueMessage($msg, 'warning');
                    break;
                }
                $this->setAttribute($subject, "content_$language", $text);
                break;
            case 'Prüfungsvorleistungen':
                $this->setAttribute($subject, "preliminary_work_$language", $text);
                break;
            case 'Studienhilfsmittel':
                $this->setAttribute($subject, "aids_$language", $text);
                break;
            case 'Bewertung, Note':
                $this->setAttribute($subject, "evaluation_$language", $text);
                break;
            case 'Empfohlene Voraussetzungen':
                $prerequisites = $this->setPrerequisites($subject, $text, $language);
                $prerequisitesSaved = $this->savePrerequisites($subject->id, $prerequisites);
                if (!$prerequisitesSaved)
                {
                    $msg = JText::sprintf('COM_THM_ORGANIZER_MESSAGE_ATTRIBUTE_SAVE_FAIL', $category, $name);
                    JFactory::getApplication()->enqueueMessage($msg, 'warning');
                    break;
                }
                $this->setAttribute($subject, "content_$language", $text);
                break;
            case 'Fachkompetenz':
            case 'Methodenkompetenz':
            case 'Sozialkompetenz':
            case 'Selbstkompetenz':
                $this->setStarAttribute($subject, $category, $text);
                break;
        }
    }

    /**
     * Sets attributes dealing with required student expenditure
     * 
     * @param   object  &$subject  the subject data
     * @param   array   $text      the expenditure text
     * 
     * @return  void
     */
    private function setExpendituresFromText(&$subject, $text)
    {
        $CrPMatch = array();
        preg_match('/(\d) CrP/', (string) $text, $CrPMatch);
        if (!empty($CrPMatch[1]))
        {
            $this->setAttribute($subject, 'creditpoints', $CrPMatch[1]);
        }

        $hoursMatches = array();
        preg_match_all('/(\d+)+ Stunden/', (string) $text, $hoursMatches);
        if (!empty($hoursMatches[1]))
        {
            $this->setAttribute($subject, 'expenditure', $hoursMatches[1][0]);
            if (!empty($hoursMatches[1][1]))
            {
                $this->setAttribute($subject, 'present', $hoursMatches[1][1]);
            }
            if (!empty($hoursMatches[1][2]))
            {
                $this->setAttribute($subject, 'independent', $hoursMatches[1][2]);
            }
        }
    }

    /**
     * Checks whether proof and method values are valid and set, and filling them with values
     * from other languages if possible
     *
     * @param   object  &$subject  the subject object
     *
     * @return  void
     */
    private function checkProofAndMethod(&$subject)
    {
        $unusableProofValue = (empty($subject->proof_en) OR strlen($subject->proof_en) < 4);
        if ($unusableProofValue AND !empty($subject->proof_de))
        {
            $subject->proof_en = $subject->proof_de;
        }

        $unusableMethodValue = (empty($subject->method_en) OR strlen($subject->method_en) < 4);
        if ($unusableMethodValue AND !empty($subject->method_de))
        {
            $subject->proof_en = $subject->proof_de;
        }
    }

    /**
     * Checks for the existence and viability of seldom used fields
     *
     * @param   object  &$dataObject  the data object
     * @param   object  &$subject     the subject object
     *
     * @return  void
     */
    private function processSpecialFields(&$dataObject, &$subject)
    {
        if (!empty($dataObject->lp))
        {
            $this->setAttribute($subject, 'creditpoints', (int) $dataObject->lp);
        }
        if (!empty($dataObject->aufwand))
        {
            $this->setAttribute($subject, 'expenditure', (int) $dataObject->aufwand);
        }
        if (!empty($dataObject->praesenzzeit))
        {
            $this->setAttribute($subject, 'present', (int) $dataObject->praesenzzeit);
        }
        if (!empty($dataObject->selbstzeit))
        {
            $this->setAttribute($subject, 'independent', (int) $dataObject->selbstzeit);
        }
        if (!empty($dataObject->sws))
        {
            $this->setAttribute($subject, 'sws', (int) $dataObject->sws);
        }
    }

    /**
     * Sets the responsible teachers in the association table
     *
     * @param   int     $subjectID    the id of the subject
     * @param   object  &$dataObject  an object containing the lsf response
     *
     * @return  bool  true on success, otherwise false
     */
    private function setTeachers($subjectID, &$dataObject)
    {
        $responsible = $dataObject->xpath('//verantwortliche');
        $teaching = $dataObject->xpath('//dozent');
        if (empty($responsible) AND empty($teaching))
        {
            return true;
        }

        $responsibleSet = $this->setTeachersByResponsibility($subjectID, $responsible, RESPONSIBLE);
        if (!$responsibleSet)
        {
            return false;
        }

        $teachingSet = $this->setTeachersByResponsibility($subjectID, $teaching, TEACHER);
        if (!$teachingSet)
        {
            return false;
        }

        return true;
    }

    /**
     * Sets subject teachers by their responsibility to the subject
     * 
     * @param   int    $subjectID       the subject's id
     * @param   array  &$teachers       an array containing information about the
     *                                  subject's teachers
     * @param   int    $responsibility  the teacher's responsibility level
     * 
     * @return  boolean  true on success, otherwise false
     */
    private function setTeachersByResponsibility($subjectID, &$teachers, $responsibility)
    {
        $subjectModel = JModelLegacy::getInstance('subject', 'THM_OrganizerModel');
        $removed = $subjectModel->removeTeachers($subjectID, $responsibility);
        if (!$removed)
        {
            return false;
        }

        if (empty($teachers))
        {
            return true;
        }

        $surnameAttribute = $responsibility == RESPONSIBLE? 'nachname' : 'personal.nachname';
        $forenameAttribute = $responsibility == RESPONSIBLE? 'vorname' : 'personal.vorname';
        foreach ($teachers as $teacher)
        {
            $teacherData = array();
            $teacherData['surname'] = (string) $teacher->personinfo->$surnameAttribute;
            if (empty($teacherData['surname']))
            {
                continue;
            }

            $teacherData['forename'] = (string) $teacher->personinfo->$forenameAttribute;

            $teacherTable = JTable::getInstance('teachers', 'thm_organizerTable');
            if (!empty($teacher->hgnr))
            {
                $username = (string) $teacher->hgnr;
                $teacherTable->load(array('username' => $username));
                $teacherData['username'] = $username;
            }
            else
            {
                $teacherTable->load($teacherData);
            }

            $teacherSaved = $teacherTable->save($teacherData);
            if (!$teacherSaved)
            {
                return false;
            }

            $added = $subjectModel->addTeacher($subjectID, $teacherTable->id, $responsibility);
            if (!$added)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Sets the prerequisite attributes
     * 
     * @param   object  &$subject  the subject data
     * @param   array   $text      the subjects language specific requirements
     * @param   string  $language  the language tag
     * 
     * @return  array  an array with prerequisites if any were found
     */
    private function setPrerequisites(&$subject, $text, $language)
    {
        $prerequisites = array();
        $text = $this->resolvePrerequisites($text, $language, $prerequisites);
        $this->setAttribute($subject, "prerequisites_$language", $text);
        return $prerequisites;
    }

    /**
     * Parses the prerequisites text and replaces subject references with links to the subjects
     * 
     * @param   string  $originalText    the original text of the object
     * @param   string  $languageTag     the desired output language
     * @param   array   &$prerequisites  an array containing prerequisite ids
     * 
     * @return  string  the text for the subject's prerequisites
     */
    private function resolvePrerequisites($originalText, $languageTag, &$prerequisites)
    {
        $modules = array();
        $parts = preg_split('[\,|\ ]', $originalText);
        foreach ($parts as $part)
        {
            if (preg_match('/[0-9]+/', $part))
            {
                $moduleInformation = $this->getModuleInformation($part, $languageTag, $prerequisites);
                if (!empty($moduleInformation))
                {
                    $modules[$part] = $moduleInformation;
                }
            }
        }
        if (!empty($modules))
        {
            foreach ($modules AS $number => $module)
            {
                $originalText = str_replace($module['name'], '', $originalText);
                $originalText = str_replace($number, $module['link'], $originalText);
            }
        }
        return $originalText;
    }

    /**
     * Builds a link to a subject description if available
     * 
     * @param   string  $moduleNumber    the external id of the subject
     * @param   string  $languageTag     the language tag
     * @param   array   &$prerequisites  an array containing prerequisite ids
     * 
     * @return  array  contains the module name and a link to it, otherwise empty
     */
    private function getModuleInformation($moduleNumber, $languageTag, &$prerequisites)
    {
        $query = $this->_db->getQuery(true);
        $query->select("id, name_$languageTag AS name");
        $query->from('#__thm_organizer_subjects')->where("externalID = '$moduleNumber'");
        $this->_db->setQuery((string) $query);

        $moduleInformation = array();
        try 
        {
            $subjectInfo = $this->_db->loadAssoc();

            if (empty($subjectInfo))
            {
                return $moduleInformation;
            }

            if (!in_array($subjectInfo['id'], $prerequisites))
            {
                $prerequisites[] = $subjectInfo['id'];
            }

            $moduleInformation['name'] = $subjectInfo['name'];

            $subjectURL = JURI::root() . 'index.php?option=com_thm_organizer&view=subject_details';
            $subjectURL .= "&languageTag=$languageTag&id={$subjectInfo['id']}";
            $href = JRoute::_($subjectURL);
            $moduleInformation['link'] = JHtml::link($href, $subjectInfo['name']);

            return $moduleInformation;
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return $moduleInformation;
        }

    }

    /**
     * Saves prerequisites imported from LSF
     *
     * @param   string  $subjectID      the id of the subject
     * @param   array   $prerequisites  an array of prerequisites
     *
     * @return  bool  true if no database errors occured, otherwise false
     */
    private function savePrerequisites($subjectID, $prerequisites)
    {
        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_prerequisites');
        $deleteQuery->where("subjectID = '$subjectID'");
        $this->_db->setQuery((string) $deleteQuery);
        try
        {
            $this->_db->execute();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        if (!empty($prerequisites))
        {
            foreach ($prerequisites as $prerequisite)
            {
                $insertQuery = $this->_db->getQuery(true);
                $insertQuery->insert('#__thm_organizer_prerequisites');
                $insertQuery->columns('subjectID, prerequisite');
                $insertQuery->values("'$subjectID', '$prerequisite'");
                $this->_db->setQuery((string) $insertQuery);
                try
                {
                    $this->_db->execute();
                }
                catch (Exception $exc)
                {
                    JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Sets the prerequisite attributes
     *
     * @param   array  $text  the subjects language specific requirements
     * 
     * @return  array  an array of module id
     */
    private function getPostrequisites($text)
    {
        $postrequisites = array();
        $parts = preg_split('[\,|\ ]', $text);
        foreach ($parts as $part)
        {
            if (preg_match('/[0-9]+/', $part))
            {
                $moduleID = $this->getModuleID(trim(strip_tags($part)));
                if (!empty($moduleID))
                {
                    $postrequisites[$moduleID] = $moduleID;
                }
            }
        }
        return $postrequisites;
    }

    /**
     * Builds a link to a subject description if available
     * 
     * @param   string  $possibleModuleNumber  a possible external id of the subject
     * 
     * @return  mixed  int  subject id on success, otherwise false
     *
     * @throws  exception
     */
    private function getModuleID($possibleModuleNumber)
    {
        $query = $this->_db->getQuery(true);
        $query->select("id");
        $query->from('#__thm_organizer_subjects')->where("externalID = '$possibleModuleNumber'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $moduleID = $this->_db->loadResult();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $moduleID;
    }

    /**
     * Saves the postrequisite relation
     * 
     * @param   int    $subjectID       the id of the subject being imported
     * @param   array  $postrequisites  the id for which this subject is required
     * 
     * @return  boolean  true on success, otherwise false
     *
     * @throws  exception
     */
    private function savePostrequisites($subjectID, $postrequisites)
    {
        if (empty($postrequisites))
        {
            return true;
        }
        foreach ($postrequisites AS $postrequisiteID)
        {
            $checkQuery = $this->_db->getQuery(true);
            $checkQuery->select("COUNT(*)");
            $checkQuery->from('#__thm_organizer_subjects')->where("subjectID = '$postrequisiteID'")->where("prerequisite = '$subjectID'");
            $this->_db->setQuery((string) $checkQuery);
            
            try
            {
                $entryExists = $this->_db->loadResult();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }

            if (!$entryExists)
            {
                $insertQuery = $this->_db->getQuery(true);
                $insertQuery->insert('#__thm_organizer_prerequisites');
                $insertQuery->columns('subjectID, prerequisite');
                $insertQuery->values("'$postrequisiteID', '$subjectID'");
                $this->_db->setQuery((string) $insertQuery);
                try
                {
                    $this->_db->execute();
                }
                catch (Exception $exc)
                {
                    JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Sets business administration department start attributes
     * 
     * @param   object  &$subject   the subject object
     * @param   string  $attribute  the attribute's name in the xml response
     * @param   string  $value      the value set in lsf
     * 
     * @return  void
     */
    private function setStarAttribute(&$subject, $attribute, $value)
    {
        if (!is_numeric($value))
        {
            $value = strlen($value);
        }
        switch ($attribute)
        {
            case 'Fachkompetenz':
                $attributeName = 'expertise';
                break;
            case 'Methodenkompetenz':
                $attributeName = 'method_competence';
                break;
            case 'Sozialkompetenz':
                $attributeName = 'social_competence';
                break;
            case 'Selbstkompetenz':
                $attributeName = 'self_competence';
                break;
        }
        $subject->$attributeName = $value;
    }

    /**
     * Updates all subject descriptions stored in the database
     * 
     * @return  void
     */
    public function updateAll()
    {
        // The execution time is dynamic and higher than most servers are configured to
        ini_set('MAX_EXECUTION_TIME', -1);

        $query = $this->_db->getQuery(true);
        $query->select("DISTINCT id")->from('#__thm_organizer_subjects');
        $this->_db->setQuery((string) $query);
        $subjectIDs = $this->_db->loadColumn();

        if (empty($subjectIDs))
        {
            return;
        }

        $failed = array();
        foreach ($subjectIDs as $subjectID)
        {
            $success = $this->importSingle($subjectID);
            if (!$success)
            {
                $failed[] = $subjectID;
            }
        }

        if (!empty($failed))
        {
            $completeFail = count($failed) == count($subjectIDs);
            if ($completeFail)
            {
                $msgType = 'error';
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL');
            }
            else
            {
                $msgType = 'error';
                $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL_PARTIAL');
            }
            JFactory::getApplication()->enqueueMessage($msg, $msgType);
            return;
        }
        $msg = JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_SUCCESS');
        JFactory::getApplication()->enqueueMessage($msg);
    }
}
