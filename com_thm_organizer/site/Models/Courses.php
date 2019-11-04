<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @author      Florian Fenzl, <florian.fenzl@mni.thm.de>
 * @author      Krishna Priya Madakkagari, <krishna.madakkagari@iem.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

namespace Organizer\Models;

use Organizer\Helpers\Filtered;
use Organizer\Helpers\Languages;

/**
 * Class retrieves the data regarding a filtered set of courses.
 */
class Courses extends ListModel
{
	use Filtered;

	protected $defaultOrdering = 'name';

    /**
     * Method to get a \JDatabaseQuery object for retrieving the data set from a database.
     *
     * Adds filter settings for status, campus, term
     *
     * @return \JDatabaseQuery  A \JDatabaseQuery object to retrieve the data set.
     */
    protected function getListQuery()
    {
        $tag = Languages::getTag();

        $query    = $this->_db->getQuery(true);
        $subQuery = $this->_db->getQuery(true);

        $subQuery->select('MIN(date) as start, MAX(date) as end, ci.courseID')
            ->from('#__thm_organizer_blocks as b')
            ->innerJoin('#__thm_organizer_instances as i on i.blockID = b.id')
            ->innerJoin('#__thm_organizer_course_instances as ci on ci.instanceID = i.id')
            ->where("i.delta!='removed'")
            ->group('ci.courseID');

        $query->select('c.id')
            ->select("ev.id as eventID, ev.name_$tag as name")
            ->select("cp.id as campusID, cp.name_$tag as campus")
            ->select("t.id AS termID, t.name as term")
            ->select("sq.start, sq.end");

        $query->from('#__thm_organizer_courses AS c')
            ->innerJoin('#__thm_organizer_events AS ev ON ev.id = c.eventID')
            ->leftJoin('#__thm_organizer_campuses as cp on cp.id = c.campusID')
            ->innerJoin('#__thm_organizer_terms as t on t.id = c.termID')
            ->innerJoin("($subQuery) as sq on sq.courseID = c.id")
            ->group('c.id');

        $this->setSearchFilter($query, ['ev.name_de', 'ev.name_en']);
        $this->setValueFilters($query, ['c.termID', 'c.campusID']);
        $this->setDateStatusFilter($query, 'status', 'sq.start', 'sq.end');

        return $query;
    }
}
