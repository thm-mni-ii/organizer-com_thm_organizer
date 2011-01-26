<?php ?>

<div id="thm_organizer_rl">
    <fieldset id="thm_organizer_rl_fieldset">
	<legend id="thm_organizer_rl_legend">Bitte w&auml;hlen Sie einen Raum:</legend>
	<form enctype='multi'
              name='roomlist'
              method='post'
              action='<?php echo JRoute::_( 'index.php?option=com_thm_organizer&view=roomdisplay') ?>' ><br />
            <?php echo $this->roomlist; ?><br /><br />
            <?php echo $this->calendar; ?><br /><br />
            <input type="submit" value="Submit">
	</form>
    </fieldset>
</div>