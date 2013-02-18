<?php
/**
 * @category    Joomla component
 * @package     THM_Organizer
 * @subpackage  com_thm_organizer.general
 * @name        script for installation, update, and uninstall processes
 * @author      James Antrim, <james.antrim@mni.thm.de>
 * @copyright   2012 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 */

defined('_JEXEC') or die;
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Class for the execution of processes during changes to the component itself.
 *
 * @category	Joomla.Component.General
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.general
 * @link        www.mni.thm.de
 */
class COM_THM_OrganizerInstallerScript
{
    /**
     * Method to install the component. For some unknown reason Joomla will not resolve text constants in this function.
     * All text constants have been replaced by hard coded English texts. :(
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
            $fillStatus = 'Default values have been added to the database tables.';
        }
        else
        {
            $fillColor = 'red';
            $fillStatus = 'Default values could not be added to the database tables.';
        }

        $dirCreated = $this->createImageDirectory();
        if ($dirCreated)
        {
            $dirColor = 'green';
            $dirStatus = 'The directory /images/thm_organizer has been created.';
        }
        else
        {
            $dirColor = 'red';
            $dirStatus = 'The directory /images/thm_organizer could not be created.';
        }

        if ($dirCreated AND $tablesFilled)
        {
            $instColor = 'green';
            $instStatus = 'THM Organizer was successfully installed.';
        }
        else
        {
            $instColor = 'yellow';
            $instStatus = 'Problems have occured while installing THM Organizer.';
        }
?>
<div>
    <div style="width: 100%;">
        Released under the terms and conditions of the
        <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
    </div>
    <div style="width: 100%;">
        <h3>Database Table Fill Status:</h3>
        <h4 style='color: <?php echo $fillColor; ?>'>
         <?php echo $fillStatus; ?></h4>
        <h3>Directory Status:</h3>
        <h4 style='color: <?php echo $dirColor; ?>'>
         <?php echo $dirStatus; ?></h4>
        <h3>Installation Status:</h3>
        <h4 style="color: <?php echo $instColor; ?>; font-weight: bold;">
         <?php echo $instStatus; ?></h4>
<?php
if ($tablesFilled AND $dirCreated)
{
?>
            <h4>Please ensure that THM Organizer has write access to the directory mentioned above.</h4>
<?php
}
if (!$tablesFilled)
{
?>
            <h4>An error occurred while adding default values to the database tables. Some values may need to be manually entered.</h4>
<?php
}
if (!$dirCreated)
{
?>
            <h4>Please check the /images/thm_organizer Directory.</h4>
            If it does not exist, please create this directory, and ensure THM - Organizer has write access to it.<br />
            Failure to do so will prevent THM - Organizer from being able use images.

<?php
}
?>
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
        $return = true;
        $fill = JFile::read($this->SQLPath() . DS . 'fill.mysql.utf8.sql');
        $dbo = JFactory::getDbo();
        $queries = $dbo->splitSql($fill);

        foreach ($queries as $query)
        {
            if (trim($query))
            {
                $dbo->setQuery($query);
                if (!$dbo->query())
                {
                    $return = false;
                    JError::raiseWarning(1, JText::sprintf($dbo->getErrorMsg(), $dbo->stderr(true)));
                }
            }
        }
        return $return;
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
        $dirDeleted = JFolder::delete(JPATH_SITE . '/images/thm_organizer');
        echo '<p>' . JText::_('COM_THM_ORGANIZER_UNINSTALL_TEXT') . '</p>';
        if (!$dirDeleted)
        {
            echo JText::_('COM_THM_ORGANIZER_UNINSTALL_DIR_FAIL');
        }
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
        $installedVersion = $this->getVersion();
        if ($installedVersion)
        {
            $updatePath = $this->SQLPath() . DS . 'updates' . DS . 'mysql';
            $updateFiles = JFolder::files($updatePath, '.sql');
            if ($updateFiles)
            {
                sort($updateFiles);
                $dbo = JFactory::getDbo();
                foreach ($updateFiles as $updateVersion)
                {
                    $isNewer = $this->isNewer($installedVersion, $updateVersion);
                    if ($isNewer)
                    {
                        $updateSQL = JFile::read($updatePath . DS . $updateVersion);
                        if ($updateSQL)
                        {
                            $queries = $dbo->splitSql($updateSQL);
                            foreach ($queries as $query)
                            {
                                if (trim($query))
                                {
                                    $dbo->setQuery($query);
                                    if (!$dbo->query())
                                    {
                                        JError::raiseWarning(1, JText::sprintf($dbo->getErrorMsg(), $dbo->stderr(true)));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            echo '<p>' . JText::_('COM_THM_ORGANIZER_UPDATE_TEXT') . '</p>';
        }
    }

    /**
     * Get current version from #__extensions table
     *
     * @return  mixed   string  version if successful, otherwise boolean false
     */
    private function getVersion()
    {
        $extension = JTable::getInstance('Extension');
        $extension->load(array('name' => 'com_thm_organizer'));
        $manifest_cache = new JRegistry($extension->manifest_cache);
        return $manifest_cache->get('version');
    }

    /**
     * Compares the version numbers to see if one is greater than the other
     * 
     * @param   string  $installedVersion  the component version currently installed
     * @param   string  $updateVersion     the sql update file being iterated
     * 
     * @return  boolean  true if the file has a higher version number, otherwise false
     */
    private function isNewer($installedVersion, $updateVersion)
    {
        $installedVersion = explode('.', $installedVersion);
        $updateVersion = explode('.', str_replace('.sql', '', $updateVersion));
        return ($updateVersion[0] >= $installedVersion[0]) AND
		 ($updateVersion[1] >= $installedVersion[1]) AND ($updateVersion[2] > $installedVersion[2]);
    }
}
