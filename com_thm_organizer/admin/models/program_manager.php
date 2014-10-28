<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelProgram_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';

/**
 * Class THM_OrganizerModelProgram_Manager for component com_thm_organizer
 *
 * Class provides methods to deal with majors
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelProgram_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'subject';

    protected $defaultDirection = 'ASC';

    /**
     * Constructor to set the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('subject', 'abbreviation', 'version', 'field');
        }

        parent::__construct($config);
    }

    /**
     * Method to determine all majors
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $language = explode('-', JFactory::getLanguage()->getTag());
        $query = $this->_db->getQuery(true);
        $subjectColumn = "subject_{$language[0]}";
        $select = "$subjectColumn AS subject, version, lsfDegree, lsfFieldID, ";
        $select .= "dp.id as id, field, color, abbreviation, ";
        $parts = array("'index.php?option=com_thm_organizer&view=program_edit&id='","dp.id");
        $select .= $query->concatenate($parts, "") . "AS link ";
        $query->select($select);

        $query->from('#__thm_organizer_programs AS dp');
        $query->leftJoin('#__thm_organizer_degrees AS d ON dp.degreeID = d.id');
        $query->leftJoin('#__thm_organizer_fields AS f ON dp.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $searchColumns = array('subject_de', 'subject_en', 'version', 'field', 'd.name', 'description_de', 'description_en');
        $this->setSearchFilter($query, $searchColumns);
        $this->setValueFilters($query, array( 'degreeID', 'version', 'fieldID'));

        // Localized column search?
        $subjectFilter = $this->state->get('filter.subject', '');
        if ($subjectFilter !== '')
        {
            $query->where("$subjectColumn = '$subjectFilter'");
        }

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to create iterate table data
     *
     * @return  array  an array of arrays with preformatted teacher data
     */
    public function getItems()
    {
        $items = parent::getItems();
        $return = array();
        if (empty($items))
        {
            return $return;
        }

        $index = 0;
        foreach ($items as $item)
        {
            $return[$index] = array();
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['subject'] = JHtml::_('link', $item->link, $item->subject);
            $return[$index]['degreeID'] = JHtml::_('link', $item->link, $item->abbreviation);
            $return[$index]['version'] = JHtml::_('link', $item->link, $item->version);
            if (!empty($item->field))
            {
                if (!empty($item->color))
                {
                    $return[$index]['fieldID'] = THM_OrganizerHelperComponent::getColorField($item->field, $item->color);
                }
                else
                {
                    $return[$index]['fieldID'] = $item->field;
                }
            }
            else
            {
                $return[$index]['fieldID'] = '';
            }
            $index++;
        }
        return $return;
    }

    /**
     * Function to get table headers
     *
     * @return array including headers
     */
    public function getHeaders()
    {
        $ordering = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);

        $headers = array();
        $headers['checkbox'] = '';
        $headers['subject'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'subject', $direction, $ordering);
        $headers['degreeID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_DEGREE', 'abbreviation', $direction, $ordering);
        $headers['version'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_VERSION', 'version', $direction, $ordering);
        $headers['fieldID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'field', $direction, $ordering);

        return $headers;
    }
}
