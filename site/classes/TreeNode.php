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
    public $children = NULL;

    function  __construct($id,$text,$iconCls,$leaf,$draggable,
            $singleClickExpand,$children) {

        $this->id = $id;
        $this->text = $text;
        $this->iconCls = $iconCls;
        $this->leaf = $leaf;
        $this->draggable = $draggable;
        $this->singleClickExpand = $singleClickExpand;
        $this->children = $children;
    }
}
?>
