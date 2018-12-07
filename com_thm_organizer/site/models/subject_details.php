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

require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/campuses.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/teachers.php';

/**
 * Class which retrieves subject information for a detailed display of subject attributes.
 */
class THM_OrganizerModelSubject_Details extends \Joomla\CMS\MVC\Model\BaseDatabaseModel
{
    public $lang = null;

    public $subjectID;

    /**
     * Constructor
     *
     * @param   array $config An array of configuration options (name, state, dbo, table_path, ignore_request).
     *
     * @throws  \Exception
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->lang      = THM_OrganizerHelperLanguage::getLanguage();
        $this->subjectID = $this->resolveID();
    }

    /**
     * Loads subject information from the database
     *
     * @return array  subject data on success, otherwise empty
     */
    public function getItem()
    {
        if (empty($this->subjectID)) {
            return [];
        }

        $input   = THM_OrganizerHelperComponent::getInput();
        $langTag = $input->getString('languageTag', THM_OrganizerHelperLanguage::getShortTag());

        $query = $this->_db->getQuery(true);
        $query->select("aids_$langTag AS aids, frequency_$langTag AS availability, campusID AS campus")
            ->select("content_$langTag AS content, creditpoints, departmentID, description_$langTag AS description")
            ->select("duration, evaluation_$langTag AS evaluation, expenditure, expertise, instructionLanguage")
            ->select("literature, method_$langTag AS method, method_competence AS methodCompetence")
            ->select("externalID AS moduleCode, name_$langTag AS name, objective_$langTag AS objective")
            ->select("preliminary_work_$langTag AS preliminaryWork, used_for_$langTag AS prerequisiteFor")
            ->select("prerequisites_$langTag AS prerequisites, proof_$langTag AS proof")
            ->select("recommended_prerequisites_$langTag as recommendedPrerequisites")
            ->select("self_competence AS selfCompetence, short_name_$langTag AS shortName")
            ->select("social_competence AS socialCompetence, sws, present");

        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_frequencies AS f ON s.frequencyID = f.id');
        $query->where("s.id = '$this->subjectID'");
        $this->_db->setQuery($query);

        $result = THM_OrganizerHelperComponent::executeQuery('loadAssoc');

        // This should not occur.
        if (empty($result['name'])) {
            return [];
        }

        $subject = $this->getTemplate();
        foreach ($result as $property => $value) {
            $subject[$property]['value'] = $value;
        }

        $this->setCampus($subject);
        $this->setDependencies($subject);
        $this->setExpenditureText($subject);
        $this->setInstructionLanguage($subject);
        $this->setTeachers($subject);

        if ($subject['shortName']['value'] == $subject['name']['value']) {
            unset($subject['shortName']);
        }

        return $subject;
    }

    /**
     * Returns the language.
     *
     * @return JLanguage|null
     */
    public function getLanguage()
    {
        return $this->lang;
    }

    /**
     * Creates a framework for labeled subject attributes
     *
     * @return array the subject template
     */
    private function getTemplate()
    {

        $option = 'COM_THM_ORGANIZER_';

        $template = [
            'subjectID'                => $this->subjectID,
            'name'                     => ['label' => $this->lang->_($option . 'NAME')],
            'departmentID'             => [],
            'shortName'                => ['label' => $this->lang->_($option . 'SHORT_NAME')],
            'campus'                   => ['label' => $this->lang->_($option . 'CAMPUS')],
            'moduleCode'               => ['label' => $this->lang->_($option . 'MODULE_CODE')],
            'executors'                => ['label' => $this->lang->_($option . 'MODULE_COORDINATOR')],
            'teachers'                 => ['label' => $this->lang->_($option . 'TEACHER')],
            'description'              => ['label' => $this->lang->_($option . 'SHORT_DESCRIPTION')],
            'objective'                => ['label' => $this->lang->_($option . 'OBJECTIVES')],
            'content'                  => ['label' => $this->lang->_($option . 'CONTENT')],
            'expertise'                => ['label' => $this->lang->_($option . 'EXPERTISE')],
            'methodCompetence'         => ['label' => $this->lang->_($option . 'METHOD_COMPETENCE')],
            'socialCompetence'         => ['label' => $this->lang->_($option . 'SOCIAL_COMPETENCE')],
            'selfCompetence'           => ['label' => $this->lang->_($option . 'SELF_COMPETENCE')],
            'duration'                 => ['label' => $this->lang->_($option . 'DURATION')],
            'instructionLanguage'      => ['label' => $this->lang->_($option . 'INSTRUCTION_LANGUAGE')],
            'expenditure'              => ['label' => $this->lang->_($option . 'EXPENDITURE')],
            'sws'                      => ['label' => $this->lang->_($option . 'SWS')],
            'method'                   => ['label' => $this->lang->_($option . 'METHOD')],
            'preliminaryWork'          => ['label' => $this->lang->_($option . 'PRELIMINARY_WORK')],
            'proof'                    => ['label' => $this->lang->_($option . 'PROOF')],
            'evaluation'               => ['label' => $this->lang->_($option . 'EVALUATION')],
            'availability'             => ['label' => $this->lang->_($option . 'AVAILABILITY')],
            'literature'               => ['label' => $this->lang->_($option . 'LITERATURE')],
            'aids'                     => ['label' => $this->lang->_($option . 'STUDY_AIDS')],
            'prerequisites'            => ['label' => $this->lang->_($option . 'PREREQUISITES')],
            'preRequisiteModules'      => ['label' => $this->lang->_($option . 'PREREQUISITE_MODULES')],
            'recommendedPrerequisites' => ['label' => $this->lang->_($option . 'RECOMMENDED_PREREQUISITES')],
            'prerequisiteFor'          => ['label' => $this->lang->_($option . 'PREREQUISITE_FOR')],
            'postRequisiteModules'     => ['label' => $this->lang->_($option . 'POSTREQUISITE_MODULES')],
        ];

        return $template;
    }

    /**
     * Attempts to determine the desired subject id
     *
     * @return mixed  int on success, otherwise null
     */
    private function resolveID()
    {
        $input     = THM_OrganizerHelperComponent::getInput();
        $requestID = $input->getInt('id', 0);

        if (!empty($requestID)) {
            // Ensure that the requested ID is existent in the table
            $query = $this->_db->getQuery(true);
            $query->select('id')->from('#__thm_organizer_subjects')->where("id = '$requestID'");
            $this->_db->setQuery($query);

            return THM_OrganizerHelperComponent::executeQuery('loadResult');
        }

        $externalID = $input->getString('nrmni', '');
        if (empty($externalID)) {
            return null;
        }

        $query = $this->_db->getQuery(true);
        $query->select('id')->from('#__thm_organizer_subjects')->where("externalID = '$externalID'");
        $this->_db->setQuery($query);

        return THM_OrganizerHelperComponent::executeQuery('loadResult');
    }

    private function setCampus(&$subject) {
        if (!empty($subject['campus']['value'])) {
            $campusID = $subject['campus']['value'];
            $subject['campus']['value']     = THM_OrganizerHelperCampuses::getName($campusID);
            $subject['campus']['location'] = THM_OrganizerHelperCampuses::getLocation($campusID);
        } else {
            unset($subject['campus']);
        }
    }

    /**
     * Loads an array of names and links into the subject model for subjects for
     * which this subject is a prerequisite.
     *
     * @param object &$subject the object containing subject data
     *
     * @return void
     */
    private function setDependencies(&$subject)
    {
        $subjectID = $subject['subjectID'];
        $langTag   = THM_OrganizerHelperLanguage::getShortTag();
        $programs  = THM_OrganizerHelperMapping::getSubjectPrograms($subjectID);

        $query  = $this->_db->getQuery(true);
        $select = 'DISTINCT pr.id AS id, ';
        $select .= "s1.id AS preID, s1.name_$langTag AS preName, s1.externalID AS preModuleNumber, ";
        $select .= "s2.id AS postID, s2.name_$langTag AS postName, s2.externalID AS postModuleNumber";
        $query->select($select);
        $query->from('#__thm_organizer_prerequisites AS pr');
        $query->innerJoin('#__thm_organizer_mappings AS m1 ON pr.prerequisite = m1.id');
        $query->innerJoin('#__thm_organizer_subjects AS s1 ON m1.subjectID = s1.id');
        $query->innerJoin('#__thm_organizer_mappings AS m2 ON pr.subjectID = m2.id');
        $query->innerJoin('#__thm_organizer_subjects AS s2 ON m2.subjectID = s2.id');

        foreach ($programs as $programID => $program) {
            $query->clear('where');
            $query->where("m1.lft > {$program['lft']} AND m1.rgt < {$program['rgt']}");
            $query->where("m2.lft > {$program['lft']} AND m2.rgt < {$program['rgt']}");
            $query->where("(s1.id = $subjectID OR s2.id = $subjectID)");
            $this->_db->setQuery($query);

            $dependencies = THM_OrganizerHelperComponent::executeQuery('loadAssocList', [], 'id');
            if (empty($dependencies)) {
                continue;
            }

            $programName = $program['name'];
            foreach ($dependencies as $dependency) {
                if ($dependency['preID'] == $subjectID) {
                    if (empty($subject['postRequisiteModules']['value'])) {
                        $subject['postRequisiteModules']['value'] = [];
                    }
                    if (empty($subject['postRequisiteModules']['value'][$programName])) {
                        $subject['postRequisiteModules']['value'][$programName] = [];
                    }

                    $name = $dependency['postName'];
                    $name .= empty($dependency['postModuleNumber']) ? '' : " ({$dependency['postModuleNumber']})";

                    $subject['postRequisiteModules']['value'][$programName][$dependency['postID']] = $name;
                } else {
                    if (empty($subject['preRequisiteModules']['value'])) {
                        $subject['preRequisiteModules']['value'] = [];
                    }
                    if (empty($subject['preRequisiteModules']['value'][$programName])) {
                        $subject['preRequisiteModules']['value'][$programName] = [];
                    }

                    $name = $dependency['preName'];
                    $name .= empty($dependency['preModuleNumber']) ? '' : " ({$dependency['preModuleNumber']})";

                    $subject['preRequisiteModules']['value'][$programName][$dependency['preID']] = $name;
                }
            }

            if (isset($subject['preRequisiteModules']['value'][$programName])) {
                asort($subject['preRequisiteModules']['value'][$programName]);
            }

            if (isset($subject['postRequisiteModules']['value'][$programName])) {
                asort($subject['postRequisiteModules']['value'][$programName]);
            }
        }
    }

    /**
     * Creates a textual output for the various expenditure values
     *
     * @param object &$subject the object containing subject data
     *
     * @return void  sets values in the references object
     */
    private function setExpenditureText(&$subject)
    {
        // If there are no credit points set, this text is meaningless.
        if (!empty($subject['creditpoints']['value'])) {
            if (empty($subject['expenditure']['value'])) {
                $subject['expenditure']['value'] = sprintf(
                    $this->lang->_('COM_THM_ORGANIZER_EXPENDITURE_SHORT'),
                    $subject['creditpoints']
                );
            } elseif (empty($subject['present']['value'])) {
                $subject['expenditure']['value'] = sprintf(
                    $this->lang->_('COM_THM_ORGANIZER_EXPENDITURE_MEDIUM'),
                    $subject['creditpoints']['value'],
                    $subject['expenditure']['value']
                );
            } else {
                $subject['expenditure']['value'] = sprintf(
                    $this->lang->_('COM_THM_ORGANIZER_EXPENDITURE_FULL'),
                    $subject['creditpoints']['value'],
                    $subject['expenditure']['value'],
                    $subject['present']['value']
                );
            }
        }

        unset($subject['creditpoints'], $subject['present']);
    }

    /**
     * Creates a textual output for the language of instruction
     *
     * @param object &$subject the object containing subject data
     *
     * @return void  sets values in the references object
     */
    private function setInstructionLanguage(&$subject)
    {
        switch ($subject['instructionLanguage']['value']) {
            case 'E':
            case 'e':
                $subject['instructionLanguage']['value'] = $this->lang->_('COM_THM_ORGANIZER_ENGLISH');
                break;
            case 'D':
            case 'd':
            default:
                $subject['instructionLanguage']['value'] = $this->lang->_('COM_THM_ORGANIZER_GERMAN');
        }
    }

    /**
     * Loads an array of names and links into the subject model for subjects for
     * which this subject is a prerequisite.
     *
     * @param object &$subject the object containing subject data
     *
     * @return void
     */
    private function setTeachers(&$subject)
    {
        $teacherData = THM_OrganizerHelperTeachers::getDataBySubject($subject['subjectID'], null, true, false);

        if (empty($teacherData)) {
            return;
        }

        $executors = [];
        $teachers  = [];

        foreach ($teacherData as $teacher) {
            $title    = empty($teacher['title']) ? '' : "{$teacher['title']} ";
            $forename = empty($teacher['forename']) ? '' : "{$teacher['forename']} ";
            $surname  = $teacher['surname'];
            $name     = $title . $forename . $surname;

            if ($teacher['teacherResp'] == '1') {
                $executors[$teacher['id']] = $name;
            } else {
                $teachers[$teacher['id']] = $name;
            }
        }

        if (count($executors)) {
            $subject['executors']['value'] = $executors;
        }

        if (count($teachers)) {
            $subject['teachers']['value'] = $teachers;
        }
    }
}
