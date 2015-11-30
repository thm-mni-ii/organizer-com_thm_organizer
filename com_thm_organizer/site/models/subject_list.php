<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelSubject_List
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_COMPONENT . '/helpers/teacher.php';
require_once JPATH_SITE . '/media/com_thm_organizer/helpers/componentHelper.php';

define('NONE', 0);
define('POOL', 1);
define('TEACHER', 2);
define('FIELD', 3);

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_List extends JModelList
{
    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItems()
    {
        $subjects = parent::getItems();
        foreach ($subjects AS $index => $subject)
        {
            if (!empty($subject->subjectColor))
            {
                $subjects[$index]->subjectTextColor = THM_OrganizerHelperComponent::getTextColor($subject->subjectColor);
            }
            if (!empty($subject->teacherColor))
            {
                $subjects[$index]->teacherTextColor = THM_OrganizerHelperComponent::getTextColor($subject->teacherColor);
            }
            $subjects[$index]->teacherName = empty($subject->forename)? $subject->surname : "$subject->surname, $subject->forename";
        }
        return $subjects;
    }

    /**
     * Method to cache the last query constructed.
     *
     * This method ensures that the query is constructed only once for a given state of the model.
     *
     * @return  object  a JDatabaseQuery object
     */
    protected function getListQuery()
    {
        $programInformation = $this->getProgramInformation();
        if (empty($programInformation))
        {
            return $this->_db->getQuery(true);
        }

        $menuID = $this->state->get('menuID');
        $languageTag = $this->state->get('languageTag');
        $subjectLink = "'index.php?option=com_thm_organizer&view=subject_details&languageTag=$languageTag&Itemid=$menuID&id='";

        $query = $this->_db->getQuery(true);

        $select = "s.id, s.name_$languageTag AS subject, s.creditpoints, s.externalID, s.fieldID, sf.field, sc.color as subjectColor, ";
        $select .= "m2.poolID, p.name_$languageTag AS pool, m2.lft, m2.rgt, pf.field as poolField, pc.color as poolColor, ";
        $select .= "st.teacherID, t.surname, t.forename, tc.color AS teacherColor, st.teacherResp, tf.field as teacherField, ";
        $parts = array("$subjectLink","s.id");
        $select .= $query->concatenate($parts, "") . " AS subjectLink";
        $query->select($select);

        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS sf ON s.fieldID = sf.id');
        $query->leftJoin('#__thm_organizer_colors AS sc ON sc.id = sf.colorID');
        $query->innerJoin('#__thm_organizer_mappings AS m1 ON m1.subjectID = s.id');
        $query->innerJoin('#__thm_organizer_mappings AS m2 ON m1.parentID = m2.id');
        $query->innerJoin('#__thm_organizer_pools AS p ON m2.poolID = p.id');
        $query->leftJoin('#__thm_organizer_fields AS pf ON p.fieldID = pf.id');
        $query->leftJoin('#__thm_organizer_colors AS pc ON pc.id = pf.colorID');
        $query->leftJoin('#__thm_organizer_subject_teachers AS st ON s.id = st.subjectID');
        $query->leftJoin('#__thm_organizer_teachers AS t ON st.teacherID = t.id');
        $query->leftJoin('#__thm_organizer_fields AS tf ON t.fieldID = tf.id');
        $query->leftJoin('#__thm_organizer_colors AS tc ON tc.id = tf.colorID');

        $query->where("m1.lft > '{$programInformation['lft']}' AND  m1.rgt < '{$programInformation['rgt']}'");

        $this->setSearch($query);

        $query->order('subject ASC');
        return $query;
    }

    /**
     * Retrieves program information (name and nesting values)
     * 
     * @return  mixed  array on success, otherwise false
     */
    private function getProgramInformation()
    {
        $programID = $this->state->get('programID');
        $languageTag = $this->state->get('languageTag');

        $query = $this->_db->getQuery(true);
        $parts = array("p.subject_$languageTag","' ('", "d.abbreviation", "' '", "p.version", "')'");
        $query->select($query->concatenate($parts, "") . " AS name, lft, rgt");
        $query->from('#__thm_organizer_programs AS p');
        $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.id');
        $query->where("p.id = '$programID'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $programData = $this->_db->loadAssoc();
            $this->programName = $programData['name'];
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return array();
        }
        
        return $programData;
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $app = JFactory::getApplication();

        $menuID = $app->getUserStateFromRequest($this->context . '.menuID', 'Itemid');
        $this->state->set('menuID', $menuID);

        $params = $app->getMenu()->getItem($menuID)->params;

        $programID = $params->get('programID');
        $this->state->set('programID', $programID);

        $search = $app->input->get('search', '');
        $this->state->set('search', $search);

        $app->set('list_limit', '0');
        $limit = $app->getUserStateFromRequest($this->context . '.limit', 'limit', '0');
        $this->state->set('list.limit', $limit);

        $menuLanguage = ($params->get('language') == '0')? 'en' : 'de';
        $languageTag = $app->getUserStateFromRequest($this->context . '.languageTag', 'languageTag', $menuLanguage);
        $this->state->set('languageTag', $languageTag);

        $menuGroupBy = $params->get('groupBy');
        $groupBy = $app->getUserStateFromRequest($this->context . '.groupBy', 'groupBy', $menuGroupBy);
        $this->state->set('groupBy', $groupBy);
    }

    /**
     * Builds the search clause based upon user input
     *
     * @param   object  &$query  the query object upon which search conditions will be set
     *
     * @return  string
     */
    private function setSearch(&$query)
    {
        $search = '%' . $this->_db->escape($this->state->get('search', ''), true) . '%';
        if ($search == '%%')
        {
            return;
        }
        $where = "(s.name_de LIKE '$search' OR s.name_en LIKE '$search' OR ";
        $where .= "s.short_name_de LIKE '$search' OR s.short_name_en LIKE '$search' OR ";
        $where .= "s.abbreviation_de LIKE '$search' OR s.abbreviation_en LIKE '$search' OR ";
        $where .= "s.externalID LIKE '$search' OR ";
        $where .= "t.surname LIKE '$search' OR t.forename LIKE '$search')";
        $query->where($where);
    }
}
