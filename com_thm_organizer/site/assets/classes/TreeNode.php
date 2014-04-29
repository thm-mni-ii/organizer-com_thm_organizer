<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        TreeNode
 * @description TreeNode file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Class TreeNode for component com_thm_organizer
 *
 * Class provides methods to create a tree node
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 */
class THMTreeNode
{
    /**
     * Id
     *
     * @var String
     */
    public $id = "";

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
    public $leaf = true;

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
    public $singleClickExpand = false;

    /**
     * Children
     *
     * @var Object
     */
    public $children = null;

    /**
     * GPunits id
     *
     * @var String
     */
    public $gpuntisID = null;

    /**
     * Type
     *
     * @var String
     */
    public $type = null;

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
    public $publicDefault = null;

    /**
     * Node key
     *
     * @var String
     */
    public $nodeKey = null;

    /**
     * Class
     *
     * @var String
     */
    public $cls = "";

    /**
     * Constructor with the joomla data abstraction object and configuration object
     *
     * @param   Object   $data           Contains the node id, node text, nodes icon class, leaf, dragable, single click, gpuntis id,
     *                                   type (room, teacher, class), children, semester, node key
     * @param   String   $checked        Is the node checked
     * @param   String   $publicDefault  Is this node the public default node
     * @param   String   $nodeKey        The node key
     * @param   Boolean  $expanded       A object which has configurations including
     */
    public function  __construct($data, $checked, $publicDefault, $nodeKey, $expanded)
    {
        $this->id = $data["nodeID"];
        $this->text = $data["text"];
        $this->iconCls = $data["iconCls"];
        $this->leaf = $data["leaf"];
        $this->draggable = $data["draggable"];
        $this->singleClickExpand = $data["singleClickExpand"];
        $this->children = $data["children"];
        $this->gpuntisID = $data["gpuntisID"];
        $this->type = $data["type"];
        $this->semesterID = $data["semesterID"];
        $this->checked = $checked;
        $this->publicDefault = $publicDefault;
        $this->nodeKey = $nodeKey;
        $this->expanded = $expanded;
        if ($this->publicDefault === "default" && JFactory::getApplication()->input->getString('showSchedule') != "" && JFactory::getApplication()->input->getString('moduleID') != "")
        {
            $this->cls = "MySchedSearchResult";
        }
    }

    /**
     * Method to set the children
     *
     * @param   Array  $children  The children for this node
     *
     * @return void
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }
}
