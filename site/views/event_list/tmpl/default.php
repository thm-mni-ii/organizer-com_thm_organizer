<?php
defined('_JEXEC') or die('Restricted access');
$rowcount = 0;
?>
<script type="text/javascript">
    function reSort( col, dir )
    {
        document.getElementById('thm_organizer_el_orderby').value=col;
        document.getElementById('thm_organizer_el_orderbydir').value=dir;
        document.getElementById('thm_organizer_el_form').submit();
    }
</script>
<div id="thm_organizer_el">
    <div id="thm_organizer_el_top_div" >
        <?php if($this->category != -1) { ?>
        <div id="thm_organizer_el_category_desc_div">
            <img class="thm_organizer_el_catimage" alt="Category Image"
                 src="images/thm_organizer/categories/<?php echo $cat->ecimage; ?>"/>
                    <h2><?php echo $cat->ecname; ?></h2><br />
            <?php if(isset($this->categories[$this->category]['description'])): ?>
                <?php echo $this->categories[$this->category]['description']; ?>
            <?php endif; ?>
        </div>
        <?php } ?>
        <div id="thm_organizer_el_action_div">
            <?php if($this->canWrite): ?>
            <a  class="hasTip thm_organizer_el_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EL_NEW_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_NEW_DESCRIPTION');?>"
                href="<?php echo JRoute::_( "index.php?option=com_thm_organizer&view=event_edit&Itemid=$this->itemID" ); ?>">
                <span id="thm_organizer_el_new_span" class="thm_organizer_el_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EL_NEW'); ?>
            </a>
            <?php endif; if($this->canEdit): ?>
            <a  class="hasTip thm_organizer_el_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EL_EDIT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_EDIT_DESCRIPTION');?>"
                href="">
                <span id="thm_organizer_el_edit_span" class="thm_organizer_el_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EL_EDIT'); ?>
            </a>
            <a  class="hasTip thm_organizer_el_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EL_DELETE_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_DELETE_DESCRIPTION');?>"
                href="<?php echo JRoute::_( "index.php?option=com_thm_organizer&view=event_edit&Itemid=$this->itemID" ); ?>">
                <span id="thm_organizer_el_delete_span" class="thm_organizer_el_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EL_DELETE'); ?>
            </a>
            <?php endif; if($this->canWrite or $this->canEdit): ?>
            <span class="thm_organizer_el_divider_span"></span>
            <?php endif; ?>
            <a  class="hasTip thm_organizer_el_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EL_SUBMIT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_SUBMIT_DESCRIPTION');?>"
                href="<?php echo JRoute::_( "index.php?option=com_thm_organizer&view=event_edit&Itemid=$this->itemID" ); ?>">
                <span id="thm_organizer_el_submit_span" class="thm_organizer_el_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EL_SUBMIT'); ?>
            </a>
            <a  class="hasTip thm_organizer_el_action_link"
                title="<?php echo JText::_('COM_THM_ORGANIZER_EL_RESET_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_RESET_DESCRIPTION');?>"
                href="<?php echo JRoute::_( "index.php?option=com_thm_organizer&view=event_edit&Itemid=$this->itemID" ); ?>">
                <span id="thm_organizer_el_reset_span" class="thm_organizer_el_action_span"></span>
                <?php echo JText::_('COM_THM_ORGANIZER_EL_RESET'); ?>
            </a>
        </div>
    </div>
    <div id="thm_organizer_el_form_div" >
        <form id='thm_organizer_el_form' enctype='multipart/form-data' method='post'
              action='<?php echo JRoute::_("index.php?option=com_thm_organizer&view=event_list&Itemid=$this->itemID"); ?>' >
            <input type="hidden" id="thm_organizer_el_orderby" name="orderby" value="<?php echo $this->orderby; ?>" />
            <input type="hidden" id="thm_organizer_el_orderbydir"name="orderbydir" value="<?php echo $this->orderbydir; ?>" />
            <div id='thm_organizer_el_search_div'>
                <span class="thm_organizer_el_label_span" >
                    <label for="search"><?php echo JText::_('COM_THM_ORGANIZER_EL_SEARCH'); ?></label>
                </span>
                <input type="text" name="search" id="thm_organizer_el_search_text"
                       value="<?php echo $this->search; ?>" class="inputbox"
                       onchange="document.getElementById('thm_organizer_el_form').submit();" />
                <?php if($this->display_type != 1 and $this->display_type != 5): ?>
                <span class="thm_organizer_el_label_span" >
                    <label for="category"><?php echo JText::_('COM_THM_ORGANIZER_EL_CATEGORY'); ?></label>
                    <?php echo $this->categorySelect; ?>
                </span>
                <?php endif; ?>
                <span class="thm_organizer_el_label_span" >
                    <label for="fromdate"><?php echo JText::_('COM_THM_ORGANIZER_EL_FROMDATE'); ?></label>
                </span>
                <?php echo $this->fromdate; ?>
                <span class="thm_organizer_el_label_span" >
                    <label for="todate"><?php echo JText::_('COM_THM_ORGANIZER_EL_TODATE'); ?></label>
                </span>
                <?php echo $this->todate; ?>
                <span class="thm_organizer_el_label_span" >
                    <label for="limit"
                           title="<?php echo JText::_('COM_THM_ORGANIZER_EL_COUNT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_COUNT_DESCRIPTION');?>">
                           <?php echo JText::_('COM_THM_ORGANIZER_EL_COUNT'); ?>
                    </label>
                </span>
                <?php echo $this->pageNav->getLimitBox(); ?>
                <span id="thm_organizer_el_count2">
                    <?php echo JText::_('COM_THM_ORGANIZER_EL_COUNT_COUNT2'); ?>
                </span>
            </div>
    <?php if(count($this->events) > 0){ ?>
            <div id="thm_organizer_el_events_div" >
                <table id="thm_organizer_el_eventtable">
                    <colgroup>
                        <col id="thm_organizer_el_col_check" />
                        <col id="thm_organizer_el_col_title" />
                        <?php if($this->display_type != 3 and $this->display_type != 7): ?>
                        <col id="thm_organizer_el_col_author" />
                        <?php endif; if($this->display_type != 2 and $this->display_type != 6): ?>
                        <col width="<?php echo $room_width; ?>%" id="thm_organizer_el_col_room" />
                        <?php endif; if($this->display_type != 1 and $this->display_type != 5): ?>
                        <col id="thm_organizer_el_col_category" />
                        <?php endif; ?>
                        <col id="thm_organizer_el_col_date" />
                        <col id="thm_organizer_el_col_edit" />
                        <col id="thm_organizer_el_col_delete" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th align="left">
                                <input type="checkbox" name="checkall-toggle" value="" onclick="checkAll(this)" />
                            </th>
                            <th id="thm_organizer_el_eventtitlehead"><?php echo $this->titleHead; ?></th>
                            <?php if($this->display_type != 3 and $this->display_type != 7): ?>
                            <th id="thm_organizer_el_eventauthorhead"><?php echo $this->authorHead; ?></th>
                            <?php endif; if($this->display_type != 2 and $this->display_type != 6): ?>
                            <th id="thm_organizer_el_eventroomhead"><?php echo $this->resourceHead; ?></th>
                            <?php endif; if($this->display_type != 1 and $this->display_type != 5): ?>
                            <th id="thm_organizer_el_eventcathead"><?php echo $this->categoryHead; ?></th>
                            <?php endif; ?>
                            <th id="thm_organizer_el_eventdthead"><?php echo $this->dateHead; ?></th>
                        </tr>
                    </thead>
                    <?php foreach($this->events as $event){
                        $rowclass = ($rowcount % 2 === 0)? "thm_organizer_el_row_even" : "thm_organizer_el_row_odd";
                        $checked = JHTML::_( 'grid.id', $event['id'], $event['id'] );?>
                    <tr class="<?php echo $rowclass; ?>">
                        <?php if($event['userCanEdit']): ?>
                        <td class="thm_organizer_ce_checkbox"><?php echo $checked; ?></td>
                        <?php else: ?>
                        <td />
                        <?php endif; ?>
                        <td>
                            <span class="thm_organizer_el_eventtitle hasTip"
                                  title="<?php echo JText::_('COM_THM_ORGANIZER_EL_EVENT_TITLE')."::".JText::_('COM_THM_ORGANIZER_EL_EVENT_DESCRIPTION');?>">
                                <a href="<?php echo $event['link'].$this->itemid; ?>">
                                    <?php echo $event['title']; ?>
                                </a>
                            </span>
                        </td>
                        <?php if($this->display_type != 3 and $this->display_type != 7): ?>
                        <td>
                            <span class="thm_organizer_el_eventauthor hasTip"
                                  title="Author::Events, die von diesem Author erstellt wurden.">
                                <a href="<?php echo $event['authorlink'].$this->itemid; ?>">
                                    <?php echo $event['author']; ?>
                                </a>
                            </span>
                        </td>
                        <?php endif; if($this->display_type != 2 and $this->display_type != 6): ?>
                        <td>
                            <span class="thm_organizer_el_eventroom hasTip"
                                  title="Termin Ressourcen::Ressourcen, die von diesem Termin betroffen sind.">
                                    <?php echo $event['resources']; ?>
                            </span>
                        </td>
                        <?php endif; if($this->display_type != 1 and $this->display_type != 5): ?>
                        <td>
                            <span class="thm_organizer_el_eventcat hasTip"
                                  title="Kategorie Ansicht::Events dieser Kategorie betrachten.">
                                <a href="<?php echo $event->catlink.$this->itemid; ?>">
                                    <?php echo $event['eventCategory']; ?>
                                </a>
                            </span>
                        </td>
                        <?php endif; ?>
                        <td>
                            <span class="thm_organizer_el_eventdt">
                                    <?php echo $event['displayDates']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php $rowcount++; } ?>
                </table>
            </div>
            <?php }else{ ?>
            <br />
            <h4><?php echo JText::_("Keine Events erf&uuml;llen die Suchkriterien"); ?></h4>
            <?php } ?>
        </form>
    </div>
    <div class="pageslinks"><?php echo $this->pageNav->getPagesLinks(); ?></div>
</div>
