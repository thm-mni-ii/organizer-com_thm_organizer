<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModelIndex
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');

/**
 * Class creates a model
 *
 * @category    Joomla.Component.Site
 * @package     thm_urriculum
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelCurriculum extends JModel
{
    private $_id;

    private $_languageTag;

    public $name = '';

    public $description = '';

    public $fields = array();

    public $icons = array();

    public function __construct()
    {
        parent::__construct();
        $params = JFactory::getApplication()->getMenu()->getActive()->params;
        $this->_id = $params->get('programID');
        $this->_languageTag = JRequest::getVar('languageTag', $params->get('language'));
        $this->setNameAndDescription();
//        $this->setFields();
//        $this->setIcons();
    }

    /**
     * Gets the name of the program selected
     *
     * @param   int  $programID  the id of the degree program being modeled
     *
     * @return  string  the name of the program
     */
    public function setNameAndDescription()
    {
        $query = $this->_db->getQuery(true);
        $select = "CONCAT(p.subject_{$this->_languageTag}, ' (', d.abbreviation, ' ', p.version, ')') AS name, ";
        $select .= "p.description_{$this->_languageTag} AS description";
        $query->select($select);
        $query->from('#__thm_organizer_programs AS p')->innerJoin('#__thm_organizer_degrees AS d ON p.degreeID = d.id');
        $query->where("p.id = '{$this->_id}'");
        $this->_db->setQuery((string) $query);
        $results = $this->_db->loadAssoc();
        if (!empty($results) AND count($results) == 2)
        {
            $this->name = $results['name'];
            $this->description = $results['description'];
        }
    }
}
