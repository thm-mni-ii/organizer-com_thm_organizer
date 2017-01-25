var variables = [];
variables.SAVE_MODE_SEMESTER = 1; //TODO: aus model holen
variables.SAVE_MODE_PERIOD = 2;
variables.SAVE_MODE_INSTANCE = 3;
variables.dateFormat = "<?php echo $this->dateFormat; ?>";
variables.defaultTimes = JSON.parse('<?php echo $this->defaultGrid->grid; ?>');
variables.departmentID = "<?php echo $this->departmentID; ?>";
variables.registered = "<?php echo !empty(JFactory::getUser()->id); ?>";
variables.username = "<?php echo !empty(JFactory::getUser()->id)? JFactory::getUser()->username : ''; ?>";
variables.auth = "<?php echo !empty(JFactory::getUser()->id)? urlencode(password_hash(JFactory::getUser()->email . JFactory::getUser()->registerDate, PASSWORD_BCRYPT)) : ''; ?>"
variables.exportbase = "<?php echo JUri::root() .'index.php?option=com_thm_organizer&view=schedule_export'; ?>";