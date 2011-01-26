<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
$event = $this->event;
//var_dump($event);

//creation of the sentence display of the dates & times
$dtstring = "Dieser Termin findet";
if(isset($event->starttime) && isset($event->endtime))
    $timestring = " zwischen ".$event->starttime." und ".$event->endtime;
else if(isset($event->starttime))
    $timestring = " ab ".$event->starttime;
else if(isset($event->endtime))
    $timestring = " bis ".$event->endtime;
if(isset($event->startdate) && isset($event->enddate))
{
    if($event->rec_type == 0)
    {
        if(isset($event->starttime) && isset($event->endtime))
            $dtstring .= " zwischen ".$event->starttime." am ".$event->startdate." und ".$event->endtime." am ".$event->enddate;
        else if(isset($event->starttime))
            $dtstring .= " vom ".$event->starttime." am ".$event->startdate." bis ".$event->enddate;
        else if(isset($event->endtime))
            $dtstring .= " vom ".$event->startdate." bis ".$event->endtime." am ".$event->enddate;
    }
    else
    {
        isset($timestring)? $dtstring .= " vom ".$event->startdate." bis dem ".$event->enddate.$timestring : $dtstring .= " vom ".$event->startdate." bis dem ".$event->enddate;
    }
}
else
{
    isset($timestring)? $dtstring .= " am ".$event->startdate.$timestring : $dtstring .= " am ".$event->startdate;
}
$dtstring .= " statt.";

$teachers = $classes = $rooms = $usergroups = false;
if(count($event->teachers) > 0)
{
    $teachers = true;
    $tstring = implode(', ', $event->teachers);
    if(count($event->teachers) > 1) $theadstring = "Dozenten:  ";
    else $theadstring = "Dozent:  ";
}
if(count($event->rooms) > 0)
{
    $rooms = true;
    $rstring = implode(', ', $event->rooms);
    if(count($event->rooms) > 1) $rheadstring = "R&auml;ume:  ";
    else $rheadstring = "Raum:  ";
}
if(count($event->classes) > 0)
{
    $classes = true;
    $cstring = implode(', ', $event->classes);
    if(count($event->classes) > 1) $cheadstring = "Semesterg&auml;nge:  ";
    else $cheadstring = "Semestergang:  ";
}
if(count($event->usergroups) > 0)
{
    $usergroups = true;
    $ugstring = implode(', ', $event->usergroups);
    if(count($event->usergroups) > 1) $ugheadstring = "Benutzergruppen:  ";
    else $ugheadstring = "Benutzergruppe:  ";
}
$contentorg = "";
if(isset($event->sectname) && isset($event->ccatname))
        $contentorg = $event->sectname." / ".$event->ccatname;
else if(isset($event->sectname))
        $contentorg = $event->sectname;
if(isset($event->publish_up) && isset($event->publish_down))
    $published = "Der Beitrag wird vom ".$event->publish_up." bis ".$event->publish_down." angezeigt";
?>
<div id="thm_organizer_e">
    <div id="thm_organizer_e_header">
        <span class="componentheading"><?php echo $event->title; ?></span>
        <div id="thm_organizer_e_headerlinks">
            <?php if(isset($this->editlink)) echo $this->editlink; ?>
            <?php if(isset($this->deletelink)) echo $this->deletelink; ?>
        </div>
    </div><!-- end header -->
    <div id="thm_organizer_e_hr">
        <hr/>
    </div>
    <div class="thm_organizer_e_block_div" >
<?php if(isset($event->description) && trim($event->description) != ''): ?>
        <div id='thm_organizer_e_description'>
            <p><?php echo trim($event->description); ?></p>
        </div><!-- end description -->
<?php endif; ?>
        <div id="thm_organizer_e_time">
            <p><?php echo $dtstring; ?></p>
        </div><!-- end date / time -->
<?php
if($teachers || $rooms || $classes || $usergroups)
{
?>
        <div id="thm_organizer_e_resources" >
            <h3><?php echo JText::_( 'der Termin betrifft:' ); ?></h3>
<?php if($teachers): ?>
            <p>
                <?php echo JText::_($theadstring); ?>
                <?php echo $tstring; ?>
            </p>
<?php endif; ?>
<?php if($rooms): ?>
            <p>
                <?php echo JText::_($rheadstring); ?>
                <?php echo $rstring; ?>
            </p>
<?php endif; ?>
<?php if($classes): ?>
            <p>
                <?php echo JText::_($cheadstring); ?>
                <?php echo $cstring; ?>
            </p>
<?php endif; ?>
<?php if($usergroups): ?>
            <p>
                <?php echo JText::_($ugheadstring); ?>
                <?php echo $ugstring; ?>
            </p>
<?php endif; ?>
        </div><!-- end resource div -->
<?php }?>
    </div><!-- end event specific div -->
    <div class="thm_organizer_e_block_div">
        <div id="thm_organizer_e_category" >
            <h3><?php echo JText::_( 'Kategorie' ).":&nbsp;".$event->ecname; ?></h3>
<?php if(isset($event->ecdescription)): ?>
            <br /><?php echo $event->ecdescription; ?><br />
<?php endif; ?>
            <p><?php echo $event->displaybehaviour; ?></p>
        </div><!-- end event category div -->
<?php if(isset($event->sectname)): ?>
        <div id="thm_organizer_e_content" >
            <h3><?php echo JText::_( 'Beitrag' ); ?></h3>
            <p>
                <?php echo JText::_( 'Dieser Termin gibt es auch als Beitrag unter ' ).$contentorg."."; ?>
            </p>
            <p>
                <?php echo $published; ?>
            </p>
        </div><!-- end content div -->
<?php endif; ?>
    </div><!-- end extra details div -->
</div><!-- end event view -->
	