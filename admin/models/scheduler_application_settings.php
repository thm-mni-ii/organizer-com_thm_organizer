<?php
defined('_JEXEC') or die('Restriced Access');
jimport('joomla.application.component.model');
class thm_organizersModelScheduler_Application_Settings extends JModel
{
	function __construct()
	{
		parent::__construct();

	}

	function getCategories()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = & JFactory::getDBO();
		$query = "SELECT ecid as id, ecname as name
					FROM #__giessen_scheduler_categories";
		$dbo->setQuery( $query );
		$usergroups = $dbo->loadObjectList();
		if(count($usergroups) <= 0)
			return false;
		return $usergroups;
	}

	function getSettings()
	{
		$mainframe = JFactory::getApplication("administrator");
		$dbo = & JFactory::getDBO();
		$query = "SELECT *
					FROM #__giessen_scheduler_settings WHERE id=1";
		$dbo->setQuery( $query );
		$usergroups = $dbo->loadObjectList();
		if(count($usergroups) <= 0)
			return false;
		return $usergroups;
	}

	function store()
	{
		$scheduler_downFolder = JRequest::getVar( 'scheduler_downFolder', '', 'post','string', JREQUEST_ALLOWRAW );
		$scheudler_vacationcat = JRequest::getVar( 'scheduler_vacationcat', '', 'post','int', JREQUEST_ALLOWRAW );
		$scheduler_eStudyPath = JRequest::getVar( 'scheduler_eStudyPath', '', 'post','url', JREQUEST_ALLOWRAW );
		$scheduler_eStudywsapiPath = JRequest::getVar( 'scheduler_eStudywsapiPath', '', 'post','string', JREQUEST_ALLOWRAW );
		$scheduler_eStudyCreateCoursePath = JRequest::getVar( 'scheduler_eStudyCreateCoursePath', '', 'post','string', JREQUEST_ALLOWRAW );
		$scheduler_eStudySoapSchema = JRequest::getVar( 'scheduler_eStudySoapSchema', '', 'post','string', JREQUEST_ALLOWRAW );

		if(isset($scheduler_downFolder) && isset($scheudler_vacationcat) && isset($scheduler_eStudyPath) && isset($scheduler_eStudywsapiPath) && isset($scheduler_eStudyCreateCoursePath) && isset($scheduler_eStudySoapSchema))
		{
			$dbo = & JFactory::getDBO();
			$querydel = "DELETE FROM #__giessen_scheduler_settings WHERE id IN ( 1 );";
			$dbo->setQuery($querydel);
			$dbo->query();

			$queryinsert = "INSERT INTO #__giessen_scheduler_settings
					 (id, downFolder, vacationcat, eStudyPath, eStudywsapiPath, eStudyCreateCoursePath, eStudySoapSchema)
					 VALUES (1, '$scheduler_downFolder', '$scheudler_vacationcat', '$scheduler_eStudyPath', '$scheduler_eStudywsapiPath', '$scheduler_eStudyCreateCoursePath', '$scheduler_eStudySoapSchema')";
			$dbo->setQuery($queryinsert);
			$dbo->query();
			if($dbo->getErrorNum())
			{
				return JText::_("Fehler beim Speichern.");
			}
			else return JText::_("Erfolgreich gespeichert.");
		}
		else
			return "Fehler beim Speichern.<br/>".print_r($_POST, true);
	}
}
?>
