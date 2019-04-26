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

require_once 'list.php';
require_once JPATH_ROOT . '/components/com_thm_organizer/Helpers/mapping.php';

use Joomla\CMS\Factory;

/**
 * Class retrieves information for a filtered set of subjects.
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
     * @param array $config Configuration  (default: array)
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = ['name', 'externalID', 'field'];
        }

        parent::__construct($config);
    }

    /**
     * Method to select all existent assets from the database
     *
     * @return \JDatabaseQuery  the query object
     */
    protected function getListQuery()
    {
        $allowedDepartments = THM_OrganizerHelperAccess::getAccessibleDepartments('document');
        $dbo                = Factory::getDbo();
        $shortTag           = Languages::getShortTag();

        // Create the sql query
        $query  = $dbo->getQuery(true);
        $select = "DISTINCT s.id, externalID, s.name_$shortTag AS name, field_$shortTag AS field, color, ";
        $parts  = ["'index.php?option=com_thm_organizer&view=subject_edit&id='", 's.id'];
        $select .= $query->concatenate($parts, '') . ' AS link ';
        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_fields AS f ON s.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');
        $query->where('(s.departmentID IN (' . implode(',', $allowedDepartments) . ') OR s.departmentID IS NULL)');

        $searchFields = [
            's.name_de',
            'short_name_de',
            'abbreviation_de',
            's.name_en',
            'short_name_en',
            'abbreviation_en',
            'externalID',
            'description_de',
            'objective_de',
            'content_de',
            'description_en',
            'objective_en',
            'content_en',
            'lsfID'
        ];

        $this->setSearchFilter($query, $searchFields);
        $this->setValueFilters($query, ['externalID']);
        $this->setLocalizedFilters($query, ['name', 'field']);

        $programID = $this->state->get('list.programID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $programID, 'program', 'subject');
        $poolID = $this->state->get('list.poolID', '');
        THM_OrganizerHelperMapping::setResourceIDFilter($query, $poolID, 'pool', 'subject');
        $isPrepCourse = $this->state->get('list.is_prep_course', '');
        if ($isPrepCourse !== "") {
            $query->where("is_prep_course = $isPrepCourse");
        }

        $this->setOrdering($query);

        return $query;
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return array  an array of objects fulfilling the request criteria
     */
    public function getItems()
    {
        $items  = parent::getItems();
        $return = [];

        if (empty($items)) {
            return $return;
        }

        $index = 0;

        foreach ($items as $item) {
            $return[$index]               = [];
            $return[$index]['checkbox']   = HTML::_('grid.id', $index, $item->id);
            $return[$index]['name']       = HTML::_('link', $item->link, $item->name);
            $return[$index]['externalID'] = HTML::_('link', $item->link, $item->externalID);
            if (!empty($item->field)) {
                if (!empty($item->color)) {
                    $return[$index]['field'] = HTML::colorField($item->field, $item->color);
                } else {
                    $return[$index]['field'] = $item->field;
                }
            } else {
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
        $ordering  = $this->state->get('list.ordering', $this->defaultOrdering);
        $direction = $this->state->get('list.direction', $this->defaultDirection);
        $headers   = [];

        $headers['checkbox']   = '';
        $headers['name']       = HTML::sort('NAME', 'name', $direction, $ordering);
        $headers['externalID'] = HTML::sort('EXTERNAL_ID', 'externalID', $direction, $ordering);
        $headers['field']      = HTML::sort('FIELD', 'field', $direction, $ordering);

        return $headers;
    }

    /**
     * Method to get the total number of items for the data set.
     *
     * @param string $idColumn not used
     *
     * @return integer  The total number of items available in the data set.
     */
    public function getTotal($idColumn = null)
    {
        $query = $this->getListQuery();
        $query->clear('select');
        $query->clear('order');
        $query->select('COUNT(DISTINCT s.id)');
        $dbo = Factory::getDbo();
        $dbo->setQuery($query);

        return (int)OrganizerHelper::executeQuery('loadResult');
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        parent::populateState($ordering, $direction);

        $session = Factory::getSession();
        $session->clear('programID');
        $formProgramID = $this->state->get('list.programID', '');
        if (!empty($formProgramID)) {
            $session->set('programID', $formProgramID);
        }
    }
}
