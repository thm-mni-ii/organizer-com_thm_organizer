<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view curriculum default
 * @description curriculum view default layout
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
if (!isset($this->counter))
{
	$this->counter = 0;
}
$flagClass = strlen($this->params->get('page_heading')) == 0? "flagWithoutTitle" : "flagWithTitle";

// Javascript Application construction parameters
$paramsString = JRequest::getVar('Itemid') . ", ";
$paramsString .= "{$this->params->get('programID')}, ";
$paramsString .= $this->params->get('horizontalGroups')? "{$this->params->get('horizontalGroups')}, " : "'', ";
$paramsString .= "'$this->languageTag', ";
$paramsString .= "{$this->params->get('width')}, ";
$paramsString .= "{$this->params->get('height')}, ";
$paramsString .= "'{$this->params->get('horizontalPanelHeaderColor')}', ";
$paramsString .= "'{$this->params->get('horizontalPanelColor')}', ";
$paramsString .= "'{$this->params->get('inlinePanelColor')}', ";
$paramsString .= "'{$this->params->get('modalPanelColor')}', ";
$paramsString .= "{$this->params->get('itemWidth')}, ";
$paramsString .= "{$this->params->get('itemHeight')}, ";
$paramsString .= "'{$this->params->get('itemColor')}', ";
$paramsString .= "{$this->params->get('titleCut')}, ";
$paramsString .= $this->params->get('titleLength')? "{$this->params->get('titleLength')}, " : "'', ";
$paramsString .= "{$this->params->get('maxItems')}, ";
$paramsString .= "{$this->params->get('spacing')}, ";
$paramsString .= $this->params->get('horizontalSpacing')? "{$this->params->get('horizontalSpacing')}, " : "'', ";
$paramsString .= $this->params->get('inlineSpacing')? "{$this->params->get('inlineSpacing')}, " : "'', ";
$paramsString .= $this->params->get('modalSpacing')? "{$this->params->get('modalSpacing')}" : "''";
?>
<script type="text/javascript">
/* global parameters */
suffix =  <?php echo "'" . $this->params->get('suffix') . "'"; ?>;
schedulerLink = <?php echo "'" . $this->params->get('schedulerLink') . "'"; ?>;
schedulerIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/scheduler_1.png";
ecollabLink = <?php echo "'" . $this->params->get('ecollabLink') . "'"; ?>;
ecollabIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/collab.png";
responsibleIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/user_1.png";
poolIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/comp_pool_icon.png";
placeHolderIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/icon_place_holder.png";
loadingIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/ajax-loader.gif";

window.addEvent('domready', function(){
            var appObj = new App(<?php echo $paramsString; ?>);
            appObj.performAjaxCall();
        });
</script>
<span>
<?php
if ($this->params->get('show_page_heading', 1) AND $this->params->get('plugin_mode', '0') != 1)
{
    echo "<h1 class='componentheading'>";
    echo $this->params->get('page_heading');
    echo "<a class='$flagClass' href='" . JRoute::_($this->langUrl) . "'>";
    echo "<img alt='$this->langLink' src='{$this->baseurl}/media/com_thm_organizer/images/curriculum/{$this->langLink}.png' />";
    echo "</a></h1>";
}
?>
</span>
<div style="text-align: center" id="loading"></div>
<div class="iScroll" id="curriculum"></div>
