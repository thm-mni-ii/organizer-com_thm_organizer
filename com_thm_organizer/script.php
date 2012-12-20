<?php
/**
 *@category    component
 *
 *@package     THM_Organizer
 *
 *@subpackage  com_thm_organizer
 *@name        script for installation, update, and uninstall processes
 *@author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 *
 *@copyright   2012 TH Mittelhessen
 *
 *@license     GNU GPL v.2
 *@link        www.mni.thm.de
 *@version     0.1.1
 */
defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
/**
 * Class for the execution of processes during changes to the component itself.
 *
 * @package  Admin
 *
 * @since    2.5.4
 */
class com_thm_organizerInstallerScript
{
    /**
     * Method to install the component
     *
     * @param   object  $parent  the class calling this method
     *
     * @return void
     */
    public function install($parent)
    {
        $tablesFilled = $this->fillTables();
        if ($tablesFilled)
        {
            $fillColor = 'green';
            $fillStatus = JText::_('COM_THM_ORGANIZER_FILL_SUCCESS');
        }
        else
        {
            $fillColor = 'red';
            $fillStatus = JText::_('COM_THM_ORGANIZER_FILL_FAIL');
            $fillText = JText::_();
        }

        $dirCreated = $this->createImageDirectory();
        if ($dirCreated)
        {
            $dirColor = 'green';
            $dirStatus = JText::_('COM_THM_ORGANIZER_INSTALL_DIR_SUCCESS');
        }
        else
        {
            $dirColor = 'red';
            $dirStatus = JText::_('COM_THM_ORGANIZER_INSTALL_DIR_FAIL');
            $dirText = JText::_();
        }

        if ($dirCreated AND $tablesFilled)
        {
            $instColor = 'green';
            $instStatus = JText::_('COM_THM_ORGANIZER_INSTALL_INST_SUCCESS');
            $instText = JText::_('COM_THM_ORGANIZER_INSTALL_INST_SUCCESS_TEXT');
        }
        elseif ($tablesFilled)
        {
            $instColor = 'yellow';
            $instStatus = JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL');
            $instText = JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL_DIR_TEXT');
        }
        elseif ($dirCreated)
        {
            $instColor = 'yellow';
            $instStatus = JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL');
            $instText = JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL_FILL_TEXT');
        }
        else
        {
            $instColor = 'yellow';
            $instStatus = JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL');
            $instText = JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL_FILL_TEXT') . "<br />";
            $instText .= JText::_('COM_THM_ORGANIZER_INSTALL_INST_FAIL_DIR_TEXT');
        }
?>
<div>
    <div style="width: 100%;">
        Released under the terms and conditions of the <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
    </div>
    <div style="width: 100%;">
        <code>Database Table Fill Status:<br />
            <span style='color: <?php echo $fillColor; ?>'><?php echo $fillStatus; ?></span><br />
        </code>
        <code>Directory Status:<br />
            <span style='color: <?php echo $dirColor; ?>'><?php echo $dirStatus; ?></span><br />
        </code>
        <code>Installation Status:<br />
            <span style="color: <?php echo $instColor; ?>; font-weight: bold;"><?php echo $instStatus; ?></span>
            <br /><br />
            <?php echo $instText; ?>
        </code>
    </div>
</div>
<?php
    }

    /**
     * Method returning the administrative compontent path
     *
     * @return  string  path to the component's sql directory 
     */
    private function SQLPath()
    {
        return JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_thm_organizer' . DS . 'sql';
    }

    /**
     * Creates the directory for images used by the component
     * 
     * @return  boolean true if the directory exists, otherwise false 
     */
    private function createImageDirectory()
    {
        $success = JFolder::exists(JPATH_SITE . '/images/thm_organizer');
        if ($success)
        {
            return $success;
        }
        else
        {
            return JFolder::create(JPATH_SITE . '/images/thm_organizer');
        }
    }

    /**
     * Fills tables with default values
     * 
     * @return  boolean true on successful fill otherwise false 
     */
    private function fillTables()
    {
        $fill = JFile::read($this->SQLPath() . DS . 'fill.mysql.utf8.sql');
        $dbo = JFactory::getDbo();
        $queries = $dbo->splitSql($input);

        // Execute the single queries
        foreach ($queries as $query)
        {
            if (trim($query))
            {
                $dbo->setQuery($query);
                if (!$dbo->query())
                {
                    JError::raiseWarning(1, JText::sprintf('COM_THM_ORGANIZER_SQL_ERROR', $dbo->stderr(true)));
                }
            }
        }
    }

    /**
     * method to uninstall the component
     *
     * @param   object  $parent  the class calling this method
     *
     * @return void
     */
    public function uninstall($parent)
    {
        echo '<p>' . JText::_('COM_THM_ORGANIZER_UNINSTALL_TEXT') . '</p>';
    }

    /**
     * method to update the component
     *
     * @param   object  $parent  the class calling this method
     *
     * @return void
     */
    public function update($parent)
    {
        echo '<p>' . JText::_('COM_THM_ORGANIZER_UPDATE_TEXT') . '</p>';

        $updateFiles = JFolder::files($this->SQLPath() . DS . 'updates' . DS . 'mysql', '.sql');
        $dbo = JFactory::getDbo();

        // Process files
        foreach ($updateFiles as $updateFile)
        {
            $update = JFile::read($path . DS . $updateFile);
            if ($update)
            {
                $queries = $dbo->splitSql($update);
                foreach ($queries as $query)
                {
                    if (trim($query))
                    {
                        $dbo->setQuery($query);
                        if (!$dbo->query())
                        {
                            JError::raiseWarning(1, JText::sprintf('COM_THM_ORGANIZER_SQL_ERROR', $dbo->stderr(true)));
                        }
                    }
                }
            }
        }

    }
}
