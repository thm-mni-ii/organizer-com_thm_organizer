<?php
/*
 * Created on 28.01.2011
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class TreeNode {

    public $id = "";
    public $text = "";
    public $iconCls = "";
    public $leaf = true;
    public $draggable = false;
    public $singleClickExpand = false;
    public $children = null;
    public $gpuntisID = null;
    public $plantype = null;
    public $type = null;
    public $semesterID = null;
    public $checked = null;
    public $expanded = false;
    public $publicDefault = null;
    public $nodeKey = null;

    public function  __construct(	$id,
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
    						$nodeKey) {

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
    }

    public function setChildren($children)
    {
    	$this->children = $children;
    }
}
?>
