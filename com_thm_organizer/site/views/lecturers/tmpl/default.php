<?php
/**
 * @version	    v2.0.0
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view lectures default
 * @description THM_Curriculum component site view
 * @author	    Markus Baier <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link		www.mni.thm.de
 */

JHTML::_('behavior.tooltip');
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

<span class="flag"> <a class='naviLink'
	href="<?php echo JRoute::_($this->langUrl); ?>"><img
		src="components/com_thm_organizer/css/images/<?php echo $this->langLink; ?>.png" />
</a>
</span>
<h1 class="componentheading">Modulhandbuch</h1>

<?php
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
?>

<div id='startTable'>
	<?php
	$k = 0;
	for ($i = 0; $i < count($this->data); $i++)
	{
		?>

	<table class="category">
		<?php echo $this->data[$i]['title'] . " "; ?>
		<big><?php echo $this->data[$i]['lecturer']; ?> </big>
		<thead>
			<tr>

				<th width="200px"><?php
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
				<th width="20px"></th>
				<th width="30px"><?php echo JText::_("Creditpoints"); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			for ($j = 0; $j < count($this->data[$i]); $j++)
			{
				?>
			<tr class="<?php echo "cat-list-row$k"; ?>">
				<?php
				if (isset($this->data[$i][$j]))
				{
					?>
				<td width="400px"><?php echo $this->data[$i][$j]['title']; ?></td>
				<?php
				if (isset($this->data[$i][$j]['schedule']))
				{
					?>
				<td width="20px"><?php echo $this->data[$i][$j]['schedule']; ?></td>
				<?php
				}
				else
				{ ?>
				<td></td>
				<?php
				}
				?>
				<td width="30px"><?php echo $this->data[$i][$j]['creditpoints']; ?>
				</td>
				<?php
				}
				?>
			</tr>
			<?php
			$k = 1 - $k;
			}
			?>
		</tbody>

	</table>

	<?php
	}
	?>

</div>
<input
	type="hidden" name="option" value="com_thm_organizer" />
<input type="hidden"
	name="task" value="" />
<input type="hidden" name="view"
	value="lecturers" />
<input type="hidden" name="layout"
	value="default" />
</form>
