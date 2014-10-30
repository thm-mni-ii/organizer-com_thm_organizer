<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');

/**
 * Provides method for generating a list of subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'ASC';

    public $programs = null;

    public $pools = null;

    /**
     * Constructor to set up the config array and call the parent constructor
     *
     * @param   Array  $config  Configuration  (default: Array)
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('name', 'externalID', 'fieldID');
        }
        parent::__construct($config);
    }

    /**
     * Method to select all existent assets from the database
     *
     * @return  Object  A query object
     */
    protected function getListQuery()
    {
        $dbo = JFactory::getDBO();
        $language = explode('-', JFactory::getLanguage()->getTag());

        // Create the sql query
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, externalID, name_{$language[0]} AS name, field, color";
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $searchFields = array('name_de', 'short_name_de', 'abbreviation_de', 'name_en', 'short_name_en',
                              'abbreviation_en', 'externalID', 'description_de', 'objective_de', 'content_de',
                              'description_en', 'objective_en', 'content_en'
                             );
        $this->setSearchFilter($query, $searchFields);

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fulfilling the request criteria
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
            $return[$index]['name'] = JHtml::_('link', $item->link, $item->name);
            $return[$index]['externalID'] = JHtml::_('link', $item->link, $item->externalID);
            $programName = THM_OrganizerHelperMapping::getProgramName('pool', $item->id);
            $return[$index]['programID'] = JHtml::_('link', $item->link, $programName);
            $poolName = THM_OrganizerHelperMapping::getPoolName('subject', $item->id);
            $return[$index]['programID'] = JHtml::_('link', $item->link, $programName);
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
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'subject', $direction, $ordering);
        $headers['externalID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_EXTERNAL_ID', 'externalID', $direction, $ordering);
        $headers['program'] = JText::_('COM_THM_ORGANIZER_PROGRAM');
        $headers['pool'] = JText::_('COM_THM_ORGANIZER_POOL');
        $headers['fieldID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'field', $direction, $ordering);

        return $headers;
    }
}
