<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelSubject_Manager
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2016 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/media/com_thm_organizer/models/list.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/componentHelper.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/language.php';
require_once JPATH_ROOT . '/media/com_thm_organizer/helpers/mapping.php';

/**
 * Provides method for generating a list of subjects
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelSubject_Manager extends THM_OrganizerModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'asc';

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
            $config['filter_fields'] = array('name', 'externalID', 'field');
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
        $shortTag = THM_OrganizerHelperLanguage::getShortTag();

        // Create the sql query
        $query = $dbo->getQuery(true);
        $select = "DISTINCT s.id, externalID, s.name_$shortTag AS name, field_$shortTag AS field, color, ";
        $parts = array("'index.php?option=com_thm_organizer&view=subject_edit&id='","s.id");
        $select .= $query->concatenate($parts, "") . " AS link ";
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $searchFields = array('name_de', 'short_name_de', 'abbreviation_de', 'name_en', 'short_name_en',
                              'abbreviation_en', 'externalID', 'description_de', 'objective_de', 'content_de',
                              'description_en', 'objective_en', 'content_en', 'lsfID'
                             );
        $this->setSearchFilter($query, $searchFields);
        $this->setValueFilters($query, array('externalID'));
        $this->setLocalizedFilters($query, array('name', 'field'));

        $programID = $this->state->get('list.programID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('list.poolID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $poolID, 'pool', 'subject');

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
            if (!empty($item->field))
            {
                if (!empty($item->color))
                {
                    $return[$index]['field'] = THM_OrganizerHelperComponent::getColorField($item->field, $item->color);
                }
                else
                {
                    $return[$index]['field'] = $item->field;
                }
            }
            else
            {
                $return[$index]['field'] = '';
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
        $headers['name'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_NAME', 'name', $direction, $ordering);
        $headers['externalID'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_EXTERNAL_ID', 'externalID', $direction, $ordering);
        $headers['field'] = JHtml::_('searchtools.sort', 'COM_THM_ORGANIZER_FIELD', 'field', $direction, $ordering);

        return $headers;
    }

    /**
     * Overwrites the JModelList populateState function
     *
     * @param   string  $ordering   the column by which the table is should be ordered
     * @param   string  $direction  the direction in which this column should be ordered
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $session = JFactory::getSession();
        $session->clear('programID');
        $formProgramID = $this->state->get('list.programID', '');
        if (!empty($formProgramID))
        {
            $session->set('programID', $formProgramID);
        }
    }
}
