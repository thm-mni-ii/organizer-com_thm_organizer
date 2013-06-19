<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view curriculum default
 * @description THM_Curriculum component site view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
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
$paramsString = JRequest::getVar('Itemid') . ", ";
$paramsString .= $this->params->get('program') . ", ";
$paramsString .= "'$this->lang', ";
$paramsString .= $this->params->get('width') . ", ";
$paramsString .= $this->params->get('height') . ", ";
$paramsString .= "'" . $this->params->get('semester_body_color') . "', ";
$paramsString .= $this->params->get('course_width') . ", ";
$paramsString .= "'" . $this->params->get('course_body_color') . "', ";
$paramsString .= "'" . $this->params->get('elective_pool_body_color') . "', ";
$paramsString .= $this->params->get('title_cut_length_activate') . ", ";
$paramsString .= $this->params->get('title_cut_length') . ", ";
$paramsString .= "'" . $this->params->get('scheduler_link') . "', ";
$paramsString .= $this->params->get('asset_line_break') . ", ";
$paramsString .= $this->params->get('elective_pool_window_line_break') . ", ";
$paramsString .= $this->params->get('compulsory_pool_line_break') . ", ";
$paramsString .= "$this->counter, ";
$paramsString .= "'" . $this->params->get('default_info_link') . "'";
?>
<script type="text/javascript">
/* global parameters */
css_suffix =  <?php echo "'" . $this->params->get('css_suffix') . "'"; ?>;
scheduler_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/scheduler_1.png";
note_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/info_1.png";
collab_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/collab.png";
responsible_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/user_1.png";
comp_pool_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/comp_pool_icon.png";
place_holder_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/icon_place_holder.png";
loading_icon = "<?php echo $this->baseurl; ?>/media/com_thm_organizer/images/curriculum/ajax-loader.gif";

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
<div style="text-align: center" id="loading_<?php echo $this->counter; ?>"></div>
<div class="iScroll" id="curriculum_<?php echo $this->counter; ?>"></div>
