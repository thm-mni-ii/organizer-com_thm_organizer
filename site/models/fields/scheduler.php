<?php
// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
jimport('thm_extjs4.thm_extjs4');
JFormHelper::loadFieldClass('list');

/**
 * HelloWorld Form Field class for the HelloWorld component
 */
class JFormFieldScheduler extends JFormField
{
        protected $type = 'Scheduler';

        protected function getInput()
        {

			$menuid = JRequest::getInt("id", 0);

			//get database
			$db		= JFactory::getDbo();
			$query	= $db->getQuery( true );
			$query->select('params');
			$query->from($db->nameQuote('#__menu'));
			$query->where('id = '.$menuid);
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			if(count($rows) > 0)
				$jsonObj = json_decode( $rows[0]->params );

			if(isset($jsonObj->id))
				$idString = $jsonObj->id;
			else
				$idString = "";

			if(isset($jsonObj->publicDefaultID))
				$publicDefaultString = $jsonObj->publicDefaultID;
			else
				$publicDefaultString = "";

			$doc =& JFactory::getDocument();
			$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css");
			$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/models/fields/css/schedule_selection_tree.css");

			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/DataAbstraction.php");
			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/TreeView.php");
			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/config.php");

			$JDA = new DataAbstraction();
			$CFG = new mySchedConfig($JDA);

			if($idString != "")
				$treeids = json_decode($idString);
			else
				$treeids = array();

			if($publicDefaultString != "")
				$publicDefaultID = json_decode($publicDefaultString);
			else
				$publicDefaultID = array();

			$treeView = new TreeView($JDA, $CFG, array("path"=>$treeids, "hide"=>false, "publicDefault"=>$publicDefaultID));

			$treearr = $treeView->load();

?>

<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all.js"></script>-->

<!-- Ext 4 framework -->
<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all-dev.js"></script>-->
<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/bootstrap.js"></script>-->

<script type="text/javascript" charset="utf-8">
	var children = <?php echo json_encode($treearr["data"]["tree"]); ?>;

	<?php
		echo 'var images = [];';
		echo 'images.unchecked = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/unchecked.gif\';';
		echo 'images.unchecked_highlighted = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/unchecked_highlighted.gif\';';
		echo 'images.checked = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/checked.gif\';';
		echo 'images.checked_highlighted = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/checked_highlighted.gif\';';
		echo 'images.intermediate = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/intermediate.gif\';';
		echo 'images.intermediate_highlighted = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/intermediate_highlighted.gif\';';
		echo 'images.selected = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/selected.gif\';';
		echo 'images.selected_highlighted = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/selected_highlighted.gif\';';
		echo 'images.notdefault = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/notdefault.png\';';
		echo 'images.default = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/default.png\';';
		echo 'images.base = \''.JURI::root(true).'/components/com_thm_organizer/models/fields/images/\';';
	?>
</script>

<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/models/fields/tree.js"></script>

<style type="text/css">

.x-tree-node-cb {
	float: none;
}

</style>


<div style="width:auto;height:auto;" id="tree-div"></div>

<?php
        }
}
