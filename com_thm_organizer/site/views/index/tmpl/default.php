<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name		view index default
 * @description THM_Curriculum component site view
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
JHtml::_('behavior.tooltip');

$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$filter = $this->state->get('filter');
$limit = $this->state->get('limit');

if ($this->lang == 'de')
{
	$txtModulnummer = JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_COURSECODE_HIS");
	$txtModultitel = JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_TITLE_DE");
	$txtVerantwortliche = JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_RESPONSIBLE");
}
else
{
	$txtModulnummer = JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_COURSECODE_HIS");
	$txtModultitel = JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_TITLE_EN");
	$txtVerantwortliche = JText::_("COM_THM_ORGANIZER_SUBMENU_ASSETS_CURRICULUM_RESPONSIBLE");
}
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
	
    function selectionBox() {
        document.formModulcode.submit();
    }
</script>

<span class="flag"> <a class='naviLink'
	href="<?php echo JRoute::_($this->langUrl); ?>"><img
		src="components/com_thm_organizer/css/images/<?php echo $this->langLink; ?>.png" />
</a>
</span>
<h1 class="componentheading">
	<?php echo JText::_('COM_THM_ORGANIZER_MODULE_CATALOGUE'); ?>
</h1>

<?php
if ($suffix = $this->params->get('lsf_navi', 0))
{
	if ($this->lang == 'de')
	{
		$txtFachgruppen = "...nach Fachgruppen";
		$txtDozenten = "...nach Dozenten";
		$txtUEberblick = "...im &Uuml;berblick";
		$orderModultitel = "title_de";
	}
	else
	{
		$txtFachgruppen = "...by Groups";
		$txtDozenten = "...by Lecturer";
		$txtUEberblick = "...Overview";
		$orderModultitel = "title_en";
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

<?php $url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
<form id='lsf_searchform' class="lsf_searchform"
	enctype='multipart/form-data' action="<?php echo $url; ?>"
	method='post' name="formModulcode">

	<label for="filter"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?> </label> <input
		type="text" name="filter" id="filter" value="<?php echo $filter; ?>"
		class="inputbox" />

	<button onclick="selectionBox();">
		<?php echo JText::_('COM_THM_ORGANIZER_OK'); ?>
	</button>
	<button
		onclick="document.getElementById('filter').value = '';selectionBox();">
		<?php echo JText::_('COM_THM_ORGANIZER_RESET'); ?>
	</button>

	<span style="float: right;" class="display-limit"> <?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
		<?php echo $this->pagination->getLimitBox(); ?>
	</span>

</form>

<form
	action="<?php echo JRoute::_('index.php?option=com_thm_organizer&view=index'); ?>"
	method="post" name="adminForm" id="adminForm">
	<br /> <br />


	<div id='startTable<?php echo $suffix ?>'>
		<table class="category echo $suffix?>">
			<thead>
				<tr>
					<th><?php
					echo JHTML::_('grid.sort', $txtModulnummer, 'lsf_course_code, his_course_code, lsf_course_id', $listDirn, $listOrder);
					?>
					</th>
					<th></th>
					<th><?php echo JHTML::_('grid.sort', $txtModultitel, $orderModultitel, $listDirn, $listOrder); ?>
					</th>
					<th><?php echo JHTML::_('grid.sort', $txtVerantwortliche, 'verantwortliche', $listDirn, $listOrder); ?>
					</th>
					<th><?php echo JHTML::_('grid.sort', 'Creditpoints', 'min_creditpoints', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$k = 0;
				foreach ($this->items as $row)
				{
					?>
				<tr class="<?php echo "cat-list-row$k"; ?>">
					<td><?php echo $row->courseDetailLink; ?></td>

					<?php
					if (isset($row->scheduler))
					{
						?>
					<td width="5%"><?php echo $row->scheduler; ?></td>
					<?php
					}
					else
					{
						?>
					<td width="5%"</td>
					<?php
					}
					?>
					<td><?php
					if ($this->lang == 'de')
					{
						echo $row->title_de;
					}
					else
					{
						echo $row->title_en;
					}
					?>
					</td>
					<td><a href="<?php echo $row->responsible_link; ?>">
					<?php echo $row->responsible_name; ?>
					</a></td>
					<td><?php
					$creditpoints = explode('.', $row->min_creditpoints);
					echo $creditpoints[0] . " CrP";
					?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
				}
				?>
			</tbody>

		</table>
		<div class="pagination" align="center">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	</div>
	<input type="hidden" name="task" value="" /> <input type="hidden"
		name="filter_order" value="<?php echo $listOrder; ?>" /> <input
		type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<input type="hidden" name="filter" id="filter"
		value="<?php echo $filter; ?>" />
	<?php echo JHtml::_('form.token');
