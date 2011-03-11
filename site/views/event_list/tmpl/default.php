<?php defined('_JEXEC') or die('Restricted access');
global $mainframe;
$params =& $mainframe->getParams('com_thm_organizer');
$display_type = $params->get('display_type');
$title_width = $params->get('title_width');
$show_author = $params->get('show_author');
if(isset($show_author) && $show_author == 1)
    $author_width = $params->get('author_width');
$show_room = $params->get('show_room');
if(isset($show_room) && $show_room == 1)
    $room_width = $params->get('room_width');
$show_category = $params->get('show_category');
if(isset($show_category) && $show_category == 1)
    $category_width = $params->get('category_width');
$date_width = $params->get('title_width');
?>
<script type="text/javascript">
    function reSort( col, dir )
    {
        document.getElementById('orderby').value=col;
        document.getElementById('orderbydir').value=dir;
        document.getElementById('thm_organizer_el_form').submit();
    }
</script>
<div id="thm_organizer_el">
<?php
if(count($this->category) == 1)
{
    if($this->category[0] != -1)
    { ?>
    <table id="thm_organizer_el_eventcattable">
<?php foreach($this->categories as $cat)
      {
            if($cat->ecid == $this->category[0])
            { ?>
        <tr>
<?php           if($cat->ecimage)
                {?>
            <td>
                <img class="thm_organizer_el_catimage"
                     alt="Category Image"
                     src="images/thm_organizer/categories/<?php echo $cat->ecimage; ?>"/>
            </td>
<?php           }?>
            <td>
                <h2><?php echo $cat->ecname; ?></h2><br />
<?php           if($cat->ecdescription) echo $cat->ecdescription; ?>
            </td>
        </tr>
<?php
            }
      }?>
    </table>
<?php
    }
}
if(count($this->category) > 1)
{
    $index = 0;
?>
    <h2>
<?php
    foreach($this->categories as $cat)
    {
        foreach($this->category as $dcat)
        {
            if($cat->ecid == $dcat):
                if($index != 0) echo ", ";
                echo $cat->ecname;
                    $index++;
            endif;
        }
    } ?>
    </h2><br />
 <?php
}
if($this->usergid >= 19)
{
?>
<div id="thm_organizer_el_newevent">
    <a  class="newEventLink hasTip"
        title="<?php echo $this->lists['newtitle']."::".$this->lists['newtext'];?>"
        href="<?php echo $this->newlink; ?>">
            <?php echo $this->lists['newimage']; ?>
    </a>
</div>
<?php
}
if(!isset($this->filter)) $this->filter = "";?>
<form id='thm_organizer_el_form'
      enctype='multipart/form-data'
      action='<?php echo JRoute::_("index.php?option=com_thm_organizer&view=eventlist&Itemid=$this->itemid"); ?>'
      method='post'>
    <input type="hidden" id="orderby" name="orderby" value="<?php echo $this->orderby; ?>" />
    <input type="hidden" id="orderbydir"name="orderbydir" value="<?php echo $this->orderbydir; ?>" />
    <div id='thm_organizer_el_searchform'>
<?php if(isset($display_type) && !($display_type == 1 || $display_type == 5)): ?>
        <label for="category"><?php echo JText::_('Kategorie'); ?></label>
<?php echo $this->lists['category'];
endif;?>
        <label for="filter"><?php echo JText::_('Suchen nach'); ?></label>
        <input type="text"
               name="filter"
               id="filter"
               value="<?php echo $this->filter; ?>"
               class="inputbox"
               onchange="document.getElementById('thm_organizer_el_form').submit();" />
        <label for="date"><?php echo JText::_('Datum'); ?></label>
        <?php echo $this->lists['date']; ?>
        <label for="limit"><?php echo JText::_('Anzahl'); ?></label>
        <?php echo $this->pageNav->getLimitBox(); ?>
        <button onclick="document.getElementById('thm_organizer_el_form').submit();">
            <?php echo JText::_( 'Los' ); ?>
        </button>
        <button onclick="document.getElementById('filter').value='';
                         document.getElementById('date').value=''
                         document.getElementById('thm_organizer_el_form').submit();">
            <?php echo JText::_( 'Reset' ); ?>
        </button>
    </div>
<?php
if(!$this->events)
        echo "<br /><h4>".JText::_("Keine Events erf&uuml;llen die Suchkriterien").".</h4></form>";
else
{ ?>
    <table id="thm_organizer_el_eventtable">
        <colgroup>
<?php if(isset($title_width)): ?>
            <col width="<?php echo $title_width; ?>%" id="thm_organizer_el_col_title" />
<?php else: ?>
            <col id="thm_organizer_el_col_title" />
<?php endif;
if(isset($show_author) && $show_author == "1")
{
    if(isset($author_width)): ?>
            <col width="<?php echo $author_width; ?>%" id="thm_organizer_el_col_author" />
<?php else: ?>
            <col id="thm_organizer_el_col_author" />
<?php endif;
}
if(isset($show_room) && $show_room == "1")
{
    if(isset($room_width)): ?>
            <col width="<?php echo $room_width; ?>%" id="thm_organizer_el_col_room" />
<?php else: ?>
            <col id="thm_organizer_el_col_room" />
<?php endif;
}
if(isset($show_category) && $show_category == "1")
{
    if(isset($category_width)): ?>
            <col width="<?php echo $category_width; ?>%" id="thm_organizer_el_col_category" />
<?php else: ?>
            <col class="thm_organizer_el_col_category" />
<?php endif;
}
if(isset($date_width)):?>
            <col width="<?php echo $date_width; ?>%" id="thm_organizer_el_col_date" />
<?php else: ?>
            <col class="thm_organizer_el_col_date" />
<?php endif; ?>
            <col class="thm_organizer_el_col_edit" />
            <col class="thm_organizer_el_col_delete" />
	</colgroup>
        <thead>
            <tr>
                <td id="thm_organizer_el_eventtitlehead"><?php echo $this->lists['titlehead']; ?></td>
                <?php if(isset($show_author) && $show_author == "1"): ?>
                <td id="thm_organizer_el_eventauthorhead"><?php echo $this->lists['authorhead']; ?></td>
                <?php endif;
                if(isset($show_room) && $show_room == "1"): ?>
                <td id="thm_organizer_el_eventroomhead"><?php echo $this->lists['roomhead']; ?></td>
                <?php endif;
                if(isset($show_category) && $show_category == "1"): ?>
                <td id="thm_organizer_el_eventcathead"><?php echo $this->lists['categoryhead']; ?></td>
                <?php endif; ?>
                <td id="thm_organizer_el_eventdthead"><?php echo $this->lists['datehead']; ?></td>
                <td id="thm_organizer_el_eventedithead" />
                <td id="thm_organizer_el_eventdeletehead" />
            </tr>
        </thead>
<?php
$rowcount = 0;
foreach($this->events as $event)
{
    if($rowcount % 2 === 0) $rowclass = "thm_organizer_el_eventeven";
    else $rowclass = "thm_organizer_el_eventodd"?>
        <tr class="<?php echo $rowclass; ?>">
            <td>
                <span class="thm_organizer_el_eventtitle hasTip"
                      title="Detail Ansicht::Diese Notiz unter die Lupe nehmen.">
                    <a href="<?php echo $event->detlink.$this->itemid; ?>">
                        <?php echo $event->title; ?>
                    </a>
                </span>
            </td>
<?php
if(isset($show_author) && $show_author == "1")
{
if(isset($event->author)): ?>
            <td>
                <span class="thm_organizer_el_eventauthor hasTip"
                      title="Author::Events, die von diesem Author erstellt wurden.">
                    <a href="<?php echo $event->filterlink.$this->itemid.'&filter='.$event->author; ?>">
                        <?php echo $event->author; ?>
                    </a>
                </span>
            </td>
<?php else: ?>
            <td />
<?php endif;
}
if(isset($show_room) && $show_room == "1")
{
if(isset($event->rooms)): ?>
            <td>
                <span class="thm_organizer_el_eventroom hasTip"
                      title="Raum Ansicht::Events, die in diesem Raum stattfinden betrachten.">
                    <a href="<?php echo $event->filterlink.$this->itemid.'&filter='.$event->rooms; ?>">
                        <?php echo $event->rooms; ?>
                    </a>
                </span>
            </td>
<?php else: ?>
            <td />
<?php endif;
}
if(isset($show_category) && $show_category == "1"):?>
            <td>
                <span class="thm_organizer_el_eventcat hasTip"
                      title="Kategorie Ansicht::Events dieser Kategorie betrachten.">
                    <a href="<?php echo $event->catlink.$this->itemid; ?>">
                        <?php echo $event->ecname; ?>
                    </a>
                </span>
            </td>
<?php endif; ?>
            <td>
                <span class="thm_organizer_el_eventdt">
                        <?php echo $event->displaydt; ?>
                </span>
            </td>
<?php if($event->access <= $this->usergid && ($event->username == $this->username || $this->usergid >= 24)): ?>
            <td>
                <a class="deleteEventLink hasTip"
                   title="<?php echo $this->lists['edittitle']."::".$this->lists['edittext'];?>"
                   href="<?php echo $event->editlink.$this->itemid; ?>">
                    <?php echo $this->lists['editimage']; ?>
                </a>
            </td>
            <td>
                <a  class="deleteEventLink hasTip"
                    title="<?php echo $this->lists['deletetitle']."::".$this->lists['deletetext'];?>"
                    href="<?php echo $event->dellink.$this->itemid; ?>">
                    <?php echo $this->lists['deleteimage']; ?>
                </a>
            </td>
<?php else: ?>
            <td />
            <td />
<?php endif; ?>
        </tr>
<?php
$rowcount++;
} ?>
    </table>
</form>
<?php
} ?>
<div class="pageslinks">
<?php echo $this->pageNav->getPagesLinks(); ?>
</div>
</div>
