<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.site
 * @name        UserSchedule
 * @description UserSchedule file from com_thm_organizer
 * @author      Wolf Rost, <wolf.rost@mni.thm.de>
 * @copyright   2014 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */
defined('_JEXEC') or die;

/**
 * Class UserSchedule for component com_thm_organizer
 *
 * Class provides methods to load and save user schedules
 *
 * @category    Joomla.Component.Site
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.site
 */
class THMUserSchedule
{
   /**
    * joomla session id
    *
    * @var    String
    */
   private $_jsid = null;

   /**
    * JSON data
    *
    * @var    Object
    */
   private $_json = null;

   /**
    * Username
    *
    * @var    String
    */
   private $_username = null;

   /**
    * Config
    *
    * @var    Object
    */
   private $_cfg = null;

   /**
    * Semester id
    *
    * @var    Integer
    */
   private $_semID = null;

   /**
    * Constructor with the configuration object
    *
    * @param   Object  $cfg      A object which has configurations including
    * @param   Array   $options  Options
    */
   public function __construct($cfg, $options = array())
   {
      $this->_jsid = session_id();
      $this->_json = file_get_contents("php://input");
 
      if (isset($options["username"]))
      {
         $this->_username = $options["username"];
      }
      else
      {
         $this->_username = JFactory::getUser()->username;
      }

      $this->_cfg = $cfg;
      if (isset($options["semesterID"]))
      {
         $this->_semID = $options["semesterID"];
      }
      else
      {
         $this->_semID = JFactory::getApplication()->input->getString("semesterID");
      }
   }

   /**
    * Method to save a user schedule
    *
    * @return Array An array with information whether the schedule could be saved
    */
   public function save()
   {
      // Wenn die Anfragen nicht durch Ajax von MySched kommt
      $requestedWith = JFactory::getApplication()->input->get->server('HTTP_X_REQUESTED_WITH', '');
      if (isset($requestedWith))
      {
         if ($requestedWith != 'XMLHttpRequest')
         {
            echo JText::_("COM_THM_ORGANIZER_SCHEDULER_PERMISSION_DENIED");
            return array("success" => false,"data" => array(
                     'code' => 2,
                     'errors' => array(
                           'reason' => JText::_("COM_THM_ORGANIZER_SCHEDULER_PERMISSION_DENIED")
                     )
                ));
         }
      }
      else
      {
         echo JText::_("COM_THM_ORGANIZER_SCHEDULER_PERMISSION_DENIED");
         return array("success" => false,"data" => array(
                     'code' => 2,
                     'errors' => array(
                           'reason' => JText::_("COM_THM_ORGANIZER_SCHEDULER_PERMISSION_DENIED")
                     )
               ));
      }

      if (isset($this->_jsid))
      {
         if ($this->_username != null && $this->_username != "")
         {
            $timestamp = time();

            $dbo = JFactory::getDbo();
 
            $query = $dbo->getQuery(true);

            // Alte Eintraege loeschen - Performanter als abfragen und Updaten
            $query->delete($dbo->quoteName("{$this->_cfg->userScheduleTable}"));
            $query->where("username = '$this->_username' ");
 
            $dbo->setQuery((string) $query);
 
            try
            {
                $result = $dbo->execute();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
 
            // Create a new query object.
            $query = $dbo->getQuery(true);
 
            // Insert columns.
            $columns = array('username', 'data', 'created');
 
            // Insert values.
            $values = array($dbo->quote($this->_username), $dbo->quote($this->_json), $dbo->quote($timestamp));
 
            // Prepare the insert query.
            $query
            ->insert('#__thm_organizer_user_schedules')
            ->columns($dbo->quoteName($columns))
            ->values(implode(',', $values));
             
            // Reset the query using our newly populated query object.
            $dbo->setQuery((string) $query);
            try
            {
                // Execute the query in Joomla 2.5.
                $result = $dbo->execute();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
 
            if ($result === true)
            {
               // ALLES OK
               return array("success" => true,"data" => array(
                   'code' => 1,
                   'errors' => array()
               ));
            }
            else
            {
               // FEHLER
               return array("success" => false,"data" => array(
                     'code' => 2,
                     'errors' => array(
                           'reason' => JText::_("COM_THM_ORGANIZER_SCHEDULER_SAVE_SCHEDULE_ERROR")
                     )
               ));
            }
         }
         else
         {
            // FEHLER
            return array("success" => false,"data" => array(
                'code' => 'expire',
                'errors' => array(
                      'reason' => JText::_("COM_THM_ORGANIZER_SCHEDULER_INVALID_SESSION")
                )
            ));
         }

      }
      else
      {
         // FEHLER
         return array("success" => false,"data" => array(
             'code' => 'expire',
               'errors' => array(
                     'reason' => JText::_("COM_THM_ORGANIZER_SCHEDULER_INVALID_SESSION")
               )
         ));
      }
   }

    /**
     * Method to load a user schedule
     *
     * @return Array An array with information about the loaded schedule
     */
    public function load()
    {
        if (isset($this->_username))
        {
            $dbo = JFactory::getDBO();
            $data = array();
     
            $query = $dbo->getQuery(true);
            $query->select('data');
            $query->from('#__thm_organizer_user_schedules');
            $query->where("username = '$this->_username'");
            $dbo->setQuery((string) $query);
             
            try 
            {
                $rows = $dbo->loadObject();
            }
            catch (runtimeException $e)
            {
                throw new Exception(JText::_("COM_THM_ORGANIZER_DATABASE_EXCEPTION"), 500);
            }
 
            if (is_object($rows) AND isset($rows->data))
            {
               $data = $rows->data;
            }

            if (count($data) === 0)
            {
                return array("data" => $data);
            }

            return array("success" => true, "data" => $data);
        }
        else
        {
            // SESSION FEHLER
            return array("success" => false, "data" => array('code' => 'expire',
               'errors' => array('reason' => JText::_("COM_THM_ORGANIZER_SCHEDULER_INVALID_SESSION")))
            );
        }
    }
}
