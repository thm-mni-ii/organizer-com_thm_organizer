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
			$doc =& JFactory::getDocument();
			$doc->addStyleSheet(JURI::root(true)."/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css");

			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/DataAbstraction.php");
			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/TreeView.php");
			require_once(JPATH_ROOT."/components/com_thm_organizer/assets/classes/config.php");

			$JDA = new DataAbstraction();
			$CFG = new mySchedConfig($JDA);

			$treeView = new TreeView($JDA, $CFG);

			$treearr = $treeView->load();
?>

<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all-debug.js"></script>

<script type="text/javascript" charset="utf-8">
	var children = <?php echo json_encode($treearr["data"]["tree"]); ?>;
</script>

<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/models/fields/tree.js"></script>

<div id="tree-div"></div>

<?php
        }
}
