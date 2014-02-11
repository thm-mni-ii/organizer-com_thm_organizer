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
jimport('joomla.application.component.model');
require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'assets' . DS . 'helpers' . DS . 'lsfapi.php';
defined('RESPONSIBLE') OR define('RESPONSIBLE', 1);
defined('TEACHER') OR define('TEACHER', 2);
/**
 * Provides persistence handling for subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelLSFSubject extends JModel
{
    /**
     * Method to import data associated with subjects from LSF
     *
     * @return  bool  true on success, otherwise false
     */
    public function importBatch()
    {
        $subjectIDs = JRequest::getVar('cid', array(), 'post', 'array');
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
        $badEntry = (empty($subject->lsfID) AND empty($subject->externalID)) OR !$entryExists;
        if ($badEntry)
        {
            return false;
        }

        $client = new THM_OrganizerLSFClient;
        $lsfData = !empty($subject->lsfID)?
            $client->getModuleByModulid($subject->lsfID) : $client->getModuleByNrMni($subject->externalID);

        $blocked = strtolower((string) $lsfData->modul->sperrmh) == 'x';
        if ($blocked)
        {
            $subjectModel = JModel::getInstance('subject', 'THM_OrganizerModel');
            return $subjectModel->deleteEntry($subject->id);
        }

        $this->setAttribute($subject, 'externalID', (string) $lsfData->modul->nrmni);
        $this->setAttribute($subject, 'abbreviation_de', (string) $lsfData->modul->kuerzel);
        $this->setAttribute($subject, 'abbreviation_en', (string) $lsfData->modul->kuerzelen, $subject->abbreviation_de);
        $this->setAttribute($subject, 'short_name_de', (string) $lsfData->modul->kurzname);
        $this->setAttribute($subject, 'short_name_en', (string) $lsfData->modul->kurznameen, $subject->short_name_de);
        $this->setAttribute($subject, 'name_de', (string) $lsfData->modul->titelde);
        $this->setAttribute($subject, 'name_en', (string) $lsfData->modul->titelen, $subject->name_de);
        $this->setAttribute($subject, 'pformID', (string) $lsfData->modul->ktxtpform);
        $this->setAttribute($subject, 'proofID', (string) $lsfData->modul->ktextpart, 'P');
        $this->setAttribute($subject, 'instructionLanguage', (string) $lsfData->modul->sprache, 'D');
        $this->setAttribute($subject, 'creditpoints', (string) $lsfData->modul->lp);
        $this->setAttribute($subject, 'expenditure', (string) $lsfData->modul->aufwand);
        $this->setAttribute($subject, 'present', (string) $lsfData->modul->praesenzzeit);
        $this->setAttribute($subject, 'independent', (string) $lsfData->modul->selbstzeit);
        $this->setNullAttribute($subject, 'methodID', (string) $lsfData->modul->verart);
        $this->setAttribute($subject, 'frequencyID', (string) $lsfData->modul->turnus);

        $responsibleSet = $this->setTeachers($subject->id, $lsfData->xpath('//modul/verantwortliche'), RESPONSIBLE);
        if (!$responsibleSet)
        {
            return false;
        }
        $teachersSet = $this->setTeachers($subject->id, $lsfData->xpath('//modul/dozent'), TEACHER);
        if (!$teachersSet)
        {
            return false;
        }

        $this->setDescriptionAttributes($subject, $lsfData->xpath('//modul/kurzbeschr'));
        $this->setExpenditureAttributes($subject, $lsfData->xpath('//modul/arbeitsaufwand'));
        $this->setMethodAttribute($subject, $lsfData->xpath('//modul/lernform'));
        $this->setPrerequisites($subject, $lsfData->xpath('//modul/zwvoraussetzungen'));

        $prerequisitesSaved = $this->savePrerequisitesFromLSF($subject);
        if (!$prerequisitesSaved)
        {
            return false;
        }

        $this->setObjectives($subject, $lsfData->xpath('//modul/lernziel'));
        $this->setContents($subject, $lsfData->xpath('//modul/lerninhalt'));
        $this->setPreliminaries($subject, $lsfData->xpath('//modul/vorleistung'));
        $this->setAttribute($subject, 'literature', $lsfData->modul->litverz);

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
     * Sets the value of a generic attribute if available, otherwise unsets the
     * key
     * 
     * @param   object  &$subject  the array where subject data is being stored
     * @param   string  $key       the key where the value should be put
     * @param   string  $value     the value string
     * 
     * @return  void
     */
    private function setNullAttribute(&$subject, $key, $value)
    {
        $noValue = empty($value) AND empty($subject->$key);
        if ($noValue)
        {
            unset($subject->$key);
        }
        if (!empty($value))
        {
            $subject->$key = $value;
        }
    }

    /**
     * Sets description attributes
     * 
     * @param   object  &$subject      the subject data
     * @param   array   $descriptions  an array of description objects
     * 
     * @return  void
     */
    private function setDescriptionAttributes(&$subject, $descriptions)
    {
        foreach ($descriptions as $description)
        {
            $language = (string) $description->sprache;
            $this->setAttribute($subject, "description_$language", (string) $description->txt);
        }
    }

    /**
     * Sets attributes dealing with required student expenditure
     * 
     * @param   array  &$subject  the subject data
     * @param   array  $elements  the expenditure nodes
     * 
     * @return void
     */
    private function setExpenditureAttributes(&$subject, $elements)
    {
        if (empty($elements))
        {
            return;
        }

        $text = $elements[0];
        $matches = array();
        preg_match_all('/[0-9]+/', $text, $matches, PREG_PATTERN_ORDER);
        if (!empty($matches) AND !empty($matches[0]) AND count($matches[0]) == 3)
        {
            if (empty($subject['creditpoints']))
            {
                $this->setAttribute($subject, 'creditpoints', $matches[0][0]);
            }
            if (empty($subject['expenditure']))
            {
                $this->setAttribute($subject, 'expenditure', $matches[0][1]);
            }
            if (empty($subject['present']))
            {
                $this->setAttribute($subject, 'present', $matches[0][2]);
            }
            if (empty($subject['independent']))
            {
                $this->setAttribute($subject, 'present', $subject['expenditure'] - $subject['present']);
            }
        }
    }

    /**
     * Resolves the text to one of 6 predefined types of lessons
     *
     * @param   array  &$subject  the subject information
     * @param   array  $methods   the method text elements
     *
     * @return  string  a code representing course instruction methods
     */
    private function setMethodAttribute(&$subject, $methods)
    {
        if (empty($methods))
        {
            return '';
        }

        $text = (string) $methods[0]->txt;
        $method = '';
        $isLecture = strpos($text, 'Vorlesung') !== false;
        $isSeminar = strpos($text, 'Seminar') !== false;
        $isProject = strpos($text, 'Praktikum') !== false;
        $isPractice = strpos($text, 'Übung') !== false;
        $lectureSeminar = ($isLecture AND $isSeminar AND !$isProject AND !$isPractice);
        $lectureProject = ($isLecture AND !$isSeminar AND $isProject AND !$isPractice);
        $lecturePractice = ($isLecture AND !$isSeminar AND !$isProject AND $isPractice);
        $lecture = ($isLecture AND !$isSeminar AND !$isProject AND !$isPractice);
        $seminar = (!$isLecture AND $isSeminar AND !$isProject AND !$isPractice);
        $project = (!$isLecture AND !$isSeminar AND $isProject AND !$isPractice);
        if ($lectureSeminar)
        {
            $method .= 'SV';
        }
        if ($lecturePractice)
        {
            $method .= 'VU';
        }
        if ($lectureProject)
        {
            $method .= 'VG';
        }
        if ($lecture)
        {
            $method .= 'V';
        }
        if ($seminar)
        {
            $method .= 'S';
        }
        if ($project)
        {
            $method .= 'P';
        }

        if (!empty($method))
        {
            $this->setAttribute($subject, 'methodID', $method);
        }
    }

    /**
     * Sets the objectives attributes
     * 
     * @param   object  &$subject    the subject data
     * @param   array   $objectives  the subjects language specific objectives
     * 
     * @return  void
     */
    private function setObjectives(&$subject, $objectives)
    {
        foreach ($objectives as $objective)
        {
            $this->setAttribute($subject, "objective_{$objective->sprache}", (string) $objective->txt);
        }
    }

    /**
     * Sets the prerequisite attributes
     * 
     * @param   object  &$subject      the subject data
     * @param   array   $requirements  the subjects language specific requirements
     * 
     * @return  void
     */
    private function setPrerequisites(&$subject, $requirements)
    {
        $prerequisites = array();
        foreach ($requirements as $requirement)
        {
            $text = $this->resolvePrerequisites((string) $requirement->txt, $requirement->sprache, $prerequisites);
            $this->setAttribute($subject, "prerequisites_{$requirement->sprache}", $text);
        }
        $subject->prerequisites = $prerequisites;
    }

    /**
     * Sets the subject's contents attributes
     * 
     * @param   object  &$subject  the subject data
     * @param   array   $contents  the subjects language specific contents
     * 
     * @return  void
     */
    private function setContents(&$subject, $contents)
    {
        foreach ($contents as $content)
        {
            $languageTag = (string) $content->sprache;
            $this->setAttribute($subject, "content_$languageTag", (string) $content->txt);
        }
    }

    /**
     * Sets the subject's contents attributes
     * 
     * @param   object  &$subject       the subject data
     * @param   array   $preliminaries  the subjects language specific preliminaries
     * 
     * @return  void
     */
    private function setPreliminaries(&$subject, $preliminaries)
    {
        foreach ($preliminaries as $preliminary)
        {
            $languageTag = (string) $preliminary->sprache;
            $this->setAttribute($subject, "preliminary_work_$languageTag", (string) $preliminary->txt);
        }
    }

    /**
     * Saves prerequisites imported from LSF
     *
     * @param   object  &$subject  the subject data
     *
     * @return  bool  true if no database errors occured, otherwise false
     */
    private function savePrerequisitesFromLSF(&$subject)
    {
        $deleteQuery = $this->_db->getQuery(true);
        $deleteQuery->delete('#__thm_organizer_prerequisites');
        $deleteQuery->where("subjectID = '$subject->id'");
        $this->_db->setQuery((string) $deleteQuery);
        try
        {
            $this->_db->query();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage($exc->getMessage(), 'error');
            return false;
        }

        if (!empty($subject->prerequisites))
        {
            foreach ($subject->prerequisites as $prerequisite)
            {
                $insertQuery = $this->_db->getQuery(true);
                $insertQuery->insert('#__thm_organizer_prerequisites');
                $insertQuery->columns('subjectID, prerequisite');
                $insertQuery->values("'$subject->id', '$prerequisite'");
                $this->_db->setQuery((string) $insertQuery);
                $success = $this->_db->query();
                if ($success == false)
                {
                    return false;
                }
            }
        }

        unset($subject->prerequisites);
        return true;
    }

    /**
     * Sets the responsible teachers in the association table
     *
     * @param   int    $subjectID       the id of the subject
     * @param   array  $teachers        an array containing the responsible node
     *                                  objects
     * @param   int    $responsibility  the teacher's responsibility for the
     *                                  subject
     *
     * @return  bool  true on success, otherwise false
     */
    private function setTeachers($subjectID, $teachers, $responsibility)
    {
        $subjectModel = JModel::getInstance('subject', 'THM_OrganizerModel');
        $removed = $subjectModel->removeTeachers($subjectID, $responsibility);
        if (!$removed)
        {
            return false;
        }

        if (empty($teachers))
        {
            return true;
        }

        foreach ($teachers as $teacher)
        {
            $teacherData = array();
            $teacherData['surname'] = (string) $teacher->personinfo->nachname;
            if (empty($teacherData['surname']))
            {
                continue;
            }

            $teacherData['forename'] = (string) $teacher->personinfo->vorname;

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
     * Parces the prerequisites text and replaces subject references with links to the subjects
     * 
     * @param   string  $originalText    the original text of the object
     * @param   string  $languageTag     the desired output language
     * @param   array   &$prerequisites  an array containing prerequisite ids
     * 
     * @return  string  the text for the subject's prereuisites
     */
    private function resolvePrerequisites($originalText, $languageTag, &$prerequisites)
    {
        $modules = array();
        $parts = preg_split('[\,|\ ]', $originalText);
        foreach ($parts as $part)
        {
            if (preg_match('/[0-9]+/', $part))
            {
                $moduleLink = $this->getModuleInformation($part, $languageTag, $prerequisites);
                if (!empty($moduleLink))
                {
                    $modules[$part] = $moduleLink;
                }
            }
        }
        if (!empty($modules))
        {
            foreach ($modules AS $number => $link)
            {
                $originalText = str_replace($number, $link, $originalText);
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
     * @return  mixed  html link string on success, otherwise false
     */
    private function getModuleInformation($moduleNumber, $languageTag, &$prerequisites)
    {
        $query = $this->_db->getQuery(true);
        $query->select("id, name_$languageTag AS name");
        $query->from('#__thm_organizer_subjects')->where("externalID = '$moduleNumber'");
        $this->_db->setQuery((string) $query);
        $subjectInfo = $this->_db->loadAssoc();
        if (empty($subjectInfo))
        {
            return false;
        }

        if (!in_array($subjectInfo['id'], $prerequisites))
        {
            $prerequisites[] = $subjectInfo['id'];
        }

        $subjectURL = JURI::root() . 'index.php?option=com_thm_organizer&view=subject_details';
        $subjectURL .= "&languageTag=$languageTag&id={$subjectInfo['id']}";
        
        $itemID = JRequest::getInt('Itemid');
        $subjectURL .= !empty($itemID)? "&Itemid=$itemID" : '';
        $href = JRoute::_($subjectURL);

        return JHtml::link($href, $subjectInfo['name']);
    }
}
