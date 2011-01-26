<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
$eventlink = "index.php?option=com_thm_organizer&view=event&eventid=";
$modulelink = "index.php?option=com_thm_organizer&view=module&moduleid=";
?>
<div id="thm_organizer_rd">
    <div id="thm_organizer_rd_head">
        <?php echo $this->roomname; ?>&nbsp;am&nbsp;<?php echo $this->day; ?>, <?php echo $this->date; ?>&nbsp;&nbsp;<?php echo $this->backlink; ?>
    </div>
    <div id="thm_organizer_rd_lessons">
        <?php if(!isset($this->blocks)){ ?>
        <h3>An diesem Tag sind keine Veranstaltungen eingeplannt.</h3>
        <?php }else{ ?>
        <h3>planm&auml;&szlig;ige Veranstaltungen</h3>
        <table id="thm_organizer_rd_lessons_table" >
<?php
    foreach($this->blocks as $bk => $bv)
    {
        if($bk % 2 == 1) $rowclass = "thm_organizer_rd_row_even";
        else $rowclass = "thm_organizer_rd_row_odd";
        if(isset($bv['subject'])) $text = $bv['subject'];
        else $text = 'keine Veranstaltung';
        if(isset($bv['teachers']) && !isset($bv['eventid'])) $teachers = $bv['teachers'];
        else $teachers = '';
        $startatag = "";
        $endatag = "";
        if(isset($bv['eventid']))
        {
            $url = JRoute::_( $eventlink.$bv['eventid'] );
            $title = "Termin Details::Die Details dieses Termins ansehen.";
            $startatag = "<a class='thm_organizer_rd_link hasTip' title='$title' href='$url'>";
            $endatag = "</a>";
        }
        else if(isset($bv['moduleid']))
        {
            $url = JRoute::_( $modulelink.$bv['moduleid'] );
            $title = "Modul Beschreibung::Die Beschreibung dieses Modules ansehen.";
            $startatag = "<a class='thm_organizer_rd_link hasTip' title='$title' href='$url'>";
            $endatag = "</a>";
        }
?>

            <tr class="<?php echo $rowclass; ?>">
                <td class="thm_organizer_rd_lesson_time">
                    <?php echo $startatag.$bv['starttime']."&nbsp;-&nbsp;".$bv['endtime'].$endatag; ?>
                </td>
                <td class="thm_organizer_rd_lesson_name">
                    <?php echo $startatag.$text.$endatag; ?>
                </td>
                <td class="thm_organizer_rd_lesson_teachers">
                    <?php echo $startatag.$teachers.$endatag; ?>
                </td>
            </tr>
<?php
    }
?>
        </table>
<?php
}
?>
    </div>	
<?php
$areevents = false;
if(isset($this->reservingevents)):
    $areevents = true;
?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_reservingevents" >
	<h3>Reservierungen an diesem Tag</h3>
	<table class="thm_organizer_rd_eventtable" id="thm_organizer_rd_reservingevents_table" >
<?php
    $index = 0;
    foreach($this->reservingevents as $k => $v)
    {
        if($index % 2 == 1) $rowclass = "thm_organizer_rd_row_odd";
        else $rowclass = "thm_organizer_rd_row_even";
        $index++;
        $url = JRoute::_( $eventlink.$v['eid'] );
        $title = "Termin Details::Die Details dieses Termins ansehen.";
        $startatag = "<a class='thm_organizer_rd_link hasTip' title='$title'  href='$url'>";
        $endatag = "</a>";
?>
            <tr class='<?php echo $rowclass; ?>'>
                <td><?php echo $startatag.$v['title'].$endatag; ?></td>
                <td><?php echo $startatag.$v['times'].$endatag; ?></td>
                <td><?php echo $startatag.$v['author'].$endatag; ?></td>
            </tr>
<?php
    }
?>
        </table>
    </div>
<?php
endif;
if(isset($this->notes)):
    $areevents = true;
?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_notes" >
	<h3>Anmerkungen zu den Ressourcen dieser Raum</h3>
	<table  class="thm_organizer_rd_eventtable" id="thm_organizer_rd_notes_table" >
<?php
    $index = 0;
    foreach($this->notes as $k => $v)
    {
        if($index % 2 == 1) $rowclass = "thm_organizer_rd_row_odd";
        else $rowclass = "thm_organizer_rd_row_even";
        $index++;
        $url = JRoute::_( $eventlink.$v['eid'] );
        $title = "Termin Details::Die Details dieses Termins ansehen.";
        $startatag = "<a class='thm_organizer_rd_link hasTip' title='$title'  href='$url'>";
        $endatag = "</a>";
?>
            <tr class='<?php echo $rowclass; ?>'>
                <td><?php echo $startatag.$v['title'].$endatag; ?></td>
                <td><?php echo $startatag.$v['times'].$endatag; ?></td>
                <td><?php echo $startatag.$v['author'].$endatag; ?></td>
            </tr>
<?php
    }
?>
        </table>
    </div>
<?php
endif;
if(isset($this->futureevents)):
    $areevents = true;
?>
    <div class="thm_organizer_rd_events" id="thm_organizer_rd_futureevents" >
	<h3>k&uuml;nftige Ereignisse die diesen Raum betreffen</h3>
	<table  class="thm_organizer_rd_eventtable" id="thm_organizer_rd_futureevents_table" >
<?php
    $index = 0;
    foreach($this->futureevents as $k => $v)
    {
        if($index % 2 == 1) $rowclass = "thm_organizer_rd_row_odd";
        else $rowclass = "thm_organizer_rd_row_even";
        $index++;
        $url = JRoute::_( $eventlink.$v['eid'] );
        $title = "Termin Details::Die Details dieses Termins ansehen.";
        $startatag = "<a class='thm_organizer_rd_link hasTip' title='$title'  href='$url'>";
        $endatag = "</a>";
?>
            <tr class='<?php echo $rowclass; ?>'>
                <td><?php echo $startatag.$v['title'].$endatag; ?></td>
                <td><?php echo $startatag.$v['dates'].$endatag; ?></td>
                <td><?php echo $startatag.$v['author'].$endatag; ?></td>
            </tr>
<?php
    }
?>
        </table>
    </div>
<?php
endif;
if(!$areevents): ?>
	<br /><br /><h3>Es gibt zur Zeit keine Anmerkungen zu den Ressourcen dieses Raumes.</h3>
<?php endif;?>
</div>
