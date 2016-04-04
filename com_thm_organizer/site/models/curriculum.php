<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelIndex
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.helpers.corehelper');
require_once JPATH_COMPONENT . '/helpers/teacher.php';

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_urriculum
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCurriculum extends JModelItem
{
    private $langTag;

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     */
    public function getItem()
    {
        $app = JFactory::getApplication();
        $params = $app->getParams();
        $program = new stdClass;

        $programID = $params->get('programID', 0);
        if (empty($programID))
        {
            return $program;
        }

        $program->id = $programID;

        $defaultLang = THM_CoreHelper::getLanguageShortTag();
        $this->langTag = $app->input->get('languageTag', $defaultLang);

        $this->setProgramInformation($program);
        if (empty($program->name))
        {
            return $program;
        }

        $this->setChildren($program, 'program');

        return $program;
    }

    /**
     * Sets program attributes
     *
     * @param   object  &$program  the object modeling the program data
     *
     * @return  void  sets object attributes
     */
    private function setProgramInformation(&$program)
    {
        $query = $this->_db->getQuery(true);
        $query->select("p.subject_$this->langTag AS name, d.abbreviation, p.version, m.id AS mapping");
        $query->from('#__thm_organizer_programs AS p');
        $query->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = p.id');
        $query->where("p.id = '$program->id'");
        $this->_db->setQuery((string) $query);
        try
        {
            $programData = $this->_db->loadAssoc();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return;
        }

        $program->name = "{$programData['name']} ({$programData['abbreviation']} {$programData['version']})";
        $program->mapping = $programData['mapping'];
        $program->type = 'program';
    }

    /**
     * Sets the children for the given element
     *
     * @param   object  &$element  the object modeling the program data
     *
     * @return  void  sets object attributes
     */
    private function setChildren(&$element)
    {
        $query = $this->_db->getQuery(true);
        $query->select('*');
        $query->from('#__thm_organizer_mappings');
        $query->where("parentID = '$element->mapping'");
        $query->order("ordering ASC");
        $this->_db->setQuery((string) $query);
        try
        {
            $children = $this->_db->loadObjectList();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return;
        }

        $element->children = array();
        foreach ($children as $child)
        {
            $order = (int) $child->ordering;
            if (!empty($child->poolID))
            {
                $element->children[$order] = $this->getPool($child->poolID, $child->id);
                if (empty($element->children[$order]))
                {
                    continue;
                }

                $this->setChildren($element->children[$order]);
            }

            // Programs should not have subjects as children this can happen through false modelling in LSF.
            if ($element->type == 'program')
            {
                continue;
            }

            if (!empty($child->subjectID))
            {
                $element->children[$order] = $this->getSubject($child->subjectID, $child->id);
            }
        }
    }

    /**
     * Retrieves a pool element
     *
     * @param   int  $poolID     the pool id
     * @param   int  $mappingID  the mapping id
     *
     * @return  mixed  object on success, otherwise null
     */
    private function getPool($poolID, $mappingID)
    {
        $query = $this->_db->getQuery(true);
        $query->select("p.id, name_$this->langTag AS name, description_$this->langTag AS description, minCrP, maxCrP, enable_desc, color AS bgColor");
        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON f.id = p.fieldID');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("p.id = '$poolID'");
        $this->_db->setQuery((string) $query);

        try
        {
            $pool = $this->_db->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return null;
        }

        if (empty($pool))
        {
            return null;
        }

        $pool->mapping = $mappingID;
        $pool->type = 'pool';

        return $pool;
    }

    /**
     * Retrieves a subject element
     *
     * @param   int  $subjectID  the subject id
     * @param   int  $mappingID  the mapping id
     *
     * @return  mixed  object on success, otherwise null
     */
    private function getSubject($subjectID, $mappingID)
    {
        $query = $this->_db->getQuery(true);

        $select = "s.id, externalID, name_$this->langTag AS name, creditpoints AS CrP, color AS bgColor, ";
        $menuID = JFactory::getApplication()->input->getInt('Itemid', 0);
        $menuIDParam = empty($menuID)? '' : "&Itemid=$menuID";
        $subjectLink = "'index.php?option=com_thm_organizer&view=subject_details&languageTag={$this->langTag}{$menuIDParam}&id='";
        $parts = array("$subjectLink","s.id");
        $select .= $query->concatenate($parts, "") . " AS link";

        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON f.id = s.fieldID');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where("s.id = '$subjectID'");
        $this->_db->setQuery((string) $query);

        try
        {
            $subject = $this->_db->loadObject();
        }
        catch (Exception $exc)
        {
            JFactory::getApplication()->enqueueMessage(JText::_("COM_THM_ORGANIZER_MESSAGE_DATABASE_ERROR"), 'error');
            return null;
        }

        if (empty($subject))
        {
            return null;
        }

        $subject->mapping = $mappingID;
        $subject->type = 'subject';

        $teacher = THM_OrganizerHelperTeacher::getDataBySubject($subject->id, 1);
        if (!empty($teacher))
        {
            $subject->teacherName = THM_OrganizerHelperTeacher::getDefaultName($teacher);
            $subject->teacherID = $teacher['id'];
        }

        return $subject;
    }
}
