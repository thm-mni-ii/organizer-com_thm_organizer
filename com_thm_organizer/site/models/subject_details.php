<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        THM_OrganizerModeldetails
 * @description THM_OrganizerModeldetails component site model
 * @author      Markus Baier, <markus.baier@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;
jimport('joomla.application.component.model');
jimport('joomla.filesystem.path');
require_once JPATH_COMPONENT . DS . 'helper' . DS . 'teacher.php';

/**
 * Class THM_OrganizerModeldetails for component com_thm_organizer
 *
 * Class provides methods to get details about modules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THM_OrganizerModelSubject_Details extends JModel
{
    public $lsfID = null;

    public $languageTag = null;

    public $subject = null;

    /**
     * Builds the data model of the requested subject
     * 
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        $lsfID = JRequest::getInt('id');
        if (!empty($lsfID))
        {
            $languageTag = JRequest::getString('lang', 'de');
            $this->subject = $this->getSubject($lsfID, $languageTag);
            if (empty($this->subject))
            {
                return;
            }
            if (!empty($this->subject['frequency']))
            {
                $this->resolveFrequency();
            }
        }
    }

    /**
     * Loads subject information from the database
     * 
     * @param   int     $lsfID        the lsf id of the subject requested
     * @param   string  $languageTag  the language to be used in the output
     * 
     * @return  array  an array of information about the subject
     */
    private function getSubject($lsfID, $languageTag)
    {
        $dbo = JFactory::getDbo();
        $query = $dbo->getQuery(true);

        $select = 'id, lsfID, hisID, externalID, ';
        $select .= "name_$languageTag AS name, short_name_$languageTag AS short_name, ";
        $select .= "abbreviation_$languageTag AS abbreviation, description_$languageTag AS description, ";
        $select .= "objective_$languageTag AS objective, content_$languageTag AS content, ";
        $select .= "preliminary_work_$languageTag AS preliminary_work, ";
        $select .= "creditpoints, expenditure, present, independent, proof, frequency, method";

        $query->select($select);
        $query->from('#__thm_organizer_subjects');
        $query->where("lsfID = '$lsfID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadAssoc();
    }

    /**
     * Replaces the numeric frequency code with the corresponding text.
     * 
     * @return  void
     */
    private function resolveFrequency()
    {
        switch ($this->subject['frequency'])
        {
            case 0:
                $this->subject['frequency'] = JText::_('COM_THM_ORGANIZER_SUM_FREQUENCY_APPT');
                return;
            case 1:
                $this->subject['frequency'] = JText::_('COM_THM_ORGANIZER_SUM_FREQUENCY_SUMMER');
                return;
            case 2:
                $this->subject['frequency'] = JText::_('COM_THM_ORGANIZER_SUM_FREQUENCY_WINTER');
                return;
            case 3:
                $this->subject['frequency'] = JText::_('COM_THM_ORGANIZER_SUM_FREQUENCY_SEMESTER');
                return;
            case 4:
                $this->subject['frequency'] = JText::_('COM_THM_ORGANIZER_SUM_FREQUENCY_ASNEEDED');
                return;
            case 5:
                $this->subject['frequency'] = JText::_('COM_THM_ORGANIZER_SUM_FREQUENCY_YEAR');
                return;
        }
    }

	/**
	 * Method to get the teacher
	 *
	 * @param   String  $nrmni  The module id
	 *
	 * @return <Array>
	 */
	public function getDozenten($nrmni)
	{
		$dbo = JFactory::getDBO();
		$query = $dbo->getQuery(true);
		$query->select('DISTINCT dozentid');
		$query->from('#__thm_organizer_dozenten_module');
		$query->where("modulid = '$nrmni'");
		$dbo->setQuery((string) $query);
		return $dbo->loadResultArray();
	}

	/**
	 * Method to parse a ISBN in the correct syntax for the isbnlink plugin
	 *
	 * @param   String   $ISBNText          The bibliography
	 * @param   Boolean  $ISBNPlgAvailable  True if the isbnlink plugin is available otherwise false
	 *
	 * @return  String  The bibliography with the transformed isbn numbers as link
	 */
	public function transformISBN($ISBNText, $ISBNPlgAvailable)
	{
		if ($ISBNPlgAvailable === false)
		{
			return $ISBNText;
		}
		else
		{
			$isbnlinkPlugin = JPluginHelper::getPlugin("content", "thm_isbnlink");
			$pluginParams = json_decode($isbnlinkPlugin->params);
				
			$pluginParams->keyword = "ISBN";
			$ISBNText .= "ISBN:0123456789blabla ISBN 0 12345-678 9";
	
			$pluginKeyword = $pluginParams->keyword;
				
			$matches = $this->getISBNMatches($ISBNText, $pluginKeyword);
	
			var_dump($matches);
			echo "<br/><br/><br/><br/>";
		}
	}
		
	/**
	 * Method to get all ISBN matches
	 * 
	 * @param   String  $ISBNText       The text with isbn
	 * @param   String  $pluginKeyword  The keyword
	 * 
	 * @return Ambigous <>|multitype:
	 */
	public function getISBNMatches($ISBNText, $pluginKeyword)
	{
		$matches = array();
	
		// Result is stored in $matches
		preg_match_all("/" . $pluginKeyword . "((-13)?(:)?(\s)?(\d[-\s]?){12}|(-10)?(:)?(\s)?(\d[-\s]?){9})\d/",
		 $ISBNText, $matches, PREG_PATTERN_ORDER
		);
	
		if ($matches[0])
		{
			return $matches[0];
		}
		else
		{
			return $matches;
		}
	}
}
