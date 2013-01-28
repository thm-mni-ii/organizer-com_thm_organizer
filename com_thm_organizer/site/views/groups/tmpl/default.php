<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view groups default
 * @description THM_Curriculum component site view
 * @author	    Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
?>

<script type="text/javascript">
    window.addEvent('domready', function(){ 
        var JTooltips = new Tips($$('.hasTip2'), {
            maxTitleChars: 50, showDelay: 500, hideDelay: 500, className: 'custom2', 
            fixed: true, 
            onShow: function(tip) {tip.effect('opacity', {duration: 500, wait: false}).start(0,1)}, 
            onHide: function(tip) {tip.effect('opacity', {duration: 500, wait: false}).start(1,0)}
        }); 
    });

</script>


<?php
if ($this->params->get('plugin_mode') != 1)
{
	?>
<span class="flag" style="float: right;"> <a class='naviLink'
	href="<?php echo JRoute::_($this->langUrl); ?>"><img
		alt="<?php echo $this->langLink; ?>"
		src="components/com_thm_organizer/css/images/<?php echo $this->langLink; ?>.png" />
</a>
</span>

<?php
}

if ($this->params->get('show_page_heading'))
{
	?>
<h1 class="componentheading">
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php
}
$suffix = null;

if ($suffix = $this->params->get('lsf_navi', 0))
{
	if ($this->lang == 'de')
	{
		$txtFachgruppen = "...nach Fachgruppen";
		$txtDozenten = "...nach Dozenten";
		$txtUEberblick = "...im &Uuml;berblick";
	}
	else
	{
		$txtFachgruppen = "...by Groups";
		$txtDozenten = "...by Lecturer";
		$txtUEberblick = "...Overview";
	}
	?>

<div class="navi">
	<span class="buttonNavi"> <a class='naviLink'
		href="<?php echo JRoute::_("index.php?option=com_thm_organizer&view=groups&Itemid=" . JRequest::getVar('Itemid')); ?>">
			<?php echo $txtFachgruppen; ?>
	</a>
	</span> <span class="buttonNavi"> <a class='naviLink'
		href="<?php echo JRoute::_("index.php?option=com_thm_organizer&view=lecturers&Itemid=" . JRequest::getVar('Itemid')); ?>">
			<?php echo $txtDozenten; ?>
	</a>
	</span> <span class="buttonNavi"> <a class='naviLink'
		href="<?php echo JRoute::_("index.php?option=com_thm_organizer&view=index&Itemid=" . JRequest::getVar('Itemid')); ?>">
			<?php echo $txtUEberblick; ?>
	</a>
	</span>
</div>

<?php
}
else
{

}
?>

<div id='startTable'>


	<?php
	$k = 0;
	for ($i = 0; $i < count($this->groups); $i++)
	{
		if (!isset($this->groups[$i][1]))
		{
			continue;
		}
		else
		{

		}

		if ($this->groups[$i] != null)
		{
			?>
	<big><b><?php echo $this->groups[$i][0]; ?> </b> </big>
	<table class="category">
		<thead>
			<tr>

				<th width="55%"><?php
				if ($this->lang == 'de')
				{
					echo JText::_("Modultitel");
				}
				else
				{
					echo JText::_("Course");
				}
				?>
				</th>
				<th width="5%"></th>
				<th width="30%"><?php
				if ($this->lang == 'de')
				{
					echo JText::_("Verantwortliche");
				}
				else
				{
					echo JText::_("Responsible");
				}
				?>
				</th>
				<th width="10%"><?php echo JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_CREDITPOINTS"); ?></th>
			</tr>
		</thead>

		<?php ?>
		<tbody>

			<?php
			if (isset($this->groups[$i][1]))
			{
			?>
			<?php
			for ($h = 0; $h < count($this->groups[$i][1]); $h++)
			{
			?>
			<tr class="<?php echo "cat-list-row$k"; ?>">
				<td width="55%"><?php echo $this->groups[$i][1][$h]['title']; ?></td>
				<?php
				$scheduleInfo = $this->groups[$i][1][$h]['schedule'];
				if (isset($scheduleInfo))
				{
					?>
				<td width="5%"><?php echo $scheduleInfo; ?></td>

				<?php
				}
				else
				{
					?>
				<td width="5%"></td>
				<?php
				}
				?>
				<td width="30%"><?php echo $this->groups[$i][1][$h]['responsible']; ?>
				</td>
				<td width="10%"><?php echo $this->groups[$i][1][$h]['creditpoints']; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
			}
			}
				?>
		</tbody>

	</table>

	<?php
		}
	}
	?>
</div>
