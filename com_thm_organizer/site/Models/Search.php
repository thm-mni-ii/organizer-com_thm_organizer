<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

defined('_JEXEC') or die;

use Organizer\Helpers\Departments;
use Organizer\Helpers\Pools;
use Organizer\Helpers\Programs;
use Organizer\Helpers\Subjects;
use Organizer\Helpers\Teachers;
use Organizer\Helpers\OrganizerHelper;

/**
 * Class searches THM Organizer resources for resources and views relevant to the given search query.
 */
class Search extends BaseModel
{
    private $schedDepts;

    public $languageTag;

    private $programResults;

    public $results;

    private $teacherID;

    private $terms;

    /**
     * Aggregates inclusive conditions into one 'where' clause
     *
     * @param object &$query      the query object
     * @param array   $conditions the conditions to be added to the query
     *
     * @return void modifies the query
     */
    private function addInclusiveConditions(&$query, $conditions)
    {
        $query->where('(' . implode(' OR ', $conditions) . ')');

        return;
    }

    /**
     * Iterates through the various match strengths and removes redundant entries in weaker strengths.
     *
     * @return void modifies the $results property
     */
    private function cleanResults()
    {
        $strengths = array_keys($this->results);

        foreach ($strengths as $outerStrength) {
            $osResults = $this->results[$outerStrength];

            foreach ($osResults as $resource => $rResults) {
                foreach (array_keys($rResults) as $resultID) {
                    foreach ($strengths as $innerStrength) {
                        if ($outerStrength == $innerStrength) {
                            continue;
                        }

                        if (!empty($this->results[$innerStrength][$resource])
                            and !empty($this->results[$innerStrength][$resource][$resultID])) {
                            unset($this->results[$innerStrength][$resource][$resultID]);

                            // Check if there is nothing left to avoid unnecessary iteration in the output
                            if (empty($this->results[$innerStrength][$resource])) {
                                unset($this->results[$innerStrength][$resource]);
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->results as $strength => $sResults) {
            foreach ($sResults as $resource => $rResults) {
                usort($this->results[$strength][$resource], ['THM_OrganizerModelSearch', 'sortItems']);
            }
        }
    }

    /**
     * Filters lessons according to status and planning period
     *
     * @param object &$query        the query object to filter
     * @param int     $planPeriodID the id of the planning period for lesson results
     *
     * @return void modifies the query
     */
    private function filterLessons(&$query, $planPeriodID = null)
    {
        $query->where("(ls.delta IS NULL OR ls.delta != 'removed')")
            ->where("(l.delta IS NULL OR l.delta != 'removed')");

        if (!empty($planPeriodID) and is_int($planPeriodID)) {
            $query->where("l.planningPeriodID = '$planPeriodID'");
        }

        return;
    }

    /**
     * Finds degrees which can be associated with the terms. Possible return strengths exact and strong.
     *
     * @param array $terms the search terms
     *
     * @return array an array of degreeIDs, grouped by strength
     */
    private function getDegrees($terms)
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')
            ->select("REPLACE(LOWER(abbreviation), '.', '') AS stdAbbr")
            ->from('#__thm_organizer_degrees');
        $this->_db->setQuery($query);

        $degrees = OrganizerHelper::executeQuery('loadAssocList', [], 'id');

        // Abbreviation or (title and type) matched
        $exactMatches = [];

        // Title or type matched
        $strongMatches = [];

        foreach ($degrees as $degreeID => $degree) {
            $key = array_search($degree['stdAbbr'], $terms);

            $nameParts = explode(' of ', $degree['name']);
            $title     = strtolower(array_shift($nameParts));
            $subject   = strtolower(implode(' of ', $nameParts));

            $titleFoundAt   = array_search($title, $terms);
            $subjectFoundAt = array_search($subject, $terms);

            $exactMatch = ($key !== false or ($titleFoundAt !== false and $subjectFoundAt !== false));

            if ($exactMatch) {
                // The abbreviated degree name only has relevance here, and can create false positives elsewhere => delete
                if ($key !== false) {
                    unset($this->terms[$key]);
                }

                $exactMatches[$degreeID] = $degree;
            } elseif ($subjectFoundAt !== false or $titleFoundAt !== false) {
                $strongMatches[$degreeID] = $degree;
            }
        }

        return ['exact' => $exactMatches, 'strong' => $strongMatches];
    }

    /**
     * Searches for Organizer resources and creates links to relevant views
     *
     * @return array the results grouped by match strength
     */
    public function getResults()
    {
        $this->teacherID  = Teachers::getIDByUserID();
        $this->schedDepts = Access::getAccessibleDepartments('schedule');

        /**
         * Exact     => exact match for the whole search independent of capitalization
         * Strong    => exact match on one of the search terms
         * Good      => similar to one or more of the search terms
         * Related   => matches via a relation with an exact/partial/strong match
         * Mentioned => one or more of the terms is a part of the extended text for the resource
         */
        $this->results     = ['exact' => [], 'strong' => [], 'good' => [], 'related' => [], 'mentioned' => []];
        $this->languageTag = Languages::getShortTag();

        $input     = OrganizerHelper::getInput();
        $rawSearch = trim($input->getString('search', ''));

        // New call or a hard reset
        if ($rawSearch === '') {
            return $this->results;
        }

        $this->setTerms($rawSearch);

        // Programs are searched for initially and set as an object property for use by departments, pools and programs
        $this->setPrograms();

        // Ordered by what I imagine their relative search frequency will be
        $this->searchSubjects();
        $this->searchPools();
        $this->searchPrograms();
        $this->searchTeachers();
        $this->searchRooms();
        $this->searchDepartments();

        $this->cleanResults();

        return $this->results;
    }

    /**
     * Checks for room types which match the the capacity and unresolvable terms.
     *
     * @param array $misc     an array of terms which could not be resolved
     * @param int   $capacity the requested capacity
     *
     * @return array the room type ids which matched the criteria
     */
    private function getRoomTypes(&$misc, $capacity = 0)
    {
        if (empty($misc) and empty($capacity)) {
            return [];
        }

        $query = $this->_db->getQuery(true);
        $query->select('id')->from('#__thm_organizer_room_types');

        $typeIDs        = [];
        $standardClause = "(name_de LIKE '%XXX%' OR name_en LIKE '%XXX%' ";
        $standardClause .= "OR description_de LIKE '%XXX%' OR description_en LIKE '%XXX%')";

        if (!empty($misc)) {
            foreach ($misc as $key => $term) {
                $query->clear('where');
                if (!empty($capacity)) {
                    // Opens conjunctive clause and cap from type
                    $query->where("(min_capacity IS NULL OR min_capacity = '0' OR min_capacity <= '$capacity')");
                    $query->where("(max_capacity IS NULL OR max_capacity = '0' OR max_capacity >= '$capacity')");
                }

                $tempClause = str_replace('XXX', $term, $standardClause);
                $query->where($tempClause);
                $this->_db->setQuery($query);
                $typeResults = OrganizerHelper::executeQuery('loadColumn', []);

                if (!empty($typeResults)) {
                    unset($misc[$key]);
                    $typeIDs = array_merge($typeIDs, $typeResults);
                }

            }
        } elseif (!empty($capacity)) {
            $query->where("(min_capacity IS NULL OR min_capacity = '0' OR min_capacity <= '$capacity')");
            $query->where("(max_capacity IS NULL OR max_capacity = '0' OR max_capacity >= '$capacity')");

            // One must have a legitimate value for this to have meaning.
            $query->where("((min_capacity IS NOT NULL AND min_capacity > '0') OR (max_capacity IS NOT NULL AND max_capacity > '0'))");

            $this->_db->setQuery($query);

            $typeResults = OrganizerHelper::executeQuery('loadColumn', []);

            if (!empty($typeResults)) {
                $typeIDs = array_merge($typeIDs, $typeResults);
            }
        }

        return array_unique($typeIDs);
    }

    /**
     * Processes department/organization results into a standardized array for output
     *
     * @param array $results the department results
     *
     * @return array modifies the results property
     */
    private function processDepartments($results)
    {
        $departments = [];

        if (!empty($results)) {
            foreach ($results as $departmentID) {
                $departmentName = Departments::getName($departmentID);

                $departments[$departmentID] = [];
                $departments[$departmentID]['text'] = Languages::_('THM_ORGANIZER_DEPARTMENT') . ": {$departmentName}";

                $links['schedule']   = "?option=com_thm_organizer&view=schedule_grid&departmentIDs=$departmentID";
                $links['event_list'] = "?option=com_thm_organizer&view=event_list&departmentIDs=$departmentID";

                $departments[$departmentID]['links'] = $links;
            }
        }

        return $departments;
    }

    /**
     * Processes pool results into a standardized array for output
     *
     * @param array  &$pools   the array that the pools are to be stored in
     * @param array   $results the pool id results
     * @param string  $type    the type of pool ids being processed
     *
     * @return mixed
     */
    private function processPools(&$pools, $results, $type)
    {
        foreach ($results as $result) {
            if ($type == 'real') {
                $index = "d{$result['id']}";
                $text  = Pools::getName($result['id'], 'real');
                $links = ['subject_manager' => "?option=com_thm_organizer&view=subject_manager&poolIDs={$result['id']}"];
            } else {
                $index               = "p{$result['id']}";
                $text                = Pools::getName($result['id'], 'plan');
                $links['schedule']   = "?option=com_thm_organizer&view=schedule_grid&poolIDs={$result['id']}";
                $links['event_list'] = "?option=com_thm_organizer&view=event_list&planPoolIDs={$result['id']}";
            }

            $pools[$index]          = [];
            $pools[$index]['text']  = Languages::_('THM_ORGANIZER_POOL') . ": {$result['program']}, $text";
            $pools[$index]['links'] = $links;
        }

        return $pools;
    }

    /**
     * Processes (plan) program results into a standardized array for output
     *
     * @param array $pResults  the program documentation results
     * @param array $ppResults the program planning results lesson results
     *
     * @return array $programs
     */
    private function processPrograms($pResults, $ppResults)
    {
        $programs = [];

        if (!empty($pResults)) {
            foreach ($pResults as $program) {
                $invalidMapping = (empty($program['lft']) or empty($program['rgt']) or $program['rgt'] - $program['lft'] < 2);
                $noPlan         = empty($program['ppID']);

                // Any linked view would be empty
                if ($invalidMapping and $noPlan) {
                    continue;
                }

                $programID = "d{$program['id']}";

                $programs[$programID]               = [];
                $programs[$programID]['programID']  = $program['id'];
                $programs[$programID]['pProgramID'] = $program['ppID'];
                $programs[$programID]['lft']        = $program['lft'];
                $programs[$programID]['rgt']        = $program['rgt'];

                $text                         = Programs::getName($program['id'], 'real');
                $programs[$programID]['name'] = $text;

                $programs[$programID]['text'] = Languages::_('THM_ORGANIZER_PROGRAM') . ": $text";

                $links = [];

                $invalidMapping = (empty($program['lft']) or empty($program['rgt']) or $program['rgt'] - $program['lft'] < 2);

                // If the mapping is invalid only an empty data set would be displayed for subject list and curriculum
                if (!$invalidMapping) {
                    $links['subject_manager'] = "?option=com_thm_organizer&view=subject_manager&programIDs={$program['id']}";
                    $links['curriculum']   = "?option=com_thm_organizer&view=curriculum&programIDs={$program['id']}";
                }

                if (!$noPlan) {
                    $links['schedule']   = "?option=com_thm_organizer&view=schedule_grid&programIDs={$program['ppID']}";
                    $links['event_list'] = "?option=com_thm_organizer&view=event_list&planProgramIDs={$program['ppID']}";
                }

                $programs[$programID]['links'] = $links;
            }
        }

        if (!empty($ppResults)) {
            foreach ($ppResults as $program) {
                $planID        = "p{$program['ppID']}";
                $scheduleLink  = "?option=com_thm_organizer&view=schedule_grid&programIDs={$program['ppID']}";
                $eventlistLink = "?option=com_thm_organizer&view=event_list&planProgramIDs={$program['ppID']}";

                // Subject was found
                if (!empty($program['id'])) {
                    $programID = "d{$program['id']}";

                    // No redundant subject entries
                    if (!empty($programID) and !empty($programs[$programID])) {
                        $programs[$programID]['pProgramID']        = $program['ppID'];
                        $programs[$programID]['links']['schedule'] = $scheduleLink;

                        continue;
                    }
                }

                $programs[$planID]               = [];
                $programs[$planID]['pProgramID'] = $program['ppID'];

                $text                      = Programs::getName($program['ppID'], 'plan');
                $programs[$planID]['name'] = $text;
                $programs[$planID]['text'] = Languages::_('THM_ORGANIZER_PROGRAM') . ": $text";

                $links = [];

                $invalidMapping = (empty($program['lft']) or empty($program['rgt']) or $program['rgt'] - $program['lft'] < 2);

                if (!$invalidMapping) {
                    $programs[$planID]['programID'] = $program['id'];
                    $links['subject_manager']          = "?option=com_thm_organizer&view=subject_manager&programIDs={$program['id']}";
                    $links['curriculum']            = "?option=com_thm_organizer&view=curriculum&programIDs={$program['id']}";
                }

                $links['schedule']          = $scheduleLink;
                $links['event_list']        = $eventlistLink;
                $programs[$planID]['links'] = $links;
            }
        }

        return $programs;
    }

    /**
     * Processes room results into a standardized array for output
     *
     * @param array &$results the room results
     *
     * @return array of formatted room results
     */
    private function processRooms($results)
    {
        $rooms = [];

        if (!empty($results)) {
            foreach ($results as $room) {
                $roomID         = $room['id'];
                $rooms[$roomID] = [];

                $rooms[$roomID]['text'] = Languages::_('THM_ORGANIZER_ROOM') . ": {$room['name']}";

                $description = empty($room['description']) ? $room['type'] : $room['description'];

                if (empty($room['capacity'])) {
                    $capacity = '';
                } else {
                    $capacity = ' (~' . $room['capacity'] . ' ' . Languages::_('THM_ORGANIZER_SEATS') . ')';
                }

                $rooms[$roomID]['description'] = "$description$capacity";

                $rooms[$roomID]['links'] = ['schedule' => "?option=com_thm_organizer&view=schedule_grid&roomIDs={$room['id']}"];
            }
        }

        return $rooms;
    }

    /**
     * Processes subject/lesson results into a standardized array for output
     *
     * @param array $sResults  the subject documentation results
     * @param array $psResults the subject lesson results
     *
     * @return array $subjects
     */
    private function processSubjects($sResults, $psResults)
    {
        $subjects = [];

        if (!empty($sResults)) {
            foreach ($sResults as $sID => $subject) {
                $subjectID = "s$sID";

                $subjects[$subjectID] = [];

                $text = Subjects::getName($sID, 'real', true);

                $subjects[$subjectID]['text'] = Languages::_('THM_ORGANIZER_SUBJECT') . ": $text";

                $links = [];

                $links['subject_details'] = "?option=com_thm_organizer&view=subject_details&id=$sID";

                if (!empty($subject['psID'])) {
                    $links['schedule'] = "?option=com_thm_organizer&view=schedule_grid&subjectIDs={$subject['psID']}";
                }

                $subjects[$subjectID]['links']       = $links;
                $subjects[$subjectID]['description'] = Subjects::getPrograms($sID, 'real');
            }
        }

        if (!empty($psResults)) {
            foreach ($psResults as $pID => $plan) {
                $planID           = "p$pID";
                $scheduleLink     = "?option=com_thm_organizer&view=schedule_grid&subjectIDs=$pID";
                $scheduleListLink = "?option=com_thm_organizer&view=event_list&subjectIDs=$pID";

                // Subject was found
                if (!empty($plan['sID'])) {
                    $subjectID = "s{$plan['sID']}";

                    // No redundant subject entries
                    if (!empty($subjects[$subjectID])) {
                        if (empty($subjects[$subjectID]['links']['schedule'])) {
                            $subjects[$subjectID]['links']['schedule']   = $scheduleLink;
                            $subjects[$subjectID]['links']['event_list'] = $scheduleListLink;
                        }

                        continue;
                    }
                }

                $subjects[$planID] = [];

                $text = Subjects::getName($pID, 'plan', true);

                $subjects[$planID]['text'] = Languages::_('THM_ORGANIZER_SUBJECT') . ": $text";

                $links = [];

                if (!empty($plan['sID'])) {
                    $links['subject_details'] = "?option=com_thm_organizer&view=subject_details&id={$plan['sID']}";
                }

                $links['schedule']                = $scheduleLink;
                $links['event_list']              = $scheduleListLink;
                $subjects[$planID]['links']       = $links;
                $subjects[$planID]['description'] = Subjects::getPrograms($pID, 'plan');
            }
        }

        return $subjects;
    }

    /**
     * Processes teacher results into a standardized array for output
     *
     * @param array $results the teacher results
     *
     * @return array $teachers
     */
    private function processTeachers($results)
    {
        $teachers = [];

        if (!empty($results)) {
            foreach ($results as $teacher) {
                $documented = Teachers::teaches('subject', $teacher['id']);
                $teaches    = Teachers::teaches('lesson', $teacher['id']);

                // Nothing to link
                if (!$documented and !$teaches) {
                    continue;
                }

                $teacherName = Teachers::getDefaultName($teacher['id']);

                $teachers[$teacher['id']]         = [];
                $teachers[$teacher['id']]['text'] = Languages::_('THM_ORGANIZER_TEACHER') . ": {$teacherName}";

                $links = [];

                if ($documented) {
                    $links['subject_manager'] = "?option=com_thm_organizer&view=subject_manager&teacherIDs={$teacher['id']}";
                }

                $overlap = array_intersect(
                    $this->schedDepts,
                    Teachers::getDepartmentIDs($teacher['id'])
                );

                $isTeacher = $this->teacherID == $teacher['id'];
                if ($teaches and (count($overlap) or $isTeacher)) {
                    $links['schedule'] = "?option=com_thm_organizer&view=schedule_grid&teacherIDs={$teacher['id']}";
                }

                $teachers[$teacher['id']]['links'] = $links;
            }
        }

        return $teachers;
    }

    /**
     * Retrieves prioritized department search results
     *
     * @return void adds to the results property
     */
    private function searchDepartments()
    {
        $eWherray = [];
        $sWherray = [];

        foreach ($this->terms as $term) {
            if (is_numeric($term)) {
                $clause     = "name_de LIKE '$term %' OR name_en LIKE '$term %' ";
                $clause     .= "OR short_name_de LIKE '$term %' OR short_name_en LIKE '$term %'";
                $eWherray[] = $clause;
                $sWherray[] = $clause;
            } elseif (strlen($term) < 4) {
                $eClause    = "short_name_de LIKE '%$term' OR short_name_en LIKE '%$term'";
                $eWherray[] = $eClause;
                $sClause    = "short_name_de LIKE '%$term%' OR short_name_en LIKE '%$term%'";
                $sWherray[] = $sClause;
            } else {
                $eClause    = "short_name_de LIKE '%$term' OR short_name_en LIKE '%$term'";
                $eClause    .= " OR name_de LIKE '%$term' OR short_name_en LIKE '%$term'";
                $eWherray[] = $eClause;
                $sClause    = "short_name_de LIKE '%$term%' OR short_name_en LIKE '%$term%'";
                $sClause    .= " OR name_de LIKE '%$term%' OR short_name_en LIKE '%$term%'";
                $sWherray[] = $sClause;
            }
        }

        $query = $this->_db->getQuery(true);
        $query->select('p.id AS ppID, d.id AS departmentID')
            ->from('#__thm_organizer_plan_programs AS p')
            ->innerJoin('#__thm_organizer_department_resources AS dr ON dr.programID = p.ID')
            ->innerJoin('#__thm_organizer_departments AS d on dr.departmentID = d.id');

        // Exact
        $this->addInclusiveConditions($query, $eWherray);
        $this->_db->setQuery($query);

        $associations = OrganizerHelper::executeQuery('loadAssocList');
        if (empty($associations)) {
            return;
        }

        $departmentIDs = [];

        foreach ($associations as $association) {
            $departmentIDs[$association['departmentID']] = $association['departmentID'];
        }

        $this->results['exact']['departments'] = $this->processDepartments($departmentIDs);

        $programs                             = [];
        $this->results['related']['programs'] = $this->processPrograms($programs, $associations);

        // Strong Related programs will not be displayed => no selection and no secondary processing.
        $query->clear('select');
        $query->clear('where');

        $query->select('DISTINCT d.id');
        $this->addInclusiveConditions($query, $sWherray);
        $this->_db->setQuery($query);
        $departmentIDs = OrganizerHelper::executeQuery('loadColumn', []);

        if (empty($departmentIDs)) {
            return;
        }

        $this->results['strong']['departments'] = $this->processDepartments($departmentIDs);
    }

    /**
     * Retrieves prioritized pool search results
     *
     * @return void adds to the results property
     */
    private function searchPools()
    {
        foreach ($this->terms as $index => $term) {
            if ($index === 0) {
                continue;
            }

            /*$epWherray[] = "REPLACE(LCASE(name), '.', '') LIKE '$term'";

            $eClause    = "REPLACE(LCASE(pl.name_de), '.', '') LIKE '$term' ";
            $eClause    .= "OR REPLACE(LCASE(pl.name_en), '.', '') LIKE '$term' ";
            $eClause    .= "OR REPLACE(LCASE(pl.short_name_de), '.', '') LIKE '$term' ";
            $eClause    .= "OR REPLACE(LCASE(pl.short_name_en), '.', '') LIKE '$term' ";
            $eClause    .= "OR REPLACE(LCASE(pl.abbreviation_de), '.', '') LIKE '$term' ";
            $eClause    .= "OR REPLACE(LCASE(pl.abbreviation_en), '.', '') LIKE '$term'";
            $eWherray[] = $eClause;*/

            $clause    = "REPLACE(LCASE(pl.name_de), '.', '') LIKE '%$term%' ";
            $clause    .= "OR REPLACE(LCASE(pl.name_en), '.', '') LIKE '%$term%' ";
            $clause    .= "OR REPLACE(LCASE(pl.short_name_de), '.', '') LIKE '%$term%' ";
            $clause    .= "OR REPLACE(LCASE(pl.short_name_en), '.', '') LIKE '%$term%' ";
            $clause    .= "OR REPLACE(LCASE(pl.abbreviation_de), '.', '') LIKE '%$term%' ";
            $clause    .= "OR REPLACE(LCASE(pl.abbreviation_en), '.', '') LIKE '%$term%'";
            $wherray[] = $clause;
        }

        // Plan programs have to be found in strings => standardized name as extra temp variable for comparison
        $ppQuery = $this->_db->getQuery(true);
        $ppQuery->from('#__thm_organizer_plan_pools');

        $pQuery = $this->_db->getQuery(true);
        $pQuery->from('#__thm_organizer_pools AS pl')
            ->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = pl.id');

        foreach ($this->programResults as $strength => $programs) {
            $pools = [];

            foreach ($programs as $program) {
                $ppQuery->clear('select');
                $ppQuery->clear('where');
                $pQuery->clear('select');
                $pQuery->clear('where');

                if (!empty($program['pProgramID'])) {
                    $ppQuery->select("DISTINCT id, '{$program['name']}' AS program");
                    $ppQuery->where("programID = '{$program['pProgramID']}'");
                    $this->_db->setQuery($ppQuery);

                    $pPoolIDs = OrganizerHelper::executeQuery('loadAssocList');
                }

                if (!empty($pPoolIDs)) {
                    $this->processPools($pools, $pPoolIDs, 'plan');
                }

                if (!empty($program['lft']) and !empty($program['rgt'])) {
                    $pQuery->select("DISTINCT pl.id, '{$program['name']}' AS program");

                    if (!empty($wherray)) {
                        $this->addInclusiveConditions($pQuery, $wherray);
                    }

                    $pQuery->where("(m.lft > '{$program['lft']}' AND m.rgt < '{$program['rgt']}')");
                    $this->_db->setQuery($pQuery);

                    $poolIDs = OrganizerHelper::executeQuery('loadAssocList');
                }

                if (!empty($poolIDs)) {
                    $this->processPools($pools, $poolIDs, 'real');
                }
            }

            if (!empty($pools)) {
                $this->results[$strength]['pools'] = $pools;
            }
        }
    }

    /**
     * Retrieves prioritized program search results
     *
     * @return void adds to the results property
     */
    private function searchPrograms()
    {
        $programResults = $this->programResults;

        foreach ($programResults as $strength => $programs) {
            $this->results[$strength]['programs'] = $programs;
        }
    }

    /**
     * Retrieves prioritized room search results
     *
     * @return void adds to the results property
     */
    private function searchRooms()
    {
        $select = 'r.id , r.name, r.capacity, ';
        $select .= "rt.name_{$this->languageTag} as type, rt.description_{$this->languageTag} as description";
        $query  = $this->_db->getQuery(true);
        $query->select($select)
            ->from('#__thm_organizer_rooms AS r')
            ->leftJoin('#__thm_organizer_room_types AS rt ON r.typeID = rt.id')
            ->order('r.name ASC');

        // EXACT

        $wherray = [];

        foreach ($this->terms as $term) {
            $wherray[] = "r.name LIKE '$term'";
        }

        $this->addInclusiveConditions($query, $wherray);
        $this->_db->setQuery($query);

        $eRooms = OrganizerHelper::executeQuery('loadAssocList');

        $this->results['exact']['rooms'] = $this->processRooms($eRooms);

        // STRONG => has name relevance
        $query->clear('where');

        $buildings = [];
        $capacity  = 0;
        $misc      = [];

        // Strong matches
        foreach ($this->terms as $index => $term) {
            // The reserved index for the complete search is irrelevant as such here
            if (count($this->terms) > 1 and $index === 0) {
                continue;
            }

            // This could probably be done with one expression, but I don't want to invest the time right now.
            $isBuilding = preg_match("/^[\p{L}}][\d]{1,2}$/", $term, $matches);
            $isFloor    = preg_match("/^[\p{L}}][\d]{1,2}\.[\d]{1,2}\.*$/", $term, $matches);

            if (!empty($isBuilding) or !empty($isFloor)) {
                $buildings[] = $term;
                continue;
            }

            // Only a number, the only real context for a numerical search term
            $isCapacity = preg_match("/^\d+$/", $term, $matches);

            if (!empty($isCapacity)) {
                $number = (int)$term;

                // The number most likely denotes a module sequence
                if ($number < 5) {
                    continue;
                }

                // Bigger numbers will trump smaller ones in the search, so they are superfluous.
                $capacity = $number > $capacity ? (int)$term : $capacity;
                continue;
            }

            // Relevance cannot be determined, if relevant than a non-conforming name
            $misc[] = $term;
        }

        $typeIDs    = $this->getRoomTypes($misc, $capacity);
        $typeString = empty($typeIDs) ? '' : "'" . implode("', '", $typeIDs) . "'";

        if (!empty($misc)) {
            foreach ($misc as $term) {
                $query->where("(r.name LIKE '%$term%')");
            }
        }

        if (!empty($buildings)) {
            $query->where("(r.name LIKE '" . implode("%' OR r.name LIKE '", $buildings) . "%')");
        }

        $performStrongQuery = (!empty($misc) or !empty($buildings));

        if ($performStrongQuery) {

            if (!empty($capacity) and !empty($typeString)) {
                // Opens main clause and room cap existent
                $query->where("((r.capacity >= '$capacity' OR r.capacity = '0') AND rt.id IN ($typeString))");
            } elseif (!empty($capacity)) {
                $query->where("r.capacity >= '$capacity'");
            } elseif (!empty($typeString)) {
                $query->where("rt.id IN ($typeString)");
            }
            $this->_db->setQuery($query);

            $sRooms = OrganizerHelper::executeQuery('loadAssocList');

            $this->results['strong']['rooms'] = $this->processRooms($sRooms);
        }

        // Related => has type or capacity relevance

        $query->clear('where');

        if (!empty($capacity) and !empty($typeString)) {
            // Opens main clause and room cap existent
            $query->where("((r.capacity >= '$capacity' OR r.capacity = '0') AND rt.id IN ($typeString))");
        } elseif (!empty($capacity)) {
            $query->where("r.capacity >= '$capacity'");
        } elseif (!empty($typeString)) {
            $query->where("rt.id IN ($typeString)");
        }

        $performRelatedQuery = (!empty($capacity) or !empty($typeString));

        if ($performRelatedQuery) {
            $this->_db->setQuery($query);

            $rRooms = OrganizerHelper::executeQuery('loadAssocList');

            $this->results['related']['rooms'] = $this->processRooms($rRooms);
        }

    }

    /**
     * Retrieves prioritized subject/lesson search results
     *
     * @return void adds to the results property
     */
    private function searchSubjects()
    {
        $terms = $this->terms;

        foreach ($terms as $index => $term) {
            $short     = strlen($term) < 3;
            $isRoman   = preg_match("/^([ix|iv|v]{1}|[i]+)$/", $term, $matches);
            $isNumeric = is_numeric($term);

            if ($short and !($isRoman or $isNumeric)) {
                unset($terms[$index]);
            }
        }

        if (empty($terms)) {
            return;
        }

        $termCount = count($terms);

        // A plan subject does not necessarily have subject documentation
        $psQuery = $this->_db->getQuery(true);
        $psQuery->select('DISTINCT ps.id AS psID, s.id AS sID')
            ->from('#__thm_organizer_plan_subjects AS ps')
            ->innerJoin('#__thm_organizer_lesson_subjects AS ls ON ls.subjectID = ps.id')
            ->innerJoin('#__thm_organizer_lessons AS l ON ls.lessonID = l.id')
            ->leftJoin('#__thm_organizer_subject_mappings AS sm ON sm.plan_subjectID = ps.id')
            ->leftJoin('#__thm_organizer_subjects AS s ON sm.subjectID = s.id');

        // Subject documentation does not necessarily have planned lesson instances
        $sQuery = $this->_db->getQuery(true);
        $sQuery->select('DISTINCT s.id AS sID, ps.id as psID')
            ->from('#__thm_organizer_subjects AS s')
            ->leftJoin('#__thm_organizer_subject_mappings AS sm on sm.subjectID = s.id')
            ->leftJoin('#__thm_organizer_plan_subjects AS ps on sm.plan_subjectID = ps.id')
            ->leftJoin('#__thm_organizer_lesson_subjects AS ls on ls.subjectID = ps.id')
            ->leftJoin('#__thm_organizer_lessons AS l on ls.lessonID = l.id');

        // EXACT => exact (case independent) match for the search term
        $initialTerm = current($terms);

        $psClause = "(ps.name LIKE '$initialTerm' OR ps.subjectNo LIKE '$initialTerm'";

        $sClause = "(s.externalID LIKE '$initialTerm' OR s.name_de LIKE '$initialTerm' OR s.name_en LIKE '$initialTerm' OR ";
        $sClause .= "s.short_name_de LIKE '$initialTerm' OR s.short_name_en LIKE '$initialTerm' OR ";
        $sClause .= "s.abbreviation_de LIKE '$initialTerm' OR s.abbreviation_en LIKE '$initialTerm'";

        foreach ($terms as $term) {
            $psClause .= " OR ps.subjectNo LIKE '$term'";
            $sClause  .= "OR s.externalID LIKE '$term'";
        }

        $psClause .= ')';
        $sClause  .= ')';

        $this->filterLessons($psQuery);
        $psQuery->where($psClause);

        $this->filterLessons($sQuery);
        $sQuery->where($sClause);

        $this->_db->setQuery($psQuery);
        $planSubjects = OrganizerHelper::executeQuery('loadAssocList', [], 'psID');
        $this->_db->setQuery($sQuery);
        $subjects = OrganizerHelper::executeQuery('loadAssocList', [], 'sID');

        $this->results['exact']['subjects'] = $this->processSubjects($subjects, $planSubjects);

        if (empty($terms)) {
            return;
        }

        // STRONG => exact match on at least one term
        $psQuery->clear('where');
        $sQuery->clear('where');
        $nameDEArray = [];
        $nameENArray = [];

        foreach ($terms as $index => $term) {
            $asNumber = false;

            preg_match("/^([ix|iv|v]{1}|[i]+)$/", $term, $matches);

            if (!empty($matches) or is_numeric($term)) {
                $asNumber = true;
            }

            // Numeric values will always be listed separately at the end of the names. Direct comparison delivers false positives.
            if ($asNumber) {
                $psQuery->where("ps.name LIKE '% $term'");
                $nameDEArray[] = "s.name_de LIKE '% $term'";
                $nameENArray[] = "s.name_en LIKE '% $term'";
            } else {
                $psQuery->where("ps.name LIKE '%$term%'");
                $nameDEArray[] = "s.name_de LIKE '%$term%'";
                $nameENArray[] = "s.name_en LIKE '%$term%'";
            }
        }

        $this->filterLessons($psQuery);
        $this->_db->setQuery($psQuery);

        $nameDEClause = '(' . implode(' AND ', $nameDEArray) . ')';
        $nameENClause = '(' . implode(' AND ', $nameENArray) . ')';
        $sQuery->where("($nameDEClause OR $nameENClause)");
        $this->filterLessons($sQuery);

        $this->_db->setQuery($psQuery);
        $planSubjects = OrganizerHelper::executeQuery('loadAssocList', [], 'psID');
        $this->_db->setQuery($sQuery);
        $subjects = OrganizerHelper::executeQuery('loadAssocList', [], 'sID');

        $this->results['strong']['subjects'] = $this->processSubjects($subjects, $planSubjects);

        // Good
        $psQuery->clear('where');
        $sQuery->clear('where');

        $sWherray  = [];
        $psWherray = [];

        foreach ($terms as $index => $term) {
            $asNumber = false;

            preg_match("/^([ix|iv|v]{1}|[i]+)$/", $term, $matches);

            if (!empty($matches) or is_numeric($term)) {
                $asNumber = true;
            }

            // Numeric values will always be listed separately at the end of the names. Direct comparison delivers false positives.
            if ($asNumber) {
                $sClause     = "s.name_de LIKE '% $term' OR s.name_en LIKE '% $term' OR ";
                $sClause     .= "s.short_name_de REGEXP '%$term' OR s.short_name_en REGEXP '%$term' OR ";
                $sClause     .= "s.abbreviation_de REGEXP '%$term' OR s.abbreviation_en REGEXP '%$term'";
                $sWherray[]  = $sClause;
                $psWherray[] = "ps.name LIKE '% $term' OR ps.subjectNo REGEXP '%$term%'";
            } else {
                $sClause     = "s.name_de LIKE '%$term%' OR s.name_en LIKE '%$term%' OR ";
                $sClause     .= "s.short_name_de LIKE '%$term%' OR s.short_name_en LIKE '%$term%' OR ";
                $sClause     .= "s.abbreviation_de LIKE '%$term%' OR s.abbreviation_en LIKE '%$term%'";
                $sWherray[]  = $sClause;
                $psWherray[] = "ps.name REGEXP '%$term%' OR ps.subjectNo REGEXP '%$term%'";
            }

        }

        // There were only numeric values in the search so the conditions are empty => don't execute queries
        if (empty($psWherray) and empty($sWherray)) {
            return;
        }

        $this->filterLessons($psQuery);
        $this->addInclusiveConditions($psQuery, $psWherray);

        $this->filterLessons($sQuery);
        $this->addInclusiveConditions($sQuery, $sWherray);

        if (!empty($psWherray)) {
            $this->_db->setQuery($psQuery);
            $planSubjects = OrganizerHelper::executeQuery('loadAssocList', [], 'psID');
        } else {
            $planSubjects = null;
        }

        if (!empty($sWherray)) {
            $this->_db->setQuery($sQuery);
            $subjects = OrganizerHelper::executeQuery('loadAssocList', [], 'sID');
        } else {
            $subjects = null;
        }

        $this->results['good']['subjects'] = $this->processSubjects($subjects, $planSubjects);

        // Mentioned Looks for mention of the terms in the differing text fields of the module descriptions.

        $sQuery->clear('where');
        $planSubjects = null;

        $sWherray = [];

        foreach ($terms as $index => $term) {
            // Numeric values deliver true for everything
            if (count($this->terms) > 1 and $index === 0) {
                continue;
            }

            $sClause    = "s.content_de LIKE '% $term%' OR s.content_en LIKE '% $term%' OR ";
            $sClause    .= "s.description_de LIKE '% $term %' OR s.description_en LIKE '% $term%' OR ";
            $sClause    .= "s.objective_de LIKE '% $term%' OR s.objective_en LIKE '% $term%'";
            $sWherray[] = $sClause;
        }

        // There were only numeric values in the search so the conditions are empty => don't execute queries
        if (empty($sWherray)) {
            return;
        }

        $this->filterLessons($sQuery);
        $this->addInclusiveConditions($sQuery, $sWherray);
        $this->_db->setQuery($sQuery);

        $subjects = OrganizerHelper::executeQuery('loadAssocList', [], 'sID');

        $this->results['mentioned']['subjects'] = $this->processSubjects($subjects, $planSubjects);

        // Related
        $psQuery->clear('where');
        $sQuery->clear('where');

        $psQuery->innerJoin('#__thm_organizer_lesson_teachers AS lt ON lt.subjectID = ls.id')
            ->innerJoin('#__thm_organizer_teachers AS t on lt.teacherID = t.id');

        $sQuery->innerJoin('#__thm_organizer_subject_teachers AS st ON st.subjectID = s.id')
            ->innerJoin('#__thm_organizer_teachers AS t on st.teacherID = t.id');

        if ($termCount == 1) {
            $psQuery->where("t.surname LIKE '%$initialTerm%'");
            $sQuery->where("t.surname LIKE '%$initialTerm%'");
        } else {
            $wherray    = [];
            $innerTerms = $terms;

            foreach ($terms as $outerTerm) {
                foreach ($terms as $iKey => $innerTerm) {
                    if ($outerTerm == $innerTerm) {
                        unset($innerTerms[$iKey]);
                        continue;
                    }

                    // lnf/fnf
                    $wherray[] = "(t.surname LIKE '%$outerTerm%' AND t.forename LIKE '%$innerTerm%')";
                    $wherray[] = "(t.surname LIKE '%$innerTerm%' AND t.forename LIKE '%$outerTerm%')";
                }
            }

            $this->addInclusiveConditions($psQuery, $wherray);
            $this->addInclusiveConditions($sQuery, $wherray);
        }

        $this->_db->setQuery($psQuery);
        $planSubjects = OrganizerHelper::executeQuery('loadAssocList', [], 'psID');
        $this->_db->setQuery($sQuery);
        $subjects = OrganizerHelper::executeQuery('loadAssocList', [], 'sID');

        $this->results['related']['subjects'] = $this->processSubjects($subjects, $planSubjects);
    }

    /**
     * Retrieves prioritized teacher search results
     *
     * @return void adds to the results property
     */
    private function searchTeachers()
    {
        $terms = $this->terms;

        foreach ($terms as $index => $term) {
            if (strlen($term) < 2) {
                unset($terms[$index]);
            }
        }

        $termCount = count($terms);

        if ($termCount == 0) {
            return;
        }

        $query = $this->_db->getQuery(true);
        $query->select('id , surname, forename, title')
            ->from('#__thm_organizer_teachers')
            ->order('forename, surname ASC');

        // EXACT => requires a forename and surname match

        if ($termCount >= 2) {
            $wherray    = [];
            $innerTerms = $terms;

            foreach ($terms as $outerTerm) {
                foreach ($innerTerms as $iKey => $innerTerm) {
                    if ($outerTerm == $innerTerm) {
                        unset($innerTerms[$iKey]);
                        continue;
                    }

                    // lnf/fnf
                    $wherray[] = "(surname LIKE '%$outerTerm%' AND forename LIKE '%$innerTerm%')";
                    $wherray[] = "(surname LIKE '%$innerTerm%' AND forename LIKE '%$outerTerm%')";
                }
            }

            $this->addInclusiveConditions($query, $wherray);
            $this->_db->setQuery($query);

            $eTeachers = OrganizerHelper::executeQuery('loadAssocList');

            $this->results['exact']['teachers'] = $this->processTeachers($eTeachers);
        }

        // Strong

        $query->clear('where');
        $wherray = [];

        foreach ($terms as $term) {
            // lnf/fnf
            $wherray[] = "surname LIKE '$term'";
        }

        $this->addInclusiveConditions($query, $wherray);
        $this->_db->setQuery($query);

        $sTeachers = OrganizerHelper::executeQuery('loadAssocList');

        $this->results['strong']['teachers'] = $this->processTeachers($sTeachers);

        // Good

        $query->clear('where');
        $wherray = [];

        foreach ($terms as $term) {
            // lnf/fnf
            $wherray[] = "surname LIKE '%$term%' OR forename LIKE '%$term%'";
        }

        $this->addInclusiveConditions($query, $wherray);
        $this->_db->setQuery($query);

        $gTeachers = OrganizerHelper::executeQuery('loadAssocList');

        $this->results['good']['teachers'] = $this->processTeachers($gTeachers);
    }

    /**
     * Finds programs which can be associated with the terms. Possible return strengths exact, strong and good.
     *
     * @return void set the program results property
     */
    private function setPrograms()
    {
        // Clone for editing.
        $terms     = $this->terms;
        $firstTerm = $terms[0];
        unset($terms[0]);

        foreach ($terms as $index => $term) {
            $terms[$index] = str_replace('.', '', $term);
        }

        $programResults = [];
        $degrees        = $this->getDegrees($terms);

        $ePWherray  = [];
        $sPWherray  = [];
        $ePPWherray = [];
        $sPPWherray = [];

        foreach ($terms as $term) {
            $ePWherray[] = "p.name_de LIKE '$term$' OR p.name_en LIKE '$term%'";
            $sPWherray[] = "p.name_de LIKE '%$term%' OR p.name_en LIKE '%$term%'";

            // Plan program degrees have to be resolved by string comparison
            $ePPWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '$term%'";
            $sPPWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '%$term%'";
        }

        $pQuery = $this->_db->getQuery(true);
        $pQuery->select("p.id, name_{$this->languageTag} AS name, degreeID, pp.id AS ppID, lft, rgt")
            ->from('#__thm_organizer_programs AS p')
            ->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.ID')
            ->leftJoin('#__thm_organizer_plan_programs AS pp ON pp.programID = p.ID');

        // Plan programs have to be found in strings => standardized name as extra temp variable for comparison
        $ppQuery = $this->_db->getQuery(true);
        $ppQuery->select("p.id, name_{$this->languageTag} AS name, degreeID, pp.id AS ppID, lft, rgt")
            ->from('#__thm_organizer_plan_programs AS pp')
            ->leftJoin('#__thm_organizer_programs AS p ON pp.programID = p.ID')
            ->leftJoin('#__thm_organizer_mappings AS m ON m.programID = p.ID');

        // Exact => program name and degree
        if (!empty($degrees['exact'])) {
            $degreeIDs = array_keys($degrees['exact']);
            $pQuery->where("p.degreeID IN ('" . implode("','", $degreeIDs) . "')");
            $this->addInclusiveConditions($pQuery, $ePWherray);

            $degreeWherray = [];
            $this->addInclusiveConditions($ppQuery, $ePPWherray);

            foreach ($degrees['exact'] as $degree) {
                $degreeWherray[] = "REPLACE(LCASE(pp.name), '.', '') LIKE '%{$degree['stdAbbr']}%'";
            }

            $this->addInclusiveConditions($ppQuery, $degreeWherray);

            $this->_db->setQuery($ppQuery);
            $planPrograms = OrganizerHelper::executeQuery('loadAssocList');
            $this->_db->setQuery($pQuery);
            $programs = OrganizerHelper::executeQuery('loadAssocList');

            $programResults['exact'] = $this->processPrograms($programs, $planPrograms);
        }

        // Strong => full program name
        $wherray   = [];
        $wherray[] = "(name LIKE '%$firstTerm%')";

        $this->addInclusiveConditions($ppQuery, $wherray);
        $this->_db->setQuery($ppQuery);
        $sPlanPrograms = OrganizerHelper::executeQuery('loadAssocList');

        $this->addInclusiveConditions($pQuery, $wherray);
        $this->_db->setQuery($pQuery);
        $sPrograms = OrganizerHelper::executeQuery('loadAssocList');

        $programResults['strong'] = $this->processPrograms($sPrograms, $sPlanPrograms);

        // Good => parts of the program name
        $wherray = [];
        foreach ($this->terms as $term) {
            $wherray[] = "(name LIKE '%$term%')";
        }

        $this->addInclusiveConditions($ppQuery, $wherray);
        $this->_db->setQuery($ppQuery);
        $gPlanPrograms = OrganizerHelper::executeQuery('loadAssocList');

        $this->addInclusiveConditions($pQuery, $wherray);
        $this->_db->setQuery($pQuery);
        $gPrograms = OrganizerHelper::executeQuery('loadAssocList');

        $programResults['good'] = $this->processPrograms($gPrograms, $gPlanPrograms);
        $this->programResults   = $programResults;
    }

    /**
     * Set the search terms.
     *
     * @param string $rawSearch the raw string from the request
     *
     * @return void sets the $terms property
     */
    private function setTerms($rawSearch)
    {
        $prohibited     = ['\\', '\'', '"', '%', '_', '(', ')'];
        $safeSearch     = str_replace($prohibited, '', $rawSearch);
        $standardSearch = strtolower($safeSearch);

        // Remove English and German ordinals
        $standardSearch = preg_replace('/ (.*[1-9])(?:\.|st|nd|rd|th)(.*)/', "$1$2", $standardSearch);

        // Filter out semester terms so that both the number and the word semster are one term.
        preg_match_all('/[1-9] (semester|sem)/', $standardSearch, $semesters);

        $this->terms = [];

        // Remove the semester terms from the search and add them to the terms
        if (!empty($semesters)) {
            foreach ($semesters[0] as $semester) {
                $this->terms[]  = $semester;
                $standardSearch = str_replace($semester, '', $standardSearch);
            }
        }

        // Add the original search to the beginning of the array
        array_unshift($this->terms, $standardSearch);

        $remainingTerms = explode(' ', $standardSearch);

        $whiteNoise = [
            'der',
            'die',
            'das',
            'den',
            'dem',
            'des',
            'einer',
            'eine',
            'ein',
            'einen',
            'einem',
            'eines',
            'und',
            'the',
            'a',
            'and',
            'oder',
            'or',
            'aus',
            'von',
            'of',
            'from',
        ];

        foreach ($remainingTerms as $term) {
            $isWhiteNoise   = in_array($term, $whiteNoise);
            $isSingleLetter = (!is_numeric($term) and strlen($term) < 2);

            if ($isWhiteNoise or $isSingleLetter) {
                continue;
            }

            $this->terms[] = $term;
        }

        // Remove non-unique terms to prevent bloated queries
        $this->terms = array_unique($this->terms);
    }

    /**
     * Function used as a call back for sorting results by their names. (Callable)
     *
     * @param array $itemOne the first item
     * @param array $itemTwo the second item
     *
     * @return bool true if the text for the first item should come after the second item, otherwise false
     *
     * @SuppressWarnings(PMD.UnusedPrivateMethod)
     */
    private function sortItems($itemOne, $itemTwo)
    {
        return $itemOne['text'] > $itemTwo['text'];
    }
}
