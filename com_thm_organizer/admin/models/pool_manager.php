<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.admin
 * @name        THM_OrganizerModelPool_Manager
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('thm_core.list.model');
require_once JPATH_COMPONENT . '/assets/helpers/mapping.php';

/**
 * Class provides functions for displaying a list of pools
 *
 * @category    Joomla.Component.Admin
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.admin
 */
class THM_OrganizerModelPool_Manager extends THM_CoreModelList
{
    protected $defaultOrdering = 'name';

    protected $defaultDirection = 'ASC';

    public $programName = '';
 
    public $programs = null;

    /**
     * constructor
     *
     * @param   array  $config  configurations parameter
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array('name');
        }
        parent::__construct($config);
    }

    /**
     * Method to overwrite the getItems method in order to set the program name
     *
     * @return  array  an array of objects fullfilling the request criteria
     */
    public function getItems()
    {
        $items = parent::getItems();
        $return = array();
        if (empty($items))
        {
            return $return;
        }

        $params = JComponentHelper::getParams('com_thm_organizer');

        $index = 0;
        foreach ($items as $item)
        {
            // Set default attributes
            if (!empty($item->useDefaults))
            {
                $item->displayBehaviour = $params->get('display');
                $item->content = $params->get('content');
            }

            $return[$index] = array();
            $return[$index]['checkbox'] = JHtml::_('grid.id', $index, $item->id);
            $return[$index]['name'] = JHtml::_('link', $item->link, $item->name);
            $return[$index]['program'] = JHtml::_('link', $item->link, $item->ip);
            $controller = 'monitor';
            $tip = JText::_('COM_THM_ORGANIZER_TOGGLE_COMPONENT_SETTINGS');
            $return[$index]['useDefaults'] = $this->getToggle($item->id, $item->useDefaults, $controller, $tip);
            $return[$index]['display'] = JHtml::_('link', $item->link, $this->displayBehaviour[$item->display]);
            $return[$index]['content'] = JHtml::_('link', $item->link, $item->content);
            $index++;
        }
        return $return;
    }

    /**
     * Method to select the tree of a given major
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $query = $this->_db->getQuery(true);

        $language = explode('-', JFactory::getLanguage()->getTag());
        $select = "DISTINCT p.id, name_{$language[0]} AS name, lsfID, hisID, ";
        $select .= 'externalID, minCrP, maxCrP, f.field, color';
        $query->select($select);

        $query->from('#__thm_organizer_pools AS p');
        $query->leftJoin('#__thm_organizer_fields AS f ON p.fieldID = f.id');
        $query->leftJoin('#__thm_organizer_colors AS c ON f.colorID = c.id');

        $programID = $this->state->get('filter.program', '-1');
        if (!empty($programID) OR $programID != '-1')
        {
            // Pools which aren't associated with
            if ($programID == '-2')
            {
                $where = "p.id NOT IN ( ";
                $where .= "SELECT poolID FROM #__thm_organizer_mappings ";
                $where .= "WHERE poolID IS NOT null )";
                $query->where($where);
            }
            else
            {
                $borders = $this->getBorders($programID, 'program');
                if (!empty($borders))
                {
                    $query->innerJoin('#__thm_organizer_mappings AS m ON m.poolID = p.id');
                    $query->where("lft > '{$borders['lft']}'");
                    $query->where("rgt < '{$borders['rgt']}'");
                }
            }
        }

        $search = '%' . $this->_db->escape($this->state->get('filter.search'), true) . '%';
        if ($search != '%%')
        {
            $searchClause = "(name_{$language[0]} LIKE '$search' ";
            $searchClause .= "OR short_name_{$language[0]} LIKE '$search' ";
            $searchClause .= "OR abbreviation_{$language[0]} LIKE '$search')";
            $query->where($searchClause);
        }

        $this->setOrdering($query);
 
        return $query;
    }

    /**
     * Retrieves the mapped left and right values for the requested program
     *
     * @param   int     $resourceID  the id of the pool
     * @param   string  $type        the type of resource being checked
     *
     * @return  array contains the sought left and right values
     */
    private function getBorders($resourceID, $type = 'pool')
    {
        $query = $this->_db->getQuery(true);
        $query->select('DISTINCT lft, rgt')->from('#__thm_organizer_mappings');
        $query->where("{$type}ID = '$resourceID'");
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $borders = $type == 'pool'? $this->_db->loadAssocList() : $this->_db->loadAssoc();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $borders;
    }

    /**
     * Retrieves the names of the programs to which a pool is ordered
     *
     * @param   array  $poolBorders  the left and right values of the pool's
     *                               mappings
     *
     * @return  array  the names of the programs to which the pool is ordered
     */
    private function getPoolPrograms($poolBorders)
    {
        $bordersClauses = array();
        foreach ($poolBorders AS $border)
        {
            $bordersClauses[] = "( lft < '{$border['lft']}' AND rgt > '{$border['rgt']}')";
        }

        $language = explode('-', JFactory::getLanguage()->getTag());
        $query = $this->_db->getQuery(true);
        $parts = array("dp.subject_{$language[0]}","' ('", "d.abbreviation", "' '", "dp.version", "')'");
        $select = "DISTINCT " . $query->concatenate($parts, "") . " As name";
        $query->select($select);       
        $query->from('#__thm_organizer_programs AS dp');
        $query->innerJoin('#__thm_organizer_mappings AS m ON m.programID = dp.id');
        $query->leftJoin('#__thm_organizer_degrees AS d ON d.id = dp.degreeID');
        $query->where($bordersClauses, 'OR');
        $query->order('name');
        $this->_db->setQuery((string) $query);
        
        try 
        {
            $programs = $this->_db->loadColumn();
        }
        catch (runtimeException $e)
        {
            throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
        }
        
        return $programs;
    }

    /**
     * Retrieves a string value representing the degree programs to which the
     * pool is ordered.
     *
     * @param   int  $poolID  the id of the pool
     *
     * @return  string  string representing the associated program
     */
    private function getProgram($poolID)
    {
        $poolBorders = $this->getBorders($poolID);
        if (empty($poolBorders))
        {
            return JText::_('COM_THM_ORGANIZER_POM_NO_MAPPINGS');
        }
        $programs = $this->getPoolPrograms($poolBorders);
        if (count($programs) === 1)
        {
            return $programs[0];
        }
        else
        {
            return JText::_('COM_THM_ORGANIZER_POM_MULTIPLE_MAPPINGS');
        }
    }
}
