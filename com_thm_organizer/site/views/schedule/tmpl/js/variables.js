var variables = [];
variables.SAVE_MODE_SEMESTER = 1; //TODO: aus model holen
variables.SAVE_MODE_PERIOD = 2;
variables.SAVE_MODE_INSTANCE = 3;
variables.dateFormat = "<?php echo $this->dateFormat; ?>";
variables.defaultTimes = JSON.parse('<?php echo $this->defaultGrid->grid; ?>');
variables.departmentID = "<?php echo $this->departmentID; ?>";
variables.userID = "<?php echo JFactory::getUser()->id; ?>";