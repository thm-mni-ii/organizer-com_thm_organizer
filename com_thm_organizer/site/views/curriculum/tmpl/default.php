<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        view curriculum default
 * @description curriculum view default layout
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

$flagPath = 'media' . DIRECTORY_SEPARATOR . 'com_thm_organizer' . DIRECTORY_SEPARATOR . 'images';
$flagPath .= DIRECTORY_SEPARATOR . 'curriculum' . DIRECTORY_SEPARATOR . ($this->languageTag == 'de') ? 'en' : 'de';

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
<?php
$schedulerLink = $this->params->get('schedulerLink');
if (!empty($schedulerLink))
{
    echo "schedulerLink = '" . $this->params->get('schedulerLink') . "';";
}
?>
schedulerIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/extjs/scheduler_1.png";
ecollabLink = <?php echo "'" . $this->ecollabLink . "'"; ?>;
ecollabIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/extjs/collab.png";
teacherIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/extjs/user_1.png";
poolIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/extjs/comp_pool_icon.png";
placeHolderIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/extjs/icon_place_holder.png";
loadingIcon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/extjs/ajax-loader.gif";

window.addEvent('domready', function(){
            var curriculumObj = new Curriculum(<?php echo $paramsString; ?>);
            curriculumObj.performAjaxCall();
        });
</script>
<?php
if ($this->params->get('show_page_heading', 1) AND $this->params->get('plugin_mode', '0') != 1)
{
?>
<div class="flag" style="float: right;">
    <a class='naviLink' href="<?php echo JRoute::_($this->langUrl); ?>">
        <img class="languageSwitcher"
             alt="<?php echo ($this->languageTag == 'de') ? 'en' : 'de'; ?>"
             src="<?php echo $flagPath; ?>" />
    </a>
</div>
<h1 class="componentheading"><?php echo 'Curriculum - ' ?><span id="programName"></span></h1>
<?php
}
?>
<div style="text-align: center" id="loading"></div>
<div class="iScroll" id="curriculum"></div>
<div class="curriculum_legend" id="curriculum_legend"></div>
