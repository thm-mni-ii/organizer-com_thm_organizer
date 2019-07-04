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

use Organizer\Helpers\Input;
use Organizer\Helpers\Languages;
use Organizer\Helpers\Teachers;
use Organizer\Helpers\OrganizerHelper;
use stdClass;

/**
 * Class loads curriculum information into the view context.
 */
class Curriculum extends \Joomla\CMS\MVC\Model\ItemModel
{
    /**
     * Method to get an array of data items.
     *
     * @return mixed  An array of data items on success, false on failure.
     */
    public function getItem()
    {
        $program = new stdClass;

        $programID = Input::getFilterID('program');
        $poolID    = Input::getFilterID('pool');

        if (empty($programID) and empty($poolID)) {
            return $program;
        }

        $program->id = $programID;
        $this->setProgramInformation($program);
        if (empty($program->name)) {
            return $program;
        }

        $this->setChildren($program);

        return $program;
    }

    /**
     * Sets program attributes
     *
     * @param object &$program the object modeling the program data
     *
     * @return void  sets object attributes
     */
    private function setProgramInformation(&$program)
    {
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);
        $query->select("p.name_$tag AS name, d.abbreviation, p.version, m.id AS mapping")
            ->from('#__thm_organizer_programs AS p')
            ->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id')
            ->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.id')
            ->where("p.id = '$program->id'");
        $this->_db->setQuery($query);
        $programData = OrganizerHelper::executeQuery('loadAssoc', []);
        if (empty($programData)) {
            return;
        }

        $program->name    = "{$programData['name']} ({$programData['abbreviation']} {$programData['version']})";
        $program->mapping = $programData['mapping'];
        $program->type    = 'program';
    }

    /**
     * Sets the children for the given element
     *
     * @param object &$element the object modeling the program data
     *
     * @return void  sets object attributes
     */
    private function setChildren(&$element)
    {
        $query = $this->_db->getQuery(true);
        $query->select('*')
            ->from('#__thm_organizer_mappings')
            ->where("parentID = '$element->mapping'")
            ->order('ordering ASC');
        $this->_db->setQuery($query);

        $children = OrganizerHelper::executeQuery('loadObjectList');
        if (empty($children)) {
            return;
        }

        $element->children = [];
        foreach ($children as $child) {
            $order = (int)$child->ordering;
            if (!empty($child->poolID)) {
                $element->children[$order] = $this->getPool($child->poolID, $child->id);
                if (empty($element->children[$order])) {
                    continue;
                }

                $this->setChildren($element->children[$order]);
            }

            // Programs should not have subjects as children this can happen through false modelling in LSF.
            if ($element->type == 'program') {
                continue;
            }

            if (!empty($child->subjectID)) {
                $element->children[$order] = $this->getSubject($child->subjectID, $child->id);
            }
        }
    }

    /**
     * Retrieves a pool element
     *
     * @param int $poolID    the pool id
     * @param int $mappingID the mapping id
     *
     * @return mixed  object on success, otherwise null
     */
    private function getPool($poolID, $mappingID)
    {
        $tag    = Languages::getTag();
        $query  = $this->_db->getQuery(true);
        $select = "p.id, p.name_$tag AS name, description_$tag AS description, minCrP, maxCrP, ";
        $select .= 'enable_desc, color AS bgColor';
        $query->select($select)
            ->from('#__thm_organizer_pools AS p')
            ->leftJoin('#__thm_organizer_fields AS f ON f.id = p.fieldID')
            ->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id')
            ->where("p.id = '$poolID'");
        $this->_db->setQuery($query);

        $pool = OrganizerHelper::executeQuery('loadObject');
        if (empty($pool)) {
            return null;
        }

        $pool->mapping = $mappingID;
        $pool->type    = 'pool';

        return $pool;
    }

    /**
     * Retrieves a subject element
     *
     * @param int $subjectID the subject id
     * @param int $mappingID the mapping id
     *
     * @return mixed  object on success, otherwise null
     */
    private function getSubject($subjectID, $mappingID)
    {
        $tag   = Languages::getTag();
        $query = $this->_db->getQuery(true);

        $select      = "s.id, externalID, s.name_$tag AS name, creditpoints AS CrP, color AS bgColor, ";
        $menuID      = Input::getItemid();
        $menuIDParam = empty($menuID) ? '' : "&Itemid=$menuID";
        $subjectLink = "'index.php?option=com_thm_organizer&view=subject_details&languageTag=$tag$menuIDParam&id='";
        $parts       = [$subjectLink, 's.id'];
        $select      .= $query->concatenate($parts, '') . ' AS link';

        $query->select($select)
            ->from('#__thm_organizer_subjects AS s')
            ->leftJoin('#__thm_organizer_fields AS f ON f.id = s.fieldID')
            ->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id')
            ->where("s.id = '$subjectID'");
        $this->_db->setQuery($query);

        $subject = OrganizerHelper::executeQuery('loadObject');
        if (empty($subject)) {
            return null;
        }

        $subject->mapping = $mappingID;
        $subject->type    = 'subject';

        $teacher = Teachers::getDataBySubject($subject->id, 1);

        if (!empty($teacher)) {
            $subject->teacherName = Teachers::getDefaultName($teacher['id']);
            $subject->teacherID   = $teacher['id'];
        }

        return $subject;
    }
}
