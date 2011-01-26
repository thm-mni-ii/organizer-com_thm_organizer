<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
//var_dump($this);
$style = "font-weight: bold; font-size:18px;";
$lessons = $this->lessons;
$blocks = $this->blocks;
?>
<div class="thm_organizer_roomdisplay">
	<div class="header">
		<div class="block_head_room"><? echo $this->roomname; ?></div>
		<div class="block_head_logo">
			<?php
			if($this->registered) echo '<img src="components/com_thm_organizer/assets/images/logo.png" alt="Logo">';
			?>
		</div>
		<div class="block_head_date">
			<? echo $this->weekday; ?>, <? echo date('d.m.y'); ?>
			<? echo date('H:i'); ?>&nbsp;&nbsp;<? if(!$this->registered) echo $this->backlink; ?>
		</div>
	</div>
	<div class="left_area">
<?php foreach($blocks as $bk => $bv) {
if($bk % 2 == 1) { ?>
		<div class="white_block">
<?php } else { ?>
		<div class="green_block">
<?php } ?>
			<div class="left_block">
<?php
if(date('H:i')>= $bv['starttime'] && date('H:i')<= $bv['endtime'])
{
	echo '<img src="'.$server.'components/com_thm_organizer/assets/images/Raute.gif" alt="Raute">';
}?>
			</div>
			<div class="middle_block"><? echo "<center>".substr($bv[starttime], 0, 5)."<br />-<br />".substr($bv[endtime], 0, 5)."</center>"; ?></div>
			<div class="<?php if($this->lessons[$bk]) echo "right_block_data"; else echo "right_block_nodata" ?>">
				<span class="lesson">
					<?
					if($lessons[$bk]['events'] 
						&& count($lessons[$bk]['events']) == 1
						&& (($lessons['events'][$lessons[$bk]['events'][0]]['starttime'] >= $bv['starttime']
							&& $lessons['events'][$lessons[$bk]['events'][0]]['starttime'] <= $bv['endtime'])
							||
							($lessons['events'][$lessons[$bk]['events'][0]]['starttime'] <= $bv['starttime']
							&& $lessons['events'][$lessons[$bk]['events'][0]]['endtime'] >= $bv['endtime'])
							||
							($lessons['events'][$lessons[$bk]['events'][0]]['endtime'] >= $bv['starttime']
							&& $lessons['events'][$lessons[$bk]['events'][0]]['endtime'] <= $bv['endtime'])
							|| $lessons['events'][$lessons[$bk]['events'][0]]['time'] == "ganzt&auml;gige Ereignis"))
					{	
									
						if($this->registered)
						{
							echo $lessons['events'][$lessons[$bk]['events'][0]]['title']."<br />";
							echo "<span class='info'>".$lessons['events'][$lessons[$bk]['events'][0]]['time']."</span>";
						}
						else
						{
							$event = $lessons['events'][$lessons[$bk]['events'][0]];
							if($event['starttime'] != "00:00:00") $starttime = substr($event['starttime'], 0, 5);
							if($event['endtime'] != "00:00:00") $endtime = substr($event['endtime'], 0, 5);
							
							$eventlink = JRoute::_("index.php?option=com_thm_organizer&view=event&eventid=".$lessons[$bk]['events'][0]);
						?>
						<span class="hasTip" title="Modul Beschreibung::Die beschreibung dieses Modules ansehen.">
							<?php
							echo "<a style='$style' href='$eventlink'>".$lessons['events'][$lessons[$bk]['events'][0]]['title']."</a>";
							?>
						</span>	
						<br />
						<span class="info">
							<? echo $lessons['events'][$lessons[$bk]['events'][0]]['time'];?>
						</span>
						<?php 
						}
					}
					else if($this->lessons[$bk])
					{
						//echo $this->lessons[$this->lessons[$bk]['subject']];
						if($this->registered || !$lessons[$bk]['module'])
						{
							echo $this->lessons[$bk]['subject']."<br />";
							echo "<span class='info'>".implode(", ",$lessons[$bk]['teachers'])."</span>";
						}
						else
						{
							$modulelink = JRoute::_("index.php?option=com_thm_organizer&view=module&oalias=".$this->lessons[$bk]['module']);
						?>
						<span class="hasTip" title="Modul Beschreibung::Die beschreibung dieses Modules ansehen.">
							<?php
							echo "<a style='$style' href='$modulelink'>".$this->lessons[$bk]['subject']."</a>";
							?>
						</span>
						<br />
						<span class="info">
							<? echo implode(", ",$lessons[$bk]['teachers']);?>
						</span>
						<?php 
						}
					}
					else echo "keine Veranstaltung";
					?>
				</span>
			</div>
		</div>
<?php
}
?>
	</div>
	<div class="right_area">
		<div class="notes">	
<?php
if($lessons['reserving'])
{
	echo "<h1>Vorsicht</h1>";
	if($this->registered)
	{ ?>
		<hr style="height:3px;color:white"/>
		<hr class="hr2" style="height:3px;color:white"/>
	<?php }	
	else
	{ ?>
		<hr style="height:1px;"/>
		<hr style="height:1px;"/>
	<?php }	
	echo "<ul>";
	foreach($lessons['events'] as $k => $v)
	{
		if($v['reservingp'])
		{
			$link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventid=".$k);
			if(!$this->registered)echo "<li><h2><a style='$style' href='$link'>".$v['title']."</a></h2>";
			else echo "<li><h2>".$v['title']."</h2><br style='line-height: 0px;' />";
			echo $v['time']."<br style='line-height: 0px;' />";
			echo $v['edescription']."</li>";
		}
	}
	echo "</ul>";
} 
if($lessons['note'])
{
	echo "<h1>spezielle Infos</h1>";
	if($this->registered)
	{ ?>
		<hr style="height:3px;color:white"/>
		<hr class="hr2" style="height:3px;color:white"/>
	<?php }	
	else
	{ ?>
		<hr style="height:1px;"/>
		<hr style="height:1px;"/>
	<?php }	
	echo "<ul>";
	foreach($lessons['events'] as $k => $v)
	{
		if(!$v['reservingp'] && !$v['globalp'])
		{
			$link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventid=".$k);
			if(!$this->registered)echo "<li><h2><a style='$style' href='$link'>".$v['title']."</a></h2>";
			else echo "<li><h2>".$v['title']."</h2><br style='line-height: 0px;' />";
			echo $v['time']."<br  style='line-height: 0px;'/>";
			echo $v['edescription']."</li>";
		}
	}
	echo "</ul>";
}
if($lessons['globalp'])
{
	echo "<h1>allgemeine Infos</h1>";
	if($this->registered)
	{ ?>
		<hr style="height:3px;color:white"/>
		<hr class="hr2" style="height:3px;color:white"/>
	<?php }	
	else
	{ ?>
		<hr style="height:1px;"/>
		<hr style="height:1px;"/>
	<?php }	
	echo "<ul>";
	foreach($lessons['events'] as $k => $v)
	{
		if($v['globalp'])
		{
			$link = JRoute::_("index.php?option=com_thm_organizer&view=event&eventid=".$k);
			if(!$this->registered)echo "<li><h2><a style='$style' href='$link'>".$v['title']."</a></h2>";
			else echo "<li><h2>".$v['title']."</h2><br style='line-height: 0px;' />";
			echo $v['time']."<br style='line-height: 0px;' />";
			echo $v['edescription']."</li>";
		}
	}
	echo "</ul>"; 
}
if(!$lessons['note'] && !$lessons['reservingp'] && !$lessons['globalp'])
	echo "Es gibt zur Zeit keine Hinweise fuer diesen Raum.<br /><br />";
?>
		</div>
	</div>
</div>
