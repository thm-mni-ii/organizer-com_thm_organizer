<?php
/**
 * @version     v0.0.1
 * @category	Joomla component
 * @package     THM_Oganizer
 * @subpackage  com_thm_organizer.site
 * @name        JFormFieldScheduler
 * @description Custom form field of com_thm_groups
 * @author      Wolf Rost, <Wolf.Rost@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

// No direct access to this file
defined('_JEXEC') or die;

// Import the list field type
jimport('joomla.form.helper');
jimport('extjs4.extjs4');
JFormHelper::loadFieldClass('list');

/**
 * Class JFormFieldScheduler for component com_thm_organizer
 *
 * Class provides methods to display a custom form field
 *
 * @category	Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 * @link        www.mni.thm.de
 * @since       v0.0.1
 */
class JFormFieldScheduler extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var    String
	 * @since  v0.0.1
	 */
	protected $type = 'Scheduler';

	/**
	 * Method to get the form field input
	 *
	 * @return  object  Custom form field
	 */
	protected function getInput()
	{
		$menuid = JRequest::getInt("id", 0);

		// Get database
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
		$query->select('params');
		$query->from($db->nameQuote('#__menu'));
		$query->where('id = ' . $menuid);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		if (count($rows) > 0)
		{
			$jsonObj = json_decode($rows[0]->params);
		}
		
		if (isset($jsonObj->publicDefaultID))
		{
			$publicDefaultID = $jsonObj->publicDefaultID;
		}
		else
		{
			$publicDefaultID = "";
		}

		if (isset($jsonObj->id))
		{
			$idString = $jsonObj->id;
		}
		else
		{
			$idString = "";
		}		

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css");
		$doc->addStyleSheet(JURI::root(true) . "/components/com_thm_organizer/models/fields/css/schedule_selection_tree.css");
		$doc->addScript(JURI::root(true) . "/components/com_thm_organizer/models/fields/tree.js");
		$doc->addScript(JURI::root(true) . "/components/com_thm_organizer/models/fields/departmentSemesterSelection.js");

		if ($idString != "")
		{
			$treeids = json_decode($idString);
		}
		else
		{
			$treeids = array();
		}
		
		
		?>

<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all.js"></script>-->

<!-- Ext 4 framework -->
<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all-dev.js"></script>-->
<!--<script type="text/javascript" charset="utf-8" src="../components/com_thm_organizer/views/scheduler/tmpl/ext/bootstrap.js"></script>-->

<script type="text/javascript" charset="utf-8">
	var menuID = <?php echo $menuid ?>;
	var treeIDs = <?php echo json_encode($treeids); ?>;
	var publicDefaultID = <?php echo json_encode($publicDefaultID); ?>;

	
	<?php
	
		echo 'var externLinks = [];' . "\n\t";
		echo 'externLinks.ajaxHandler = \'' . JRoute::_(JURI::root() . 'index.php?option=com_thm_organizer&view=ajaxhandler&format=raw') . '\';' . "\n\t";
	
		echo 'var images = [];' . "\n\t";
		echo 'images.unchecked = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/unchecked.gif\';' . "\n\t";
		echo 'images.unchecked_highlighted = \'' . JURI::root(true) .
		'/components/com_thm_organizer/models/fields/images/unchecked_highlighted.gif\';' . "\n\t";
		echo 'images.checked = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/checked.gif\';' . "\n\t";
		echo 'images.checked_highlighted = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/checked_highlighted.gif\';' . "\n\t";
		echo 'images.intermediate = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/intermediate.gif\';' . "\n\t";
		echo 'images.intermediate_highlighted = \'' . JURI::root(true) .
		'/components/com_thm_organizer/models/fields/images/intermediate_highlighted.gif\';' . "\n\t";
		echo 'images.selected = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/selected.gif\';' . "\n\t";
		echo 'images.selected_highlighted = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/selected_highlighted.gif\';' . "\n\t";
		echo 'images.notdefault = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/notdefault.png\';' . "\n\t";
		echo 'images.default = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/default.png\';' . "\n\t";
		echo 'images.base = \'' . JURI::root(true) . '/components/com_thm_organizer/models/fields/images/\';' . "\n\t";
	?>

	Joomla.submitbutton = function(task, type)
	{
		if(task == "item.apply" || task == "item.save" || task == "item.save2new" || task == "item.save2copy")
		{
			var paramID = Ext.get('jform_params_id');
			var treeChecked = tree.getChecked();
			var paramValue = Ext.encode(treeChecked);
			paramID.dom.value = paramValue;

			var paramID = Ext.get('jform_params_publicDefaultID');
			var publicDefault = tree.getPublicDefault();
			var paramValue = Ext.encode(publicDefault);
			paramID.dom.value = paramValue;
		}

		if (task == 'item.setType' || task == 'item.setMenuType') {
		if(task == 'item.setType') {
			document.id('item-form').elements['jform[type]'].value = type;
			document.id('fieldtype').value = 'type';
		} else {
			document.id('item-form').elements['jform[menutype]'].value = type;
		}
		Joomla.submitform('item.setType', document.id('item-form'));
		} else if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			Joomla.submitform(task, document.id('item-form'));
		} else {
			// special case for modal popups validation response
			$$('#item-form .modal-value.invalid').each(function(field){
				var idReversed = field.id.split("").reverse().join("");
				var separatorLocation = idReversed.indexOf('_');
				var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
				document.id(name).addClass('invalid');
			});
		}

	}
	
</script>

<style type="text/css">
.x-tree-node-cb {
	float: none;
}
</style>


<div style="width: auto; height: auto;" id="tree-div"></div>

<?php
	}
}
