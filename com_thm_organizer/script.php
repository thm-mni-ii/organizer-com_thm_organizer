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
 * @category    Joomla.Component.General
 * @package     thm_organizer
 * @subpackage  com_thm_organizer.general
 * @link        www.mni.thm.de
 */
class Com_THM_OrganizerInstallerScript
{
    /**
     * Method to install the component. For some unknown reason Joomla will not resolve text constants in this function.
     * All text constants have been replaced by hard coded English texts. :(
     *
     * @param   object  $parent  the class calling this method
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
    <style>

    .com_thm_organizer_success {
        box-shadow: -5px -5px 25px green inset;
        transition-property: box-shadow;
        transition-duration: 3s;
    }

    .com_thm_organizer_failure {
        box-shadow: -5px -5px 25px red inset;
        transition-property: box-shadow;
        transition-duration: 3s;
    }

    </style>
    <script>

    <?php
    if ($dirCreated AND $tablesFilled)
    {
    ?>
            var status = "com_thm_organizer_success";
    <?php
    }
    else
    {
    ?>
            var status = "com_thm_organizer_failure";

    <?php
    }
    ?>

    window.addEvent('domready', function() {
        $('com_thm_organizer_fieldset').addClass(status);
    });

    </script>
        <fieldset id="com_thm_organizer_fieldset" style="border-radius:10px;">
        <legend>
            <img style="float:none;" src="../media/com_thm_organizer/images/THM_Organizer_Logo.jpg" alt="THM Organizer Logo"/>
        </legend>
        <div style="padding-left:17px;">
            <div style="color:#146295; font-size: 1.182em; font-weight:bold; padding-bottom: 17px" >
            THM Organizer is a component designed to handle the scheduling and planning needs of the
            University of Applied Sciences Central Hessen in Giessen, Germany.</div>
            <div style="width: 100%;">
                Released under the terms and conditions of the
                <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
            </div>
            <table style="border-radius: 5px; border-style: dashed; margin-top: 17px;">

            <!-- Table header -->

                <thead>
                </thead>

            <!-- Table footer -->

                <tfoot>
                </tfoot>

            <!-- Table body -->

            <tbody>
                <tr>
                    <td>Database Table Fill Status</td>
                    <td><span style='color:
                    <?php echo $fillColor; ?>
                    '>
                    <?php echo $fillStatus; ?>
                    </span></td>
                </tr>
                <tr>
                    <td>Directory Status</td>
                    <td><span style='color:
                    <?php echo $dirColor; ?>
                    '>
                    <?php echo $dirStatus; ?>
                    </span></td>
                </tr>
                <tr>
                    <td>Installation Status</td>
                    <td><span style='color:
                    <?php echo $instColor; ?>
                    '>
                    <?php echo $instStatus; ?>
                    </span></td>
                </tr>
            </tbody>

    </table>
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
</fieldset>

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
     * Method to uninstall the component
     *
     * @param   object  $parent  the class calling this method
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * com_thm_organizer update function
     *
     * @param   Object  $parent  JInstallerComponent
     *
     * @return void
     */
    public function update($parent)
    {
        ?>
        <style>
            .com_thm_organizer_success {
                box-shadow: -5px -5px 25px green inset;
                transition-property: box-shadow;
                transition-duration: 3s;
            }
        </style>

        <script>
        window.addEvent('domready', function() {
            $('com_thm_organizer_fieldset').addClass("com_thm_organizer_success");
        });

        </script>

        <fieldset id="com_thm_organizer_fieldset" style="border-radius:10px;">
        <legend>
            <img style="float:none;" src="../media/com_thm_organizer/images/THM_Organizer_Logo.jpg" alt="THM Organizer Logo"/>
        </legend>

        <div style="padding-left: 17px; padding-bottom: 20px">
            <div style="width: 100%;">
                <?php echo JTEXT::_('COM_THM_ORGANIZER_UPDATE_LICENSE')?>
                <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
            </div>
            <table style="border-radius: 5px; border-style: dashed; margin-top: 17px;">

                <!-- Table header -->

                    <thead>
                    </thead>

                <!-- Table footer -->

                    <tfoot>
                    </tfoot>

                <!-- Table body -->

                <tbody>
                    <tr>
                        <td><?php echo JTEXT::_('COM_THM_ORGANIZER_UPDATE_DATABASE_STATUS')?></td>
                        <td><span style='color: green'><?php echo JTEXT::_('COM_THM_ORGANIZER_UPDATE_DATABASE_STATUS_TEXT')?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo JTEXT::_('COM_THM_ORGANIZER_UPDATE_FILES_DIRECTORIES_STATUS')?></td>
                        <td><span style='color: green'><?php echo JTEXT::_('COM_THM_ORGANIZER_UPDATE_FILES_DIRECTORIES_STATUS_TEXT')?></span></td>
                    </tr>
                    <tr>
                        <td><?php echo JTEXT::_('COM_THM_ORGANIZER_UPDATE_UPDATE_STATUS')?></td>
                        <td><span style='color: green'>
                        <?php echo JText::sprintf('COM_THM_ORGANIZER_UPDATE_UPDATE_TEXT', $parent->get('manifest')->version); ?>
                        </span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </fieldset>
    <?php
    }
}
