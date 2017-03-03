var variables = [];
variables.SEMESTER_MODE = 1;
variables.PERIOD_MODE = 2;
variables.INSTANCE_MODE = 3;
variables.ajaxbase = "<?php echo JUri::root() . 'index.php?option=com_thm_organizer&view=schedule_ajax&format=raw'; ?>";
variables.auth = "<?php echo !empty(JFactory::getUser()->id)? urlencode(password_hash(JFactory::getUser()->email . JFactory::getUser()->registerDate, PASSWORD_BCRYPT)) : ''; ?>";
variables.dateFormat = "<?php echo $this->dateFormat; ?>";
variables.defaultGrid = JSON.parse('<?php echo $this->defaultGrid->grid; ?>');
variables.departmentID = "<?php echo $this->model->params['departmentID']; ?>";
variables.deltaDays = "<?php echo $this->model->params['deltaDays']; ?>";
<?php if (!empty($this->model->params['displayName'])): ?>
variables.displayName = "<?php echo $this->model->params['displayName']; ?>";
<?php endif; ?>
variables.grids = [];
<?php foreach ($this->model->grids as $key => $grid): ?>
variables.grids[<?php echo $key; ?>] = {
	"id" : "<?php echo $grid->id; ?>",
	"grid" : '<?php echo $grid->grid; ?>'
};
<?php endforeach; ?>
variables.exportbase = "<?php echo JUri::root() .'index.php?option=com_thm_organizer&view=schedule_export'; ?>";
variables.isMobile = "<?php echo $this->isMobile; ?>";
<?php if (!empty($this->model->params['poolIDs'])): ?>
variables.poolIDs = JSON.parse("<?php echo json_encode($this->model->params['poolIDs']); ?>");
<?php endif; ?>
<?php if (!empty($this->model->params['programIDs'])): ?>
variables.programIDs = JSON.parse("<?php echo json_encode($this->model->params['programIDs']); ?>");
<?php endif; ?>
variables.registered = "<?php echo !empty(JFactory::getUser()->id); ?>";
<?php if (!empty($this->model->params['roomIDs'])): ?>
variables.roomIDs = JSON.parse("<?php echo json_encode($this->model->params['roomIDs']); ?>");
<?php endif; ?>
<?php if (!empty($this->model->params['roomTypeIDs'])): ?>
variables.roomTypeIDs = JSON.parse("<?php echo json_encode($this->model->params['roomTypeIDs']); ?>");
<?php endif; ?>
variables.showPrograms = "<?php echo $this->model->params['showPrograms']; ?>";
variables.showPools = "<?php echo $this->model->params['showPools']; ?>";
variables.showRooms = "<?php echo $this->model->params['showRooms']; ?>";
variables.showTeachers = "<?php echo $this->model->params['showTeachers']; ?>";
<?php if (!empty($this->model->params['subjectIDs'])): ?>
variables.subjectIDs = JSON.parse("<?php echo json_encode($this->model->params['subjectIDs']); ?>");
<?php endif; ?>
variables.subjectDetailbase = "<?php echo JUri::root() .'index.php?option=com_thm_organizer&view=subject_details&id=1'; ?>";
<?php if (!empty($this->model->params['teacherIDs'])): ?>
variables.teacherIDs = JSON.parse("<?php echo json_encode($this->model->params['teacherIDs']); ?>");
<?php endif; ?>
variables.username = "<?php echo !empty(JFactory::getUser()->id)? JFactory::getUser()->username : ''; ?>";