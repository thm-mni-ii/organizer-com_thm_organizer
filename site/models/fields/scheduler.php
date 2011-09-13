<?php
// No direct access to this file
defined('_JEXEC') or die;

// import the list field type
jimport('joomla.form.helper');
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

			$doc =& JFactory::getDocument();
			$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css");

			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/DataAbstraction.php");
			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/TreeView.php");
			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/config.php");

			$JDA = new DataAbstraction();
			$CFG = new mySchedConfig($JDA);

			$treeids = explode("/",$idString);

			$treeView = new TreeView($JDA, $CFG, array("path"=>$treeids, "hide"=>false));

			$treearr = $treeView->load();

?>

<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all.js"></script>-->

<!-- Ext 4 framework -->
	<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all-dev.js"></script>

<script type="text/javascript" charset="utf-8">
	var children = <?php echo json_encode($treearr["data"]["tree"]); ?>;
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
