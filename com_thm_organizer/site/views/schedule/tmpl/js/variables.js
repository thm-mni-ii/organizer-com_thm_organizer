var variables = [];
variables.SEMESTER_MODE = 1;
variables.PERIOD_MODE = 2;
variables.INSTANCE_MODE = 3;
variables.ajaxbase = "<?php echo JUri::root() . 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw'; ?>";
variables.auth = "<?php echo !empty(JFactory::getUser()->id)? urlencode(password_hash(JFactory::getUser()->email . JFactory::getUser()->registerDate, PASSWORD_BCRYPT)) : ''; ?>";
variables.dateFormat = "<?php echo $this->dateFormat; ?>";
variables.defaultGrid = JSON.parse('<?php echo $this->defaultGrid->grid; ?>');
variables.departmentID = "<?php echo $this->departmentID; ?>";
variables.deltaDays = "<?php echo $this->deltaDays; ?>";
variables.grids = [];
<?php
foreach ($this->getModel()->grids as $key => $grid)
{
?>
variables.grids[<?php echo $key; ?>] = {
	"id" : "<?php echo $grid->id; ?>",
	"grid" : '<?php echo $grid->grid; ?>'
}
<?php
}
?>
variables.exportbase = "<?php echo JUri::root() .'index.php?option=com_thm_organizer&view=schedule_export'; ?>";
variables.isMobile = "<?php echo $this->isMobile; ?>";
variables.registered = "<?php echo !empty(JFactory::getUser()->id); ?>";
variables.subjectDetailbase = "<?php echo JUri::root() .'index.php?option=com_thm_organizer&view=subject_details&id=1'; ?>";
variables.username = "<?php echo !empty(JFactory::getUser()->id)? JFactory::getUser()->username : ''; ?>";