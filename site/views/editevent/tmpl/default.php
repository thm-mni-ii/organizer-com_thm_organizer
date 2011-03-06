<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
$event = $this->event;
if(isset($_POST['title'])) $title = $_POST['title'];
else if(isset($event['title'])) $title = $event['title'];
else $title = "";
isset($event['eventid'])? $eventid = $event['eventid'] : $eventid = '0';
isset($event['contentid'])? $contentid = $event['contentid'] : $contentid = '0';
if(isset($event['recurrence_type']) && $event['recurrence_type'] == 0)
{
    $blockchecked = 'checked';
    $dailychecked = '';
}
else
{
    $blockchecked = '';
    $dailychecked = 'checked';
}
isset($event['startdate'])? $sd = $event['startdate'] : $sd = '';
isset($event['publish_up'])? $pu = $event['publish_up']: $pu = date('Y-m-d');
isset($event['publish_down'])? $pd = $event['publish_down']: $pd = '';
?>
<script language="javascript" type="text/javascript">

    Window.onDomReady(function()
    {
        document.formvalidator.setHandler
        (
            'date',
            function (value)
            {
                value = trim(value);
                if(value=="")
                {
                    return false;
                }
                else
                {
                    timer = new Date();
                    time = timer.getTime();
                    regexp = new Array();
                    regexp[time] = new RegExp('^[0-9]{4}-[0-1][0-9]-[0-3][0-9]$','gi');
                    return regexp[time].test(value);
                }
            }
        );
        document.formvalidator.setHandler
        (
            'time',
            function (value)
            {
                if(value=="")
                {
                    return true;
                }
                else
                {
                    //check length
                    timer = new Date();
                    time = timer.getTime();
                    regexp = new Array();
                    regexp[time] = new RegExp('^[0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$','gi');
                    return regexp[time].test(value);
                }
            }
        );
    });

    function changeDisplayedInformation()
    {
    }


    function submitbutton( pressbutton )
    {
        if (pressbutton == 'cancelevent') {
            elsubmitform( pressbutton );
            return;
        }
        var form = document.getElementById('eventForm');
        var validator = document.formvalidator;
        var title = $(form.title).getValue();
        var description = $(form.description).getValue();
        var strikes = 0;

        if ( title.length==0 )
        {
            alert("<?php echo JText::_( 'Kein Title', true ); ?>");
            validator.handleResponse(false,form.title);
            form.title.focus();
            return false;
        }
        else if ( validator.validate(form.publish_up) === false )
        {
            alert("<?php echo JText::_( 'Ungueltige Startdatum', true ); ?>");
            form.publish_up.focus();
            return false;
        }
        else if ( validator.validate(form.publish_down) === false )
        {
            alert("<?php echo JText::_( 'Ungueltige Enddatum', true ); ?>");
            form.publish_down.focus();
            return false;
        }
        else if ( validator.validate(form.startdate) === false )
        {
            alert("<?php echo JText::_( 'Ungueltige Startdatum', true ); ?>");
            form.startdate.focus();
            return false;
        }
        else if ( validator.validate(form.enddate) === false )
        {
            alert("<?php echo JText::_( 'Ungueltige Enddatum', true ); ?>");
            form.enddate.focus();
            return false;
        }
        else if ( validator.validate(form.starttime) === false )
        {
            alert("<?php echo JText::_( 'Ungueltige Startzeit', true ); ?>");
            form.starttime.focus();
            return false;
        }
        else if ( validator.validate(form.endtime) === false )
        {
            alert("<?php echo JText::_( 'Ungueltige Endzeit', true ); ?>");
            form.endtime.focus();
            return false;
        }
    }

    /*function updatePD(){
        var enddate = new Date( document.getElementById('enddate').value );
        alert ( enddate.toString());
        document.getElementById('publish_down').value = enddate;
    }*/

    //joomla submitform needs form name
    function elsubmitform(pressbutton){

        var form = document.getElementById('eventForm');
        if (pressbutton)
        {
            form.task.value=pressbutton;
        }
        if (typeof form.onsubmit == "function")
        {
            form.onsubmit();
        }
        form.submit();
    }

</script>
<div id="thm_organizer_ee">
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer&controller=editevent&Itemid='.$this->itemid) ?>"
          method="post" name="eventForm" id="eventForm" class="eventForm">
        <div id="thm_organizer_ee_category_div">
            <div id="thm_organizer_ee_category_select_div">
                <div class="thm_organizer_ee_label_div" >
                    <label class="thm_organizer_ee_label" for="categoryID">
                        <?php echo JText::_('Kategorie:'); ?>
                    </label>
                </div>
                <div class="thm_organizer_ee_data_div" >
                        <?php echo $this->categoryselect; ?>
                </div>
            </div>
            <div id="thm_organizer_ee_event_cat_desc_div" >
                <p><?php echo $this->categories[$this->event['categoryID']]['description']; ?></p>
            </div>
            <div id="thm_organizer_ee_event_cat_disp_div" >
            <?php if($this->categories[$this->event['categoryID']]['global']): ?>
                <p><?php echo JText::_('Termine/Ereignisse erstellt für dieser Kategorie werden auf jeden Monitor als &quot;allgemeine Infos&quot; angezeigt.'); ?></p>
            <?php endif; if($this->categories[$this->event['categoryID']]['reserves']): ?>
                <p><?php echo JText::_('Termine/Ereignisse erstellt für dieser Kategorie werden auf Monitoren als &quot;Vorsicht&quot; angezeigt wo eine ausgewählte Ressource betroffen ist.'); ?></p>
            <?php endif; ?>
            </div>
            <div id="thm_organizer_ee_content_cat_desc_div" >

            </div>
        </div>
        <div id="thm_organizer_ee_name_div">
            <div class="thm_organizer_ee_label_div" >
                <label class="thm_organizer_ee_label" for="title">
                    <?php echo JText::_('Titel:'); ?>
                </label>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <input type='text' name='title' size='56' id='title' value='<?php echo $title; ?>' />
            </div>
        </div>
        <div id="thm_organizer_ee_desc_div">
            <div class="thm_organizer_ee_label_div" >
                <label class="thm_organizer_ee_label" for="description">
                    <?php echo JText::_('Beschreibung:'); ?>
                </label>
            </div>
            <div class="thm_organizer_ee_data_div" >
                <textarea name='description' rows='6' cols='44' id='description'><?php
                    if(isset($error) && $_POST['description']) echo $_POST['description'];
                    else if(isset($event['description'])) echo $event['description'];
                    else echo"";
                ?></textarea>
            </div>
        </div>
        <div id="thm_organizer_ee_time_div">
            <table>
                <tr>
                    <td>
                        <label class="thm_organizer_ee_label" for="startdate">
                            <?php echo JText::_('Startdatum:'); ?>
                        </label>
                    </td>
                    <td>
                        <?php echo $this->startcalendar; ?>
                    </td>
                    <td>
                        <label class="thm_organizer_ee_label" for="starttime">
                            <?php echo JText::_('Startzeit:'); ?>
                        </label>
                    </td>
                    <td>
                        <input type='text' name='starttime' size='2' maxlength='11' id='starttime'
                               class='inputbox validate-time' value='<?php echo $event['starttime']; ?>'/>
                    </td>
                    <td>
                        <label class="thm_organizer_ee_label" for="rec_type_block">
                           <?php echo JText::_('Durchgehend:'); ?>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_block" name="rec_type" <?php echo $blockchecked;?> value="0">
                        <span class="hasTip" title="Durchgehend::Der Termin beginnt am Startdatum zur Startzeit und endet am Enddatum zur Endzeit.">
                            <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format HH:MM" />
                        </span>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label class="thm_organizer_ee_label" for="enddate">
                            <?php echo JText::_('Enddatum:'); ?>
                        </label>
                    </td>
                    <td>
                        <?php echo $this->endcalendar; ?>
                    </td>
                    <td>
                        <label class="thm_organizer_ee_label" for="endtime">
                            <?php echo JText::_('Endzeit:'); ?>
                        </label>
                    </td>
                    <td>
                        <input type='text' name='endtime' size='2' maxlength='11' id='endtime'
                               class='inputbox validate-time' value='<?php echo $event['endtime']; ?>'/>
                    </td>
                    <td>
                        <label class="thm_organizer_ee_label" or="rec_type_daily">
                           <?php echo JText::_('Täglich:'); ?>
                        </label>
                    </td>
                    <td>
                        <input type="radio" id="rec_type_daily" name="rec_type" <?php echo $dailychecked;?> value="1">
                        <span class="hasTip" title="T&auml;glich::Der Termin findet t&auml;glich zwischen Start- und Endzeit statt, an allen Tagen zwischen Start- und Enddatum.">
                            <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format HH:MM" />
                        </span>
                    </td>
                </tr>
            </table>
        </div>
        <div id="thm_organizer_ee_resource_selection_div" >
            <table>
                <tr>
                    <td align="center"><?php echo JText::_( 'Dozent:' ); ?></td>
                    <td align="center"><?php echo JText::_( 'Raum:' ); ?></td>
                    <td align="center"><?php echo JText::_( 'Benutzergruppen:' ); ?></td>
                </tr>
                <tr>
                    <td><?php echo $this->teacherselect; ?></td>
                    <td><?php echo $this->roomselect; ?></td>
                    <td><?php echo $this->groupselect; ?></td>
                </tr>
            </table>
        </div>
        <div id="thm_organizer_ee_button_div">
            <button type="submit" class="submit" id="btnsubmit" onclick="return submitbutton('saveevent');">
                <?php echo JText::_('SAVE') ?>
            </button>
            <input id="btnreset" type="reset" name="reset" value="Reset" />
            <button type="reset" class="button cancel" id="btncancel" onclick="submitbutton('cancelevent');">
                <?php echo JText::_('Abbrechen'); ?>
            </button>
        </div>
        <input type='hidden' name='author' value='<?php echo $this->userid; ?>' />
        <input type='hidden' name='eventid' value='<?php echo $eventid; ?>' />
        <input type='hidden' name='contentid' value='<?php echo $contentid; ?>' />
        <input type='hidden' name='task' value='save_event' />
    </form>
</div>
