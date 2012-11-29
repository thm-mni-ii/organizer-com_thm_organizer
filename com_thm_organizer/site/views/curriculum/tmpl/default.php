<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Curriculum
 * @subpackage  com_thm_organizer.site
 * @name		view curriculum default
 * @description THM_Curriculum component site view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
jimport('thm_extjs4.thm_extjs4');

if (!isset($this->counter))
{
	$this->counter = 0;
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>


<link rel="stylesheet" type="text/css"
	href="components/com_thm_organizer/views/curriculum/tmpl/extjs/curriculum-minify.css" />

<script
	src="components/com_thm_organizer/views/curriculum/tmpl/extjs/curriculum.js"
	type="text/javascript"></script>

<script
	src="components/com_thm_organizer/views/curriculum/tmpl/extjs/app-minify.js"
	type="text/javascript"></script>




<script type="text/javascript">

            /* global parameters */
            css_suffix =  <?php echo "'" . $this->params->get('css_suffix') . "'"; ?>;
            scheduler_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/scheduler_1.png";
            note_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/info_1.png";
            collab_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/collab.png";
            responsible_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/user_1.png";
            comp_pool_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/comp_pool_icon.png";
            place_holder_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/icon_place_holder.png";
            loading_icon = "<?php echo JURI::base(); ?>components/com_thm_organizer/css/images/ajax-loader.gif";


            window.addEvent('domready',function(){
                var appObj = new App(
<?php echo JRequest::getVar('Itemid'); ?>,
<?php echo $this->params->get('major'); ?>,
        "<?php echo implode(",", $this->params->get('semesters')); ?>",
<?php echo "'" . $this->lang . "'"; ?>,
<?php echo $this->params->get('width'); ?>,
<?php echo $this->params->get('height'); ?>,
<?php echo "'" . $this->params->get('semester_body_color') . "'"; ?>,
<?php echo $this->params->get('course_width'); ?>,
<?php echo "'" . $this->params->get('course_body_color') . "'"; ?>,
<?php echo "'" . $this->params->get('elective_pool_body_color') . "'"; ?>,
<?php echo $this->params->get('title_cut_length_activate'); ?>,
<?php echo $this->params->get('title_cut_length'); ?>,
        "<?php echo $this->params->get('scheduler_link'); ?>",
<?php echo $this->params->get('asset_line_break'); ?>,
<?php echo $this->params->get('elective_pool_window_line_break'); ?>,
<?php echo $this->params->get('compulsory_pool_line_break'); ?>,
<?php echo $this->counter; ?>,
        "<?php echo $this->params->get('default_info_link'); ?>"
  
    );
        		
        appObj.performAjaxCall();
    });

        </script>
</head>
<body>

	<?php
		$page_heading = $this->params->get('page_heading');
	?>

	<span> <?php 
	if ($this->params->get('show_page_heading', 1) && $this->params->get('plugin_mode', '0') != 1)
	{
		?>
		<h1 class="componentheading">
			<?php echo $page_heading; ?>
		<?php 
	}

	if ($this->params->get('plugin_mode', '0') != 1)
	{
		$flagClass = "flagWithTitle";
		if(strlen($page_heading) == 0)
		{
			$flagClass = "flagWithoutTitle";
		}
		else
		{
			$flagClass = "flagWithTitle";
		}
		?> <a class='<?php echo $flagClass; ?>' href="<?php echo JRoute::_($this->langUrl); ?>"><img
			alt="<?php echo $this->langLink; ?>"
			src="components/com_thm_organizer/css/images/<?php echo $this->langLink; ?>.png" />
		</a> 
		</h1><?php 
	}
	?>
	</span>
	<div style="text-align: center"
		id="loading_<?php echo $this->counter; ?>"></div>
	<div class="iScroll" id="curriculum_<?php echo $this->counter; ?>"></div>

</body>
</html>
