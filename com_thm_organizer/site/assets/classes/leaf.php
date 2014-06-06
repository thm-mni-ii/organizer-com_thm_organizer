<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        leaf elements for schedule resource navigation
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

/**
 * Leaf class for resource schedule navigation
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerLeaf
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
    public $iconCls = "leaf-node";

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
    public $draggable = true;

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
     * 
     * @param type $leafID
     * @param type $leafKey
     * @param type $gpuntisID
     * @param type $type
     * @param type $semesterID
     */
    public function  __construct($paramters)
    {
        $this->id = $paramters['id'];
        $this->nodeKey = $paramters['nodeKey'];
        $this->gpuntisID = $paramters['resource']->gpuntisID;
        $this->type = $paramters['category'];
        $this->semesterID = $paramters['scheduleID'];
        $this->setName($paramters['resource']);
        $this->setDynamicProperties($paramters['checked'], $paramters['publicDefault']);
    }

    /**
     * Sets the name displayed for the leaf
     * 
     * @param   object  $resource  the resource associated with the leaf
     * 
     * @return  void  sets the object attribute text
     */
    private function setName(&$resource)
    {
        switch ($this->type)
        {
            case "teacher":
                if (!empty($resource->surname))
                {
                    $this->text = $resource->surname;
                    if (!empty($resource->forename))
                    {
                        $this->text .= ", " . $resource->forename{0} . ".";
                    }
                    break;
                }
                $this->text = $this->nodeKey;
                break;
            case "room":
                if (!empty($resource->longname))
                {
                    $this->text = $resource->longname;
                    break;
                }
                $this->text = $this->nodeKey;
                break;
            case  "module":
                if (!empty($resource->restriction))
                {
                    $this->text = $resource->restriction;
                    break;
                }
                $this->text = $this->nodeKey;
                break;
            case  "subject":
                if (!empty($resource->shortname))
                {
                    $this->text = $resource->shortname;
                    break;
                }
                if (!empty($resource->longname))
                {
                    $this->text = $resource->longname;
                    break;
                }
                $this->text = $this->nodeKey;
                break;
            default:
                $this->text = $resource->gpuntisID;
                break;
        }
    }

    /**
     * Sets individual leaf properties
     * 
     * @param   array  &$checked        the checked nodes
     * @param   array  &$publicDefault  the public default nodes
     * 
     * @return  void  sets class properties
     */
    private function setDynamicProperties(&$checked, &$publicDefault)
    {
        $menuID = JRequest::getInt("menuID", 0);
        $frontend = empty($menuID)? true : false;
        $childrenCheckbox = JRequest::getBool("childrenCheckbox", false);
        if ($frontend AND !$childrenCheckbox)
        {
            $this->checked = null;
        }
        else
        {
            $this->checked = !empty($checked[$this->id])? $checked[$this->id] : "unchecked";
        }

        if (!empty($publicDefault))
        {
            $firstValue = each($publicDefault);

            if (strpos($firstValue["key"], $this->id) === 0)
            {
                $this->expanded = true;
            }
            $this->publicDefault = isset($publicDefault[$this->id])?
                $publicDefault[$this->id] : "notdefault";
        }
        else
        {
            $publicDefault = "notdefault";
        }

        $showSchedule = JRequest::getString('showSchedule');
        $moduleID = JRequest::getString('moduleID');
        if ($this->publicDefault === "default" AND $showSchedule != '' AND $moduleID != "")
        {
            $this->cls = "MySchedSearchResult";
        }
    }
}
