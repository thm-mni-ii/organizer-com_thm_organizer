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

    /**
    * Changes a dynamically generated list
    * @param string The name of the list to change
    * @param array A javascript array of list options in the form [key,value,text]
    * @param string The key to display
    * @param string The original key that was selected
    * @param string The original item value that was selected
    */
    function changeCCatList( listname, source, key )
    {
        var list = eval( 'document.eventForm.' + listname );

        // empty the list
        for (i in list.options.length)
        {
            list.options[i] = null;
        }
        i = 0;
        for (x in source)
        {
            if (source[x][0] == key)
            {
                opt = new Option();
                opt.value = source[x][1];
                opt.text = source[x][2];

                list.options[i++] = opt;
            }
            list.length = i;
        }
    }

	var sectioncategories = new Array;
<?php
$i = 0;
foreach ($this->lists['sectioncategories'] as $k=>$items)
{
	foreach ($items as $v) {
		echo "sectioncategories[".$i++."] = new Array( '$k','".addslashes( $v->id )."','".addslashes( $v->title )."' );\n\t\t";
	}
}
?>

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
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_thm_organizer&controller=editevent&Itemid='.$this->itemid) ?>" method="post" name="eventForm" id="eventForm" class="eventForm">
        <table>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="title"><?php echo JText::_('Titel:'); ?></label>
                                <input type='text' name='title' size='57' id='title' value='<?php echo $title; ?>' />
                                <input type='hidden' name='author' value='<?php echo $this->userid; ?>' />
                                <input type='hidden' name='eventid' value='<?php echo $eventid; ?>' />
                                <input type='hidden' name='contentid' value='<?php echo $contentid; ?>' />
                                <input type='hidden' name='task' value='save_event' />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="description"><?php echo JText::_('Beschreibung:'); ?></label>
                                <textarea name='description' rows='3' cols='45' id='description'><?php
                                    if(isset($error) && $_POST['description']) echo $_POST['description'];
                                    else if(isset($event['description'])) echo $event['description'];
                                    else echo"";
                                ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="ecatid"><?php echo JText::_('Termin Kategorie:'); ?></label>
                                <?php echo $this->lists['ecats']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="enddate"><?php echo JText::_('Startdatum:'); ?></label>
                                <?php
                                    echo JHTML::_('calendar', $sd, 'startdate', 'startdate', '%Y-%m-%d',
                                                  array('class' => 'inputbox required validate-date', 'size'=>'7',  'maxlength'=>'11'));
                                ?>
                                <span class="hasTip" title="Format::YYYY-MM-DD">
                                    <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format YYYY-MM-DD" />
                                </span>
                            </td>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="starttime"><?php echo JText::_('Startzeit:'); ?></label>
                                <input type='text' name='starttime' size='2' maxlength='11'
                                       id='starttime' class='inputbox validate-time' value='<?php
                                    if(isset($error) && isset($_POST['starttime'])) echo $_POST['starttime'];
                                    elseif(isset($event['starttime'])) echo trim($event['starttime']);
                                    else echo"";
                                ?>'/>
                            </td>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="rec_type_block"><?php echo JText::_('Durchgehend:'); ?></label>
                                <input type="radio" id="rec_type_block" name="rec_type" <?php echo $blockchecked;?> value="0">
                                <span class="hasTip" title="Durchgehend::Der Termin beginnt am Startdatum zur Startzeit und endet am Enddatum zur Endzeit.">
                                    <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format HH:MM" />
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="enddate"><?php echo JText::_('Enddatum:'); ?></label>
                                <?php
                                    isset($event['enddate'])? $ed = $event['enddate'] : $ed = '';
                                    echo JHTML::_('calendar', $ed, 'enddate', 'enddate', '%Y-%m-%d',
                                                  array('class' => 'inputbox validate-date hasTip',
                                                        'title'=>'Ablaufdatum::Datum an dem das Event nicht mehr angezeigt werden soll.',
                                                        'size'=>'7',
                                                        'maxlength'=>'11')//'onChange'=>'updatePD()'
                                                 );
                                ?>
                                <span class="hasTip" title="Format::YYYY-MM-DD">
                                    <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format YYYY-MM-DD" />
                                </span>
                            </td>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" for="endtime"><?php echo JText::_('Endzeit:'); ?></label>
                                <input type='text' name='endtime' size='2' maxlength='11'
                                       id='endtime' class='inputbox validate-time' value='<?php
                                    if(isset($error) && isset($_POST['endtime'])) echo $_POST['endtime'];
                                    else if(isset($event['endtime'])) echo trim($event['endtime']);
                                    else echo"";
                                ?>'/>
                            </td>
                            <td>
                                <label class="thm_organizer_ee_shortlabel" or="rec_type_daily"><?php echo JText::_('Täglich:'); ?></label>
                                <input type="radio" id="rec_type_daily" name="rec_type" <?php echo $dailychecked;?> value="1">
                                <span class="hasTip" title="T&auml;glich::Der Termin findet t&auml;glich zwischen Start- und Endzeit statt, an allen Tagen zwischen Start- und Enddatum.">
                                    <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format HH:MM" />
                                </span>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table>
                        <tr>
                            <td><?php echo JText::_( 'Dozent:' ); ?></td>
                            <td><?php echo JText::_( 'Raum:' ); ?></td>
                            <td><?php echo JText::_( 'Semestergang:' ); ?></td>
                            <td><?php echo JText::_( 'Benutzergruppen:' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php echo $this->lists['teachers']; ?></td>
                            <td><?php echo $this->lists['rooms']; ?></td>
                            <td><?php echo $this->lists['semesters']; ?></td>
                            <td><?php echo $this->lists['groups']; ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <fieldset>
                        <legend>
                            <?php
                                echo JText::_( 'zus&auml;tzlich als Beitrag anlegen?')."&nbsp;";
                                if(isset($event['contentid'])) echo '<input type="checkbox" name="content" checked="checked" />';
                                else echo '<input type="checkbox" name="content" />';
                            ?>
                        </legend>
                        <p>
                            <label class="thm_organizer_ee_longlabel" for="sectionid"><?php echo JText::_('Beitrag Bereich:'); ?></label>
                            <?php echo $this->lists['sectionid']; ?>
                        </p>
                        <p>
                            <label class="thm_organizer_ee_longlabel" for="ccatid"><?php echo JText::_('Beitrag Kategorie:'); ?></label>
                            <?php echo $this->lists['ccats']; ?>
                        </p>
                        <p>
                            <label class="thm_organizer_ee_longlabel" for="publish_up"><?php echo JText::_('Beitrag anzeigen von:'); ?></label>
                            <?php
                                echo JHTML::_('calendar', $pu, 'publish_up', 'publish_up', '%Y-%m-%d',
                                              array('class' => 'inputbox validate-date', 'size'=>'7',  'maxlength'=>'11'));
                            ?>
                            <span class="hasTip" title="Format::YYYY-MM-DD">
                                <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format YYYY-MM-DD" />
                            </span>
                        </p>
                        <p>
                            <label class="thm_organizer_ee_longlabel" for="publish_down"><?php echo JText::_('Beitrag anzeigen bis:'); ?></label>
                            <?php
                                echo JHTML::_('calendar', $pd, 'publish_down', 'publish_down', '%Y-%m-%d',
                                              array('class' => 'inputbox validate-date hasTip', 'title'=>'Ablaufdatum::Datum an dem das Event nicht mehr angezeigt werden soll.', 'size'=>'7',  'maxlength'=>'11'));
                            ?>
                            <span class="hasTip" title="Format::YYYY-MM-DD">
                                <img src="<?php echo 'components'.DS.'com_thm_organizer'.DS.'assets'.DS.'images'.DS.'hint.png'; ?>" alt="Format YYYY-MM-DD" />
                            </span>
                        </p>
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td>
                    <button type="submit" class="submit" id="btnsubmit" onclick="return submitbutton('saveevent');">
                        <?php echo JText::_('SAVE') ?>
                    </button>
                    <input id="btnreset" type="reset" name="reset" value="Reset" />
                    <button type="reset" class="button cancel" id="btncancel" onclick="submitbutton('cancelevent');">
                        <?php echo JText::_('Abbrechen'); ?>
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>
