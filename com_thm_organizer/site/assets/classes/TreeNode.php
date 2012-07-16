<?php
/**
 *@category    Joomla component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer.site
 *@name		   TreeNode
 *@description TreeNode file from com_thm_organizer
 *@author	   Wolf Rost, wolf.rost@mni.thm.de
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license 	   GNU GPL v.2
 *@link		   www.mni.thm.de
 *@version	   1.0
 */

/**
 * Class TreeNode for component com_thm_organizer
 *
 * Class provides methods to create a tree node
 *
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       1.5
 */
class TreeNode
{
	/**
	 * Id
	 *
	 * @var String
	 * @since  1.0
	 */
	public $id = "";

	/**
	 * Text
	 *
	 * @var String
	 * @since  1.0
	 */
	public $text = "";

	/**
	 * Icon Class
	 *
	 * @var String
	 * @since  1.0
	 */
	public $iconCls = "";

	/**
	 * Is leaf
	 *
	 * @var Boolean
	 * @since  1.0
	 */
	public $leaf = true;

	/**
	 * Is dragable
	 *
	 * @var Boolean
	 * @since  1.0
	 */
	public $draggable = false;

	/**
	 * Single click expand the node
	 *
	 * @var Boolean
	 * @since  1.0
	 */
	public $singleClickExpand = false;

	/**
	 * Children
	 *
	 * @var Object
	 * @since  1.0
	 */
	public $children = null;

	/**
	 * GPunits id
	 *
	 * @var String
	 * @since  1.0
	 */
	public $gpuntisID = null;

	/**
	 * Plantype
	 *
	 * @var Integer
	 * @since  1.0
	 */
	public $plantype = null;

	/**
	 * Type
	 *
	 * @var String
	 * @since  1.0
	 */
	public $type = null;

	/**
	 * Semester id
	 *
	 * @var Integer
	 * @since  1.0
	 */
	public $semesterID = null;

	/**
	 * Is checked
	 *
	 * @var String
	 * @since  1.0
	 */
	public $checked = null;

	/**
	 * Is expanded
	 *
	 * @var Boolean
	 * @since  1.0
	 */
	public $expanded = false;

	/**
	 * Is public default
	 *
	 * @var String
	 * @since  1.0
	 */
	public $publicDefault = null;

	/**
	 * Node key
	 *
	 * @var String
	 * @since  1.0
	 */
	public $nodeKey = null;

	/**
	 * Class
	 *
	 * @var String
	 * @since  1.0
	 */
	public $cls = "";

	/**
	 * Constructor with the joomla data abstraction object and configuration object
	 *
	 * @param   Integer  $id  				 The node id
	 * @param   String	 $text  			 The node text
	 * @param   String	 $iconCls  			 The nodes icon class
	 * @param   Boolean	 $leaf  			 Is the node leaf
	 * @param   Boolean	 $draggable  		 Is the node dragable
	 * @param   Boolean	 $singleClickExpand  Should the node expand on single click
	 * @param   String	 $gpuntisID  		 The gpuntis id for this node
	 * @param   Integer	 $plantype  		 The nodes plantype
	 * @param   String	 $type  			 The nodes type (room, teacher, class)
	 * @param   Object	 $children  		 The nodes children
	 * @param   Integer	 $semesterID  		 In which semester is this node
	 * @param   String	 $checked  			 Is the node checked
	 * @param   String	 $publicDefault  	 Is this node the public default node
	 * @param   String	 $nodeKey  			 The node key
	 * @param   Boolean	 $expanded  		 A object which has configurations including
	 *
	 * @since  1.5
	 *
	 */
	public function  __construct($id,
	 $text,
	 $iconCls,
	 $leaf,
	 $draggable,
	 $singleClickExpand,
	 $gpuntisID,
	 $plantype,
	 $type,
	 $children,
	 $semesterID,
	 $checked,
	 $publicDefault,
	 $nodeKey,
	 $expanded)
	{

		$this->id = $id;
		$this->text = $text;
		$this->iconCls = $iconCls;
		$this->leaf = $leaf;
		$this->draggable = $draggable;
		$this->singleClickExpand = $singleClickExpand;
		$this->children = $children;
		$this->gpuntisID = $gpuntisID;
		$this->plantype = $plantype;
		$this->type = $type;
		$this->semesterID = $semesterID;
		$this->checked = $checked;
		$this->publicDefault = $publicDefault;
		$this->nodeKey = $nodeKey;
		$this->expanded = $expanded;
		if ($this->publicDefault === "default" && JRequest::getString('showSchedule') != "" && JRequest::getString('moduleID') != "")
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
