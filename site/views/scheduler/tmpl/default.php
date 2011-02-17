<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!-- <link rel="stylesheet" type="text/css" href="components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/ext-all.css"></link>
<link rel="stylesheet" type="text/css" href="components/com_thm_organizer/views/scheduler/tmpl/ext/resources/css/MultiSelect.css"/>
<link rel="stylesheet" type="text/css" href="components/com_thm_organizer/views/scheduler/tmpl/mySched/style.css"></link> -->

<div id="MySchedMainW" class="MySchedMainW">
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/preLoadingMessage.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/ext/ext-all-debug.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/coreextension.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/MultiSelect.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/libs.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/authorize.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/mapping.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/models.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/readers.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/grid.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/main.js"></script>
	<script type="text/javascript" charset="utf-8" src="components/com_thm_organizer/views/scheduler/tmpl/mySched/plugins.js"></script>
    <script type="text/javascript" charset="utf-8">

    <?php

    	if($this->hasBackendAccess === true)
		{
			require_once("components/com_thm_organizer/views/scheduler/tmpl/mySched/advancedFunctions.js");
		}

    ?>

	<?php 	echo 'MySched.SessionId = \''.$this->jsid.'\';';
			echo 'MySched.class_semester_id = \''.$this->semesterID.'\';';
			echo 'MySched.class_semester_author = \''.$this->semAuthor.'\';';
			echo 'MySched.startup = \''.$this->startup.'\';';
	?>

		Ext.onReady(MySched.Base.init, MySched.Base);
	</script>
	</div>
	<iframe
		id="MySchedexternURL"
		name="MySchedexternURL"
		src="#"
		scrolling="auto"
		align="top"
		frameborder="0"
		class="MySchedexternURLClass_DIS">
	</iframe>