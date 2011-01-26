<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
//var_dump($this);
$style = "font-weight: bold; font-size:18px;";
?>
<div class="thm_organizer_roomdisplay">
    <div class="header">
        <div class="block_head_room"><?php  echo $this->roomname; ?></div>
        <div class="block_head_logo">
            <img src="components/com_thm_organizer/assets/images/logo.png" alt="Logo">
        </div>
        <div class="block_head_date">
                <?php echo $this->day; ?>, <?php echo $this->date; ?>
                <?php echo date('H:i'); ?>
        </div>
    </div>
    <div class="left_area">
<?php
if(isset($this->blocks))
{
    foreach($this->blocks as $bk => $bv)
    {
        if($bk % 2 == 1) $blockclass = "white_block";
        else  $blockclass = "green_block";
        if(isset($bv['subject'])) $dataclass = "right_block_data";
        else $dataclass = "right_block_nodata"
?>
        <div class="<?php echo $blockclass; ?>">
            <div class="left_block">
<?php if(date('H:i')>= $bv['starttime'] && date('H:i')<= $bv['endtime']): ?>
                    <img src="components/com_thm_organizer/assets/images/Raute.gif" alt="Raute">
<?php endif; ?>
            </div>
            <div class="middle_block">
                <center>
                    <?php echo substr($bv[starttime], 0, 5); ?><br />-<br /><?php echo substr($bv[endtime], 0, 5); ?>
                </center>
            </div>
            <div class="<?php  echo $dataclass; ?>">
                <span class="lesson">
<?php
        if(isset($bv['subject']))
        {
            if(isset($bv['times'])) $moreinfo = $bv['times'];
            else if(isset($bv['teachers'])) $moreinfo = $bv['teachers'];
?>
                    <?php  echo $bv['subject']; ?>
                    <br />
                    <span class='info'><?php  echo $moreinfo; ?></span>
<?php
        }
        else echo "keine Veranstaltung";
?>
                </span>
            </div>
        </div>
<?php
    }
}
else
{
?>
        <br /><br /><h2>An diesem Tag sind keine Veranstaltungen eingeplannt.</h2>
<?php
}
?>
	</div>
	<div class="right_area">
		<div class="notes">	
<?php
$areevents = false;
if(isset($this->reservingevents)):
    $areevents = true;
?>
	<h1>Vorsicht</h1>
        <hr style="height:3px;color:white"/>
        <hr class="hr2" style="height:3px;color:white"/>
	<ul>
<?php foreach($this->reservingevents as $k => $v): ?>
            <li>
                <h2><?php echo $v['title']; ?></h2>
                <br style='line-height: 0px;' />
		<?php echo $v['times'] ?>
                <br style='line-height: 0px;' />
		<?php echo $v['edescription']; ?>
            </li>
<?php endforeach; ?>
        </ul>
<?php
endif;
if(isset($this->notes)):
    $areevents = true;
?>
	<h1>spezielle Infos</h1>
	<hr style="height:3px;color:white"/>
	<hr class="hr2" style="height:3px;color:white"/>
	<ul>
<?php foreach($this->notes as $k => $v): ?>
            <li>
                <h2><?php $v['title'] ?></h2>
                <br style='line-height: 0px;' />
                <?php echo $v['times']; ?>
                <br  style='line-height: 0px;'/>
                <?php echo $v['edescription']; ?>
            </li>
<?php endforeach; ?>
        </ul>
<?php
endif;
if(isset($this->globalevents)):
    $areevents = true;
?>
	<h1>allgemeine Infos</h1>
	<hr style="height:3px;color:white"/>
	<hr class="hr2" style="height:3px;color:white"/>
	<ul>
<?php foreach($this->globalevents as $k => $v): ?>
            <li>
                <h2><?php echo$v['title']; ?></h2>
                <br style='line-height: 0px;' />
                <?php echo $v['times']; ?>
                <br style='line-height: 0px;' />
                <?php echo $v['edescription']; ?>
            </li>
<?php endforeach; ?>
        </ul>
<?php
endif;
if(isset($this->futureevents)):
    $areevents = true;
?>
	<h1>k&uuml;nftige Termine im Raum</h1>
	<hr style="height:3px;color:white"/>
	<hr class="hr2" style="height:3px;color:white"/>
	<ul>
<?php foreach($this->futureevents as $k => $v): ?>
            <li>
                <h2><?php echo$v['title']; ?></h2>
                <br style='line-height: 0px;' />
                <?php echo $v['dates']; ?>
                <br style='line-height: 0px;' />
                <?php echo $v['times']; ?>
                <br style='line-height: 0px;' />
                <?php echo $v['edescription']; ?>
            </li>
<?php endforeach; ?>
        </ul>
<?php
endif;
if(!$areevents): ?>
        <br /><br /><h2>Es gibt zur Zeit keine Hinweise f&uuml;r diesen Raum.</h2>
<?php endif; ?>
		</div>
	</div>
</div>
