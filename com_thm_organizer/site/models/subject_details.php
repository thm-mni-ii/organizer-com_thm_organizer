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
            if (!empty($this->subject['creditpoints']))
            {
                $this->subject['expenditureOutput'] = "{$this->subject['creditpoints']} CrP";
                if (!empty($this->subject['expenditure']) AND !empty($this->subject['present']))
                {
                    if ($languageTag == 'de')
                    {
                        $this->subject['expenditureOutput'] .= "; {$this->subject['expenditure']} hours, ";
                        $this->subject['expenditureOutput'] .= "of which {$this->subject['present']} is spent in class.";
                    }
                    else
                    {
                        $this->subject['expenditureOutput'] .= "; {$this->subject['expenditure']} hours, ";
                        $this->subject['expenditureOutput'] .= "of which {$this->subject['present']} is spent in class.";
                    }
                }
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

        $select = "s.id, externalID, name_$languageTag AS name, description_$languageTag AS description, ";
        $select .= "objective_$languageTag AS objective, content_$languageTag AS content, instructionLanguage, ";
        $select .= "preliminary_work_$languageTag AS preliminary_work, literature, creditpoints, expenditure, ";
        $select .= "present, independent, proof_$languageTag AS proof, frequency_$languageTag AS frequency, ";
        $select .= "method_$languageTag AS method, pform_$languageTag AS pform";

        $query->select($select);
        $query->from('#__thm_organizer_subjects AS s');
        $query->leftJoin('#__thm_organizer_frequencies AS f ON s.frequencyID = f.id');
        $query->leftJoin('#__thm_organizer_methods AS m ON s.methodID = m.id');
        $query->leftJoin('#__thm_organizer_proof AS p ON s.proofID = p.id');
        $query->leftJoin('#__thm_organizer_pforms AS form ON s.pformID = form.id');
        $query->where("lsfID = '$lsfID'");
        $dbo->setQuery((string) $query);
        return $dbo->loadAssoc();
    }
}
