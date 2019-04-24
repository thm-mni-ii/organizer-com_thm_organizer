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

require_once 'departments.php';
require_once 'languages.php';

use THM_OrganizerHelperLanguages as Languages;

/**
 * Provides general functions for program access checks, data retrieval and display.
 */
class THM_OrganizerHelperPrograms
{
    /**
     * Retrieves the table id if existent.
     *
     * @param mixed $program the program object (gpuntisID & name) or string (gpuntisID)
     *
     * @return mixed int id on success, otherwise null
     */
    public static function getID($program)
    {
        $table = \JTable::getInstance('plan_programs', 'thm_organizerTable');

        $gpuntisID = is_string($program) ? $program : $program->gpuntisID;
        $pullData  = ['gpuntisID' => $gpuntisID];
        $exists    = $table->load($pullData);

        if ($exists) {
            return $table->id;
        } elseif (is_string($program)) {
            return null;
        }

        $pullData = ['name' => $program->name];
        $exists   = $table->load($pullData);

        if ($exists) {
            return $table->id;
        }

        return null;
    }

    /**
     * Retrieves the (plan) program name
     *
     * @param int    $programID the table id for the program
     * @param string $type      the type of the id (real or plan)
     *
     * @return string the name of the (plan) program, otherwise empty
     */
    public static function getName($programID, $type)
    {
        $dbo         = \JFactory::getDbo();
        $languageTag = Languages::getShortTag();

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');

        if ($type == 'real') {
            $query->from('#__thm_organizer_programs AS p');
            $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->leftJoin('#__thm_organizer_plan_programs AS ppr ON ppr.programID = p.id');
            $query->where("p.id = '$programID'");
        } else {
            $query->from('#__thm_organizer_plan_programs AS ppr');
            $query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
            $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
            $query->where("ppr.id = '$programID'");
        }

        $dbo->setQuery($query);
        $names = THM_OrganizerHelperComponent::executeQuery('loadAssoc', []);

        return empty($names) ? '' : empty($names['name']) ? $names['ppName'] : $names['name'];
    }

    /**
     * Getter method for schedule programs in database
     *
     * @return array an array of program information
     */
    public static function getPlanPrograms()
    {
        $dbo           = \JFactory::getDbo();
        $languageTag   = Languages::getShortTag();
        $departmentIDs = THM_OrganizerHelperComponent::getInput()->get('departmentIDs', [], 'raw');

        $query     = $dbo->getQuery(true);
        $nameParts = ["p.name_$languageTag", "' ('", 'd.abbreviation', "' '", 'p.version', "')'"];
        $query->select('DISTINCT ppr.id, ppr.name AS ppName, ' . $query->concatenate($nameParts, "") . ' AS name');
        $query->from('#__thm_organizer_plan_programs AS ppr');
        $query->innerJoin('#__thm_organizer_plan_pools AS ppo ON ppo.programID = ppr.id');
        $query->leftJoin('#__thm_organizer_programs AS p ON ppr.programID = p.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');

        if (!empty($departmentIDs)) {
            $query->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = ppr.id');
            $query->where("dr.departmentID IN ($departmentIDs)");
        }

        $query->order('ppName');
        $dbo->setQuery($query);

        return THM_OrganizerHelperComponent::executeQuery('loadAssocList', []);
    }

    /**
     * Attempts to get the plan program's id, creating it if non-existent.
     *
     * @param object $program the program object
     *
     * @return mixed int on success, otherwise null
     */
    public static function getPlanResourceID($program)
    {
        $programID = self::getID($program);
        if (!empty($programID)) {
            return $programID;
        }

        $data              = [];
        $data['gpuntisID'] = $program->gpuntisID;
        $data['name']      = $program->name;
        $plausibleData     = self::getPlausibleData($program->gpuntisID);
        $tempArray         = explode('(', $program->name);
        $tempName          = trim($tempArray[0]);
        $data['programID'] = $plausibleData ? self::getProgramID($plausibleData, $tempName) : null;
        $planResourceTable = \JTable::getInstance('plan_programs', 'thm_organizerTable');
        $success           = $planResourceTable->save($data);

        return $success ? $planResourceTable->id : null;
    }

    /**
     * Determines whether the data conveyed in the gpuntis ID is plausible for finding a real program.
     *
     * @param string $gpuntisID the id used in untis for this program
     *
     * @return array empty if the id is implausible
     */
    private static function getPlausibleData($gpuntisID)
    {
        $container       = [];
        $programPieces   = explode('.', $gpuntisID);
        $plausibleNumber = count($programPieces) === 3;
        if ($plausibleNumber) {
            $plausibleCode    = preg_match('/^[A-Z]+[0-9]*$/', $programPieces[0]);
            $plausibleVersion = (ctype_digit($programPieces[2]) and preg_match('/^[2]{1}[0-9]{3}$/',
                    $programPieces[2]));
            $plausibleDegree  = (ctype_upper($programPieces[1])
                and preg_match('/^[B|M]{1}[A-Z]{1,2}$/', $programPieces[1]));
            if ($plausibleDegree) {
                $degreeTable    = \JTable::getInstance('degrees', 'thm_organizerTable');
                $degreePullData = ['code' => $programPieces[1]];
                $exists         = $degreeTable->load($degreePullData);
                $degreeID       = $exists ? $degreeTable->id : null;
            }
            if ($plausibleCode and !empty($degreeID) and $plausibleVersion) {
                $container['code']     = $programPieces[0];
                $container['degreeID'] = $degreeID;
                $container['version']  = $programPieces[2];
            }
        }

        return $container;
    }

    /**
     * Attempts to get the real program's id, creating the stub if non-existent.
     *
     * @param array  $programData the program data
     * @param string $tempName    the name to be used if no entry already exists
     *
     * @return mixed int on success, otherwise false
     */
    private static function getProgramID($programData, $tempName)
    {
        $programTable = \JTable::getInstance('programs', 'thm_organizerTable');
        $exists       = $programTable->load($programData);
        if ($exists) {
            return $programTable->id;
        }

        $formData                    = THM_OrganizerHelperComponent::getInput()->get('jform', [], 'array');
        $programData['departmentID'] = $formData['departmentID'];
        $programData['name_de']      = $tempName;
        $programData['name_en']      = $tempName;

        require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/program.php';

        $model     = \Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('program', 'THM_OrganizerModel');
        $programID = $model->save($programData);

        return empty($programID) ? null : $programID;
    }
    /**
     * Validates the resource collection node
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$xmlObject     the xml object being validated
     *
     * @return void
     */
    public static function validate(&$scheduleModel, &$xmlObject)
    {
        if (empty($xmlObject->departments)) {
            $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_PROGRAMS_MISSING');

            return;
        }

        $scheduleModel->schedule->degrees = new \stdClass;

        foreach ($xmlObject->departments->children() as $degreeNode) {
            self::validateIndividual($scheduleModel, $degreeNode);
        }
    }

    /**
     * Checks whether program nodes have the expected structure and required information
     *
     * @param object &$scheduleModel the validating schedule model
     * @param object &$programNode   the degree (program/department) node to be validated
     *
     * @return void
     */
    private static function validateIndividual(&$scheduleModel, &$programNode)
    {
        $programID = trim((string)$programNode[0]['id']);
        if (empty($programID)) {
            if (!in_array(\JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING'), $scheduleModel->scheduleErrors)) {
                $scheduleModel->scheduleErrors[] = \JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_ID_MISSING');
            }

            return;
        }

        $programID = str_replace('DP_', '', $programID);

        $programName = (string)$programNode->longname;
        if (!isset($programName)) {
            $scheduleModel->scheduleErrors[]
                = sprintf(\JText::_('COM_THM_ORGANIZER_ERROR_PROGRAM_NAME_MISSING'), $programID);

            return;
        }

        $program            = new \stdClass;
        $program->gpuntisID = $programID;
        $program->name      = $programName;
        $program->id        = THM_OrganizerHelperPrograms::getPlanResourceID($program);
        THM_OrganizerHelperDepartments::setDepartmentResource($program->id, 'programID');

        $scheduleModel->schedule->degrees->$programID = $program;
    }
}
