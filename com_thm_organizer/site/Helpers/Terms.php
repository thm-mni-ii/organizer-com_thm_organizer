<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Helpers;

use Joomla\CMS\Factory;

/**
 * Provides general functions for term access checks, data retrieval and display.
 */
class Terms
{
    /**
     * Gets the id of the term whose dates encompass the current date
     *
     * @return int the id of the term for the dates used on success, otherwise 0
     */
    public static function getCurrentID()
    {
        $date  = date('Y-m-d');
        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_terms')
            ->where("'$date' BETWEEN startDate and endDate");
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Checks for the term end date for a given term id
     *
     * @param string $termID the term's id
     *
     * @return mixed  string the end date of the term could be resolved, otherwise null
     */
    public static function getEndDate($termID)
    {
        $table = OrganizerHelper::getTable('Terms');

        try {
            $success = $table->load($termID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        return $success ? $table->endDate : null;
    }

    /**
     * Checks for the term entry in the database, creating it as necessary.
     *
     * @param array $data the term's data
     *
     * @return mixed  int the id if the room could be resolved/added, otherwise null
     */
    public static function getID($data)
    {
        $table        = OrganizerHelper::getTable('Terms');
        $loadCriteria = ['startDate' => $data['startDate'], 'endDate' => $data['endDate']];

        try {
            $success = $table->load($loadCriteria);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        if ($success) {
            return $table->id;
        } elseif (empty($data)) {
            return null;
        }

        // Entry not found
        $success = $table->save($data);

        return $success ? $table->id : null;
    }

    /**
     * Checks for the term name for a given term id
     *
     * @param string $termID the term's id
     *
     * @return mixed  string the name if the term could be resolved, otherwise null
     */
    public static function getName($termID)
    {
        $table = OrganizerHelper::getTable('Terms');

        try {
            $success = $table->load($termID);
        } catch (Exception $exc) {
            OrganizerHelper::message($exc->getMessage(), 'error');

            return null;
        }

        return $success ? $table->name : null;
    }

    /**
     * Retrieves the ID of the term occurring immediately after the reference term.
     *
     * @param int $currentID the id of the reference term
     *
     * @return int the id of the subsequent term if successful, otherwise 0
     */
    public static function getNextID($currentID = 0)
    {
        if (empty($currentID)) {
            $currentID = self::getCurrentID();
        }

        $currentEndDate = self::getEndDate($currentID);

        $dbo   = Factory::getDbo();
        $query = $dbo->getQuery(true);
        $query->select('id')
            ->from('#__thm_organizer_terms')
            ->where("startDate > '$currentEndDate'")
            ->order('startDate ASC');
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Getter method for rooms in database. Only retrieving the IDs here allows for formatting the names according to
     * the needs of the calling views.
     *
     * @return string  all pools in JSON format
     */
    public static function getTerms()
    {
        $dbo   = Factory::getDbo();
        $input = OrganizerHelper::getInput();

        $selectedDepartments = $input->getString('departmentIDs');
        $selectedCategories  = $input->getString('categoryIDs');

        $query = $dbo->getQuery(true);
        $query->select('DISTINCT term.id, term.name, term.startDate, term.endDate')
            ->from('#__thm_organizer_terms AS term');

        if (!empty($selectedDepartments) or !empty($selectedCategories)) {
            $query->innerJoin('#__thm_organizer_lessons AS l on term.id = l.termID');

            if (!empty($selectedDepartments)) {
                $query->innerJoin('#__thm_organizer_departments AS dpt ON l.departmentID = dpt.id');
                $departmentIDs = "'" . str_replace(',', "', '", $selectedDepartments) . "'";
                $query->where("l.departmentID IN ($departmentIDs)");
            }

            if (!empty($selectedCategories)) {
                $query->innerJoin('#__thm_organizer_lesson_courses AS lcrs on lcrs.lessonID = l.id');
                $query->innerJoin('#__thm_organizer_lesson_groups AS lg on lg.lessonCourseID = lcrs.id');
                $query->innerJoin('#__thm_organizer_groups AS gr ON gr.id = lg.groupID');
                $categoryIDs = "'" . str_replace(',', "', '", $selectedCategories) . "'";
                $query->where("gr.categoryID in ($categoryIDs)");
            }
        }

        $query->order('startDate');
        $dbo->setQuery($query);

        return OrganizerHelper::executeQuery('loadAssocList', []);
    }
}
