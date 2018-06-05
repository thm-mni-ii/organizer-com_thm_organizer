<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;

/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/lsf.php';
/** @noinspection PhpIncludeInspection */
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

defined('RESPONSIBLE') or define('RESPONSIBLE', 1);
defined('TEACHER') or define('TEACHER', 2);

/**
 * Class used to import lsf subject data.
 */
class THM_OrganizerModelLSFSubject extends JModelLegacy
{
    private $crp = 0;

    /**
     * Checks whether the text is without content other than subject module numbers and subject name attributes
     *
     * @param string $text              the text to be checked
     * @param array  $checkedAttributes the attributes whose values are to be removed during the search
     * @param array  $modules           the mapped subject information
     *
     * @return bool
     */
    private function checkContents($text, $checkedAttributes, $modules)
    {
        foreach ($checkedAttributes as $checkedAttribute) {
            foreach ($modules as $moduleNr => $mappedSubjects) {
                foreach ($mappedSubjects as $mappedSubject) {
                    if ($checkedAttribute == 'externalID') {
                        $text = str_replace(strtolower($mappedSubject[$checkedAttribute]), '', $text);
                        $text = str_replace(strtoupper($mappedSubject[$checkedAttribute]), '', $text);
                    } else {
                        $text = str_replace($mappedSubject[$checkedAttribute], '', $text);
                    }
                }
            }
        }

        $text = $this->sanitizeText($text);
        $text = trim($text);

        return empty($text);
    }

    /**
     * Checks for subjects with the given possible module number mapped to the same programs
     *
     * @param array $possibleModuleNumbers the possible module numbers used in the attribute text
     * @param array $programs              the programs to which the subject is mapped [id, name, lft, rgt)
     *
     * @return array the subject details for subjects with dependencies
     * @throws Exception
     */
    private function checkForMappedSubjects($possibleModuleNumbers, $programs)
    {
        $select = "s.id AS subjectID, externalID, ";
        $select .= "abbreviation_de, short_name_de, name_de, abbreviation_en, short_name_en, name_en, ";
        $select .= "m.id AS mappingID, m.lft, m.rgt, ";

        $query = $this->_db->getQuery(true);
        $query->from('#__thm_organizer_subjects AS s')->innerJoin('#__thm_organizer_mappings AS m ON m.subjectID = s.id');

        $subjects = [];
        foreach ($possibleModuleNumbers as $possibleModuleNumber) {
            $possibleModuleNumber = strtoupper($possibleModuleNumber);
            if (empty(preg_match('/[A-Z0-9]{3,10}/', $possibleModuleNumber))) {
                continue;
            }

            foreach ($programs as $program) {
                $query->clear('select');
                $query->select($select . "'{$program['id']}' AS programID");

                $query->clear('where');
                $query->where("lft > '{$program['lft']}' AND rgt < '{$program['rgt']}'");
                $query->where("s.externalID = '$possibleModuleNumber'");
                $this->_db->setQuery($query);

                try {
                    $mappedSubjects = $this->_db->loadAssocList('mappingID');
                } catch (Exception $exc) {
                    JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                        'error');

                    continue;
                }

                if (empty($mappedSubjects)) {
                    continue;
                }

                if (empty($subjects[$possibleModuleNumber])) {
                    $subjects[$possibleModuleNumber] = $mappedSubjects;
                } else {
                    $subjects[$possibleModuleNumber] = $subjects[$possibleModuleNumber] + $mappedSubjects;
                }
            }
        }

        return $subjects;
    }

    /**
     * Checks whether proof and method values are valid and set, and filling them with values
     * from other languages if possible
     *
     * @param object &$subject the subject object
     *
     * @return void
     */
    private function checkProofAndMethod(&$subject)
    {
        $unusableProofValue = (empty($subject->proof_en) or strlen($subject->proof_en) < 4);

        if ($unusableProofValue and !empty($subject->proof_de)) {
            $subject->proof_en = $subject->proof_de;
        }

        $unusableMethodValue = (empty($subject->method_en) or strlen($subject->method_en) < 4);

        if ($unusableMethodValue and !empty($subject->method_de)) {
            $subject->method_en = $subject->method_de;
        }
    }

    /**
     * Removes the formatted text tag on a text node
     *
     * @param string $text the xml node as a string
     *
     * @return string  the node without its formatted text shell
     */
    private function cleanText($text)
    {
        // Gets rid of bullshit encoding from copy and paste from word
        $text = str_replace(chr(160), " ", $text);
        $text = str_replace(chr(194) . chr(167), "&sect;", $text);
        $text = str_replace(chr(194), " ", $text);
        $text = str_replace(chr(195) . chr(159), "&szlig;", $text);

        // Remove the formatted text tag
        $text = preg_replace("/<[\/]?[f|F]ormatted[t|T]ext\>/", "", $text);

        // Remove non self closing tags with no content and unwanted self closing tags
        $text = preg_replace("/<((?!br|col|link).)[a-z]*[\s]*\/>/", "", $text);

        // Replace non-blank spaces
        $text = preg_replace("/\&nbsp\;/", " ", $text);

        // Run iterative parsing for nested bullshit.
        do {
            $startText = $text;

            // Replace multiple whitespace characters with a single single space
            $text = preg_replace("/\s+/", " ", $text);

            // Replace non-blank spaces
            $text = preg_replace("/^\s+/", "", $text);

            // Remove leading white space
            $text = preg_replace("/^\s+/", "", $text);

            // Remove trailing white space
            $text = preg_replace("/\s+$/", "", $text);

            // Replace remaining white space with an actual space to prevent errors from weird coding
            $text = preg_replace("/\s$/", " ", $text);

            // Remove white space between closing and opening tags
            $text = preg_replace("/(<\/[^>]+>)\s*(<[^>]*>)/", "$1$2", $text);

            // Remove non-self closing tags containing only white space
            $text = preg_replace("/<[^\/>][^>]*>\s*<\/[^>]+>/", "", $text);
        } while ($text != $startText);

        return $text;
    }

    /**
     * Gets the subjects existing mapping ids for the given program
     *
     * @param array $program   the program being iterated
     * @param int   $subjectID the id of the subject being iterated
     *
     * @return array|mixed the mapping ids for the subject or null if the query failed
     * @throws Exception
     */
    private function getProgramMappings($program, $subjectID)
    {
        $query = $this->_db->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_mappings')
            ->where("subjectID = '$subjectID'")
            ->where("lft > '{$program['lft']}'")
            ->where("rgt < '{$program['rgt']}'");
        $this->_db->setQuery($query);

        try {
            return $this->_db->loadColumn();
        } catch (Exception $exc) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR'), 'error');

            return [];
        }
    }

    /**
     * Method to import data associated with subjects from LSF
     *
     * @return bool  true on success, otherwise false
     * @throws Exception
     */
    public function importBatch()
    {
        $subjectIDs = JFactory::getApplication()->input->get('cid', [], 'array');
        $this->_db->transactionStart();

        foreach ($subjectIDs as $subjectID) {
            $subjectImported = $this->importSingle($subjectID);

            if (!$subjectImported) {
                $this->_db->transactionRollback();

                return false;
            }

            $dependenciesResolved = $this->resolveDependencies($subjectID);

            if (!$dependenciesResolved) {
                $this->_db->transactionRollback();

                return false;
            }
        }

        $this->_db->transactionCommit();

        return true;
    }

    /**
     * Method to import data associated with a subject from LSF
     *
     * @param int $subjectID the id of the subject entry
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    public function importSingle($subjectID)
    {
        $subject = JTable::getInstance('subjects', 'thm_organizerTable');

        $entryExists = $subject->load($subjectID);
        if (!$entryExists) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_BAD_ENTRY'), 'error');

            return false;
        }

        $cantBeImported = (empty($subject->lsfID) and empty($subject->externalID));
        if ($cantBeImported) {
            return true;
        }

        $client  = new THM_OrganizerHelperLSF;
        $lsfData = !empty($subject->lsfID) ?
            $client->getModuleByModulid($subject->lsfID) : $client->getModuleByNrMni($subject->externalID);

        // The system administrator does not wish to display entries with this value
        $blocked      = strtolower((string)$lsfData->modul->sperrmh) == 'x';
        $invalidTitle = THM_OrganizerHelperLSF::invalidTitle($lsfData, true);

        if ($blocked or $invalidTitle) {
            $subjectModel = JModelLegacy::getInstance('subject', 'THM_OrganizerModel');

            return $subjectModel->deleteEntry($subject->id);
        }

        return $this->parseAttributes($subject, $lsfData->modul);
    }

    /**
     * Parses the object and sets subject attributes
     *
     * @param object &$subject    the subject table object
     * @param object &$dataObject an object representing the data from the
     *                            LSF response
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    private function parseAttributes(&$subject, &$dataObject)
    {
        $teachersSet = $this->setTeachers($subject->id, $dataObject);

        if (!$teachersSet) {
            JFactory::getApplication()->enqueueMessage(JText::_('COM_THM_ORGANIZER_MESSAGE_SAVE_FAIL'), 'error');

            return false;
        }

        $this->setAttribute($subject, 'hisID', (int)$dataObject->nrhis);
        $this->setAttribute($subject, 'externalID', (string)$dataObject->modulecode);
        $this->setAttribute($subject, 'abbreviation_de', (string)$dataObject->kuerzel);
        $this->setAttribute($subject, 'abbreviation_en', (string)$dataObject->kuerzelen, $subject->abbreviation_de);
        $this->setAttribute($subject, 'short_name_de', (string)$dataObject->kurzname);
        $this->setAttribute($subject, 'short_name_en', (string)$dataObject->kurznameen, $subject->short_name_de);
        $this->setAttribute($subject, 'name_de', (string)$dataObject->titelde);
        $this->setAttribute($subject, 'name_en', (string)$dataObject->titelen, $subject->name_de);
        $this->setAttribute($subject, 'instructionLanguage', (string)$dataObject->sprache);
        $this->setAttribute($subject, 'frequencyID', (string)$dataObject->turnus);

        $durationExists = preg_match('/\d+/', (string)$dataObject->dauer, $duration);
        $durationValue  = empty($durationExists) ? 1 : $duration[0];
        $this->setAttribute($subject, 'duration', $durationValue, '1');

        // Ensure reset before iterative processing
        $this->crp = 0;

        // Attributes that can be set by text or individual fields
        $this->processSpecialFields($dataObject, $subject);

        $blobs = $dataObject->xpath('//blobs/blob');

        foreach ($blobs as $objectNode) {
            $this->setObjectProperty($subject, $objectNode);
        }

        $this->checkProofAndMethod($subject);

        $success = $subject->store();

        return empty($success) ? false : true;
    }

    /**
     * Checks for the existence and viability of seldom used fields
     *
     * @param object &$dataObject the data object
     * @param object &$subject    the subject object
     *
     * @return void
     */
    private function processSpecialFields(&$dataObject, &$subject)
    {
        if (!empty($dataObject->sws)) {
            $this->setAttribute($subject, 'sws', (int)$dataObject->sws);
        }

        if (empty($dataObject->lp)) {
            $this->crp = 0;
            $this->setAttribute($subject, 'creditpoints', 0);
            $this->setAttribute($subject, 'expenditure', 0);
            $this->setAttribute($subject, 'present', 0);
            $this->setAttribute($subject, 'independent', 0);

            return;
        }

        $crp = (int)$dataObject->lp;

        $this->setAttribute($subject, 'creditpoints', $crp);
        $this->crp = $crp;

        $expenditure = empty($dataObject->aufwand) ? $crp * 30 : (int)$dataObject->aufwand;
        $this->setAttribute($subject, 'expenditure', $expenditure);

        $presenceExists    = !empty($dataObject->praesenzzeit);
        $independentExists = !empty($dataObject->selbstzeit);
        $validSum          = ($presenceExists and $independentExists
            and ((int)$dataObject->praesenzzeit + (int)$dataObject->selbstzeit) == $expenditure);

        if ($validSum) {
            $this->setAttribute($subject, 'present', (int)$dataObject->praesenzzeit);
            $this->setAttribute($subject, 'independent', (int)$dataObject->selbstzeit);

            return;
        }

        // I let required presence time take priority
        if ($presenceExists) {
            $presence    = (int)$dataObject->praesenzzeit;
            $independent = $expenditure - $presence;
            $this->setAttribute($subject, 'present', $presence);
            $this->setAttribute($subject, 'independent', $independent);

            return;
        }

        // I let required presence time take priority
        if ($independentExists) {
            $independent = (int)$dataObject->selbstzeit;
            $presence    = $expenditure - $independent;
            $this->setAttribute($subject, 'present', $presence);
            $this->setAttribute($subject, 'independent', $independent);

            return;
        }

        $this->setAttribute($subject, 'present', 0);
        $this->setAttribute($subject, 'independent', 0);
    }

    /**
     * Creates a subject entry if none exists and imports data to fill it
     *
     * @param object &$stub        a simplexml object containing rudimentary subject data
     * @param int    $departmentID the id of the department to which this data belongs
     *
     * @return boolean true on success, otherwise false
     * @throws Exception
     */
    public function processStub(&$stub, $departmentID)
    {
        $lsfID = (string)(empty($stub->modulid) ? $stub->pordid : $stub->modulid);
        if (empty($lsfID)) {
            return false;
        }

        $table = JTable::getInstance('subjects', 'thm_organizerTable');

        // Attempt to load using the departmentID
        $data = ['lsfID' => $lsfID, 'departmentID' => $departmentID];
        $table->load($data);

        if (empty($table->id)) {
            // Check for a non-migrated row
            $table->load(['lsfID' => $lsfID]);
        }

        $invalidTitle = THM_OrganizerHelperLSF::invalidTitle($stub);
        $blocked = !empty($stub->sperrmh) and strtolower((string)$stub->sperrmh) == 'x';

        // No row was found => create one
        if (empty($table->id) or empty($table->departmentID)) {
            if ($blocked or $invalidTitle) {
                return true;
            }

            $stubSaved = $table->save($data);
            if (!$stubSaved) {
                return false;
            }
        } // Already exists and should no longer be maintained.
        elseif ($blocked or $invalidTitle) {
            $subjectModel = JModelLegacy::getInstance('subject', 'THM_OrganizerModel');

            return $subjectModel->deleteEntry($table->id);
        }

        return $this->importSingle($table->id);
    }

    /**
     * Parses the prerequisites text and replaces subject references with links to the subjects
     *
     * @param string $subjectID the id of the subject being processed
     *
     * @return bool true on success, otherwise false
     * @throws Exception
     */
    public function resolveDependencies($subjectID)
    {
        $subjectTable = JTable::getInstance('subjects', 'thm_organizerTable');
        $exists       = $subjectTable->load($subjectID);

        // Entry doesn't exist. Should not occur.
        if (!$exists) {
            return true;
        }

        $programs = THM_OrganizerHelperMapping::getSubjectPrograms($subjectID);

        // Subject has not yet been mapped to a program. Improbable, but not impossible.
        if (empty($programs)) {
            return true;
        }

        // These have to be in order of potential string length in case the shorter attribute is a real subset of a longer one.
        $checkedAttributes = [
            'externalID',
            'name_de',
            'short_name_de',
            'abbreviation_de',
            'name_en',
            'short_name_en',
            'abbreviation_en'
        ];

        // Flag to be set should one of the attribute texts consist only of module information. => Text should be empty.
        $attributeChanged = false;

        $prerequisiteAttributes = ['prerequisites_de', 'prerequisites_en'];
        $prerequisites          = [];

        foreach ($prerequisiteAttributes as $attribute) {
            $originalText          = $subjectTable->$attribute;
            $sanitizedText         = $this->sanitizeText($originalText);
            $possibleModuleNumbers = preg_split('[\ ]', $sanitizedText);

            $mappedDependencies = $this->checkForMappedSubjects($possibleModuleNumbers, $programs);

            if (!empty($mappedDependencies)) {
                $prerequisites  = $prerequisites + $mappedDependencies;
                $emptyAttribute = $this->checkContents($originalText, $checkedAttributes, $mappedDependencies);

                if ($emptyAttribute) {
                    $subjectTable->$attribute = '';
                    $attributeChanged         = true;
                }
            }
        }

        $prerequisitesSaved = $this->saveDependencies($programs, $subjectID, $prerequisites, 'pre');

        if (!$prerequisitesSaved) {
            return false;
        }

        $postRequisiteAttributes = ['used_for_de', 'used_for_en'];
        $postrequisites          = [];

        foreach ($postRequisiteAttributes as $attribute) {
            $originalText          = $subjectTable->$attribute;
            $sanitizedText         = $this->sanitizeText($originalText);
            $possibleModuleNumbers = preg_split('[\ ]', $sanitizedText);

            $mappedDependencies = $this->checkForMappedSubjects($possibleModuleNumbers, $programs);

            if (!empty($mappedDependencies)) {
                $postrequisites = $postrequisites + $mappedDependencies;
                $emptyAttribute = $this->checkContents($originalText, $checkedAttributes, $mappedDependencies);
                if ($emptyAttribute) {
                    $subjectTable->$attribute = '';
                    $attributeChanged         = true;
                }
            }
        }

        $postrequisitesSaved = $this->saveDependencies($programs, $subjectID, $postrequisites, 'post');

        if (!$postrequisitesSaved) {
            return false;
        }

        if ($attributeChanged) {
            return $subjectTable->store();
        }

        return true;
    }

    /**
     * Saves the dependencies to the prerequisites table
     *
     * @param array  $programs     the programs that the schedule should be associated with
     * @param int    $subjectID    the id of the subject being processed
     * @param array  $dependencies the subject dependencies
     * @param string $type         the type (direction) of dependency: pre|post
     *
     * @return bool
     * @throws Exception
     */
    private function saveDependencies($programs, $subjectID, $dependencies, $type)
    {
        if (empty($dependencies)) {
            return true;
        }

        foreach ($programs as $program) {
            $subjectMappings = $this->getProgramMappings($program, $subjectID);

            $dependencyMappings = [];
            foreach ($dependencies as $moduleNumber => $mappings) {
                foreach ($mappings as $mappingID => $subjectData) {
                    if ($subjectData['programID'] == $program['id']) {
                        $dependencyMappings[$mappingID] = $mappingID;
                    }
                }
            }

            if (empty($dependencyMappings)) {
                continue;
            }

            if ($type == 'pre') {
                $success = $this->savePrerequisites($dependencyMappings, $subjectMappings);
            } else {
                $success = $this->savePrerequisites($subjectMappings, $dependencyMappings);
            }

            if ($success == false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Sets the value of a generic attribute if available
     *
     * @param object &$subject the array where subject data is being stored
     * @param string $key      the key where the value should be put
     * @param string $value    the value string
     * @param string $default  the default value
     *
     * @return void
     */
    private function setAttribute(&$subject, $key, $value, $default = '')
    {
        $subject->$key = empty($value) ? $default : $value;
    }

    /**
     * Sets subject properties according to those of the dynamic lsf properties
     *
     * @param object &$subject    the subject object
     * @param object &$objectNode the object containing lsf texts
     *
     * @return void
     */
    private function setObjectProperty(&$subject, &$objectNode)
    {
        $category = (string)$objectNode->kategorie;

        /**
         * SimpleXML is terrible with mixed content. Since there is no guarantee what a node's format is,
         * this needs to be processed manually.
         */

        // German entries are the standard right now.
        if (empty($objectNode->de->txt)) {
            $germanText  = null;
            $englishText = null;
        } else {
            $rawGermanText = (string)$objectNode->de->txt->FormattedText->asXML();
            $germanText    = $this->cleanText($rawGermanText);

            if (empty($objectNode->en->txt)) {
                $englishText = null;
            } else {
                $rawEnglishText = (string)$objectNode->en->txt->FormattedText->asXML();
                $englishText    = $this->cleanText($rawEnglishText);
            }
        }

        switch ($category) {
            case 'Aufteilung des Arbeitsaufwands':

                // There are int fields handled elsewhere for this hopefully.
                if (empty($this->crp)) {
                    $this->setExpenditures($subject, $germanText);
                }
                break;

            case 'Lehrformen':

                $this->setAttribute($subject, "method_de", $germanText);
                $this->setAttribute($subject, "method_en", $englishText);
                break;

            case 'Voraussetzungen für die Vergabe von Creditpoints':

                $this->setAttribute($subject, "proof_de", $germanText);
                $this->setAttribute($subject, "proof_en", $englishText);
                break;

            case 'Kurzbeschreibung':

                $this->setAttribute($subject, "description_de", $germanText);
                $this->setAttribute($subject, "description_en", $englishText);
                break;

            case 'Literatur':

                // This should never have been implemented with multiple languages
                $litText = empty($germanText) ? $englishText : $germanText;
                $this->setAttribute($subject, 'literature', $litText);
                break;

            case 'Qualifikations und Lernziele':

                $this->setAttribute($subject, "objective_de", $germanText);
                $this->setAttribute($subject, "objective_en", $englishText);
                break;

            case 'Inhalt':

                $this->setAttribute($subject, "content_de", $germanText);
                $this->setAttribute($subject, "content_en", $englishText);
                break;

            case 'Voraussetzungen':

                $this->setAttribute($subject, "prerequisites_de", $germanText);
                $this->setAttribute($subject, "prerequisites_en", $englishText);

                break;

            case 'Empfohlene Voraussetzungen':

                $this->setAttribute($subject, "recommended_prerequisites_de", $germanText);
                $this->setAttribute($subject, "recommended_prerequisites_en", $englishText);

                break;

            case 'Verwendbarkeit des Moduls':

                $this->setAttribute($subject, "used_for_de", $germanText);
                $this->setAttribute($subject, "used_for_en", $englishText);

                break;

            case 'Prüfungsvorleistungen':

                $this->setAttribute($subject, "preliminary_work_de", $germanText);
                $this->setAttribute($subject, "preliminary_work_en", $englishText);
                break;

            case 'Studienhilfsmittel':

                $this->setAttribute($subject, "aids_de", $germanText);
                $this->setAttribute($subject, "aids_en", $englishText);
                break;

            case 'Bewertung, Note':

                $this->setAttribute($subject, "evaluation_de", $germanText);
                $this->setAttribute($subject, "evaluation_en", $englishText);
                break;

            case 'Fachkompetenz':
            case 'Methodenkompetenz':
            case 'Sozialkompetenz':
            case 'Selbstkompetenz':
                $this->setStarAttribute($subject, $category, $germanText);
                break;
        }
    }

    /**
     * Sets attributes dealing with required student expenditure
     *
     * @param object &$subject the subject data
     * @param string $text     the expenditure text
     *
     * @return void
     */
    private function setExpenditures(&$subject, $text)
    {
        $CrPMatch = [];
        preg_match('/(\d) CrP/', (string)$text, $CrPMatch);
        if (!empty($CrPMatch[1])) {
            $this->setAttribute($subject, 'creditpoints', $CrPMatch[1]);
        }

        $hoursMatches = [];
        preg_match_all('/(\d+)+ Stunden/', (string)$text, $hoursMatches);
        if (!empty($hoursMatches[1])) {
            $this->setAttribute($subject, 'expenditure', $hoursMatches[1][0]);
            if (!empty($hoursMatches[1][1])) {
                $this->setAttribute($subject, 'present', $hoursMatches[1][1]);
            }

            if (!empty($hoursMatches[1][2])) {
                $this->setAttribute($subject, 'independent', $hoursMatches[1][2]);
            }
        }
    }

    /**
     * Sets the responsible teachers in the association table
     *
     * @param int    $subjectID   the id of the subject
     * @param object &$dataObject an object containing the lsf response
     *
     * @return bool  true on success, otherwise false
     * @throws Exception
     */
    private function setTeachers($subjectID, &$dataObject)
    {
        $responsible = $dataObject->xpath('//verantwortliche');
        $teaching    = $dataObject->xpath('//dozent');

        if (empty($responsible) and empty($teaching)) {
            return true;
        }

        $responsibleSet = $this->setTeachersByResponsibility($subjectID, $responsible, RESPONSIBLE);
        if (!$responsibleSet) {
            return false;
        }

        $teachingSet = $this->setTeachersByResponsibility($subjectID, $teaching, TEACHER);
        if (!$teachingSet) {
            return false;
        }

        return true;
    }

    /**
     * Sets subject teachers by their responsibility to the subject
     *
     * @param int   $subjectID      the subject's id
     * @param array &$teachers      an array containing information about the
     *                              subject's teachers
     * @param int   $responsibility the teacher's responsibility level
     *
     * @return boolean  true on success, otherwise false
     * @throws Exception
     */
    private function setTeachersByResponsibility($subjectID, &$teachers, $responsibility)
    {
        $subjectModel = JModelLegacy::getInstance('subject', 'THM_OrganizerModel');
        $removed      = $subjectModel->removeTeachers($subjectID, $responsibility);

        if (!$removed) {
            return false;
        }

        if (empty($teachers)) {
            return true;
        }

        $surnameAttribute  = $responsibility == RESPONSIBLE ? 'nachname' : 'personal.nachname';
        $forenameAttribute = $responsibility == RESPONSIBLE ? 'vorname' : 'personal.vorname';

        foreach ($teachers as $teacher) {
            $teacherData             = [];
            $teacherData['surname']  = trim((string)$teacher->personinfo->$surnameAttribute);
            $teacherData['username'] = trim((string)$teacher->hgnr);

            if (empty($teacherData['surname']) or empty($teacherData['username'])) {
                continue;
            }

            $loadCriteria            = [];
            $loadCriteria[]          = ['username' => $teacherData['username']];
            $teacherData['forename'] = (string)$teacher->personinfo->$forenameAttribute;

            if (!empty($teacherData['forename'])) {
                $loadCriteria[] = ['surname' => $teacherData['surname'], 'forename' => $teacherData['forename']];
            }

            $teacherTable = JTable::getInstance('teachers', 'thm_organizerTable');
            $loaded       = false;

            foreach ($loadCriteria as $criteria) {
                try {
                    $success = $teacherTable->load($criteria);
                } catch (Exception $exc) {
                    JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                        'error');

                    return false;
                }

                if ($success) {
                    $loaded = true;
                    break;
                }
            }

            if (!$loaded) {
                $teacherSaved = $teacherTable->save($teacherData);
                if (!$teacherSaved) {
                    return false;
                }
            }

            $added = $subjectModel->addTeacher($subjectID, $teacherTable->id, $responsibility);
            if (!$added) {
                return false;
            }
        }

        return true;
    }

    /**
     * Saves the prerequisite relation.
     *
     * @param array $prerequisiteMappings the mappings for the prerequiste subject for the program
     * @param array $subjectMappings      the mappings for the subject for the program
     *
     * @return bool true on success otherwise false
     * @throws Exception
     */
    private function savePrerequisites($prerequisiteMappings, $subjectMappings)
    {
        foreach ($prerequisiteMappings as $prerequisiteID) {
            foreach ($subjectMappings as $subjectID) {
                $checkQuery = $this->_db->getQuery(true);
                $checkQuery->select("COUNT(*)");
                $checkQuery->from('#__thm_organizer_prerequisites')
                    ->where("prerequisite = '$prerequisiteID'")
                    ->where("subjectID = '$subjectID'");
                $this->_db->setQuery($checkQuery);

                try {
                    $entryExists = $this->_db->loadResult();
                } catch (Exception $exc) {
                    JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                        'error');

                    return false;
                }

                if (!$entryExists) {
                    $insertQuery = $this->_db->getQuery(true);
                    $insertQuery->insert('#__thm_organizer_prerequisites');
                    $insertQuery->columns('prerequisite, subjectID');
                    $insertQuery->values("'$prerequisiteID', '$subjectID'");
                    $this->_db->setQuery($insertQuery);
                    try {
                        $this->_db->execute();
                    } catch (Exception $exc) {
                        JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"),
                            'error');

                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Sanitizes text for more consistent processing
     *
     * @param string $text the text to be processed
     *
     * @return mixed|string
     */
    private function sanitizeText($text)
    {
        // Get rid of HTML
        $text = preg_replace("/<.*?>/", " ", $text);

        // Remove punctuation
        $text = preg_replace("/[\!\"§\$\%\&\/\(\)\=\?\`\,]/", " ", $text);
        $text = preg_replace("/[\{\}\[\]\\\´\+\*\~\#\'\<\>\|\;\.\:\-\_]/", " ", $text);

        // Remove excess white space
        $text = trim($text);
        $text = preg_replace("/\s+/", " ", $text);

        return $text;
    }

    /**
     * Sets business administration department start attributes
     *
     * @param object &$subject  the subject object
     * @param string $attribute the attribute's name in the xml response
     * @param string $value     the value set in lsf
     *
     * @return void
     */
    private function setStarAttribute(&$subject, $attribute, $value)
    {
        switch ($attribute) {
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

        if ($value === '' or $value === null) {
            $subject->$attributeName = null;
        } elseif (!is_numeric($value)) {
            $value = strlen($value);
        }

        $subject->$attributeName = $value;
    }
}
