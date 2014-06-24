<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        node elements for schedule resource navigation
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Node class for resource schedule navigation
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerNode
{
    /**
     * Id
     *
     * @var String
     */
    public $id = "";

    /**
     * Node key
     *
     * @var String
     */
    public $nodeKey = null;

    /**
     * Text
     *
     * @var String
     */
    public $text = "";

    /**
     * Icon Class
     *
     * @var String
     */
    public $iconCls = "";

    /**
     * Is leaf
     *
     * @var Boolean
     */
    public $leaf = false;

    /**
     * Is dragable
     *
     * @var Boolean
     */
    public $draggable = false;

    /**
     * Single click expand the node
     *
     * @var Boolean
     */
    public $singleClickExpand = true;

    /**
     * Children
     *
     * @var Object
     */
    public $children = array();

    /**
     * GPunits id
     *
     * @var String
     */
    public $gpuntisID = '';

    /**
     * Type
     *
     * @var String
     */
    public $type = '';

    /**
     * Semester id
     *
     * @var Integer
     */
    public $semesterID = null;

    /**
     * Is checked
     *
     * @var String
     */
    public $checked = null;

    /**
     * Is expanded
     *
     * @var Boolean
     */
    public $expanded = false;

    /**
     * Is public default
     *
     * @var String
     */
    public $publicDefault = '';

    /**
     * Class
     *
     * @var String
     */
    public $cls = "";

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   array  $parameters  the configuration parameters
     */
    public function  __construct($parameters)
    {
        $this->id = $config['id'];
        $this->nodeKey = $config['nodeKey'];
        $this->text = $config['text'];
        $this->gpuntisID = $config['gpuntisID'];
        $this->type = $config['type'];
        $this->semesterID = $config['semesterID'];
        $this->iconCls = $config['iconCls'];
        $this->setCalculatedProperties($config);
    }

    /**
     * Sets individual node properties
     * 
     * @param   array  &$config  the checked nodes
     * 
     * @return  void  sets class properties
     */
    private function setCalculatedProperties(&$config)
    {
        $input = JFactory::getApplication()->input;
        $menuID = $input->getInt("menuID", 0);
        $frontend = empty($menuID)? true : false;
        $childrenCheckbox = $input->getBool("childrenCheckbox", false);
        if (($frontend AND !$childrenCheckbox) OR $childrenCheckbox)
        {
            $this->checked = null;
        }
        else
        {
            $this->checked = !empty($config['checked'][$this->id])? $config['checked'][$this->id] : "unchecked";
        }

        if (!empty($config['publicDefault']))
        {
            $firstValue = each($config['publicDefault']);

            if (strpos($firstValue["key"], $this->id) === 0)
            {
                $this->expanded = true;
            }
        }

        $showSchedule = $input->getString('showSchedule', '');
        $moduleID = $input->getString('moduleID', '');
        if ($this->publicDefault === "default" AND $showSchedule != '' AND $moduleID != '')
        {
            $this->cls = "MySchedSearchResult";
        }
    }
}
