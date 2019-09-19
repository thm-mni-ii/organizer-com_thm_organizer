<?php
/**
 * @package     THM_Organizer
 * @extension   com_thm_organizer
 * @author      James Antrim, <james.antrim@nm.thm.de>
 * @copyright   2018 TH Mittelhessen
 * @license     GNU GPL v.2
 * @link        www.thm.de
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

/**
 * Class for the execution of processes during changes to the component itself.
 */
class Com_THM_OrganizerInstallerScript
{
    /**
     * Creates the directory for images used by the component
     *
     * @return boolean true if the directory exists, otherwise false
     */
    private function createImageDirectory()
    {
        return Folder::create(JPATH_ROOT . '/images/thm_organizer');
    }

    /**
     * Method to install the component. For some unknown reason Joomla will not resolve text constants in this function.
     * All text constants have been replaced by hard coded English texts. :(
     *
     * It also seems that under 3.x this function is ignored if the method is upgrade even if no prior installation
     * existed.
     *
     * @param \stdClass $parent - Parent object calling this method.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install($parent)
    {
        $dirCreated = $this->createImageDirectory();

        if ($dirCreated) {
            $dirColor   = 'green';
            $dirStatus  = 'The directory /images/thm_organizer has been created.';
            $instColor  = 'green';
            $instStatus = 'THM Organizer was successfully installed.';
            $status     = 'com_thm_organizer_success';
        } else {
            $dirColor   = 'red';
            $dirStatus  = 'The directory /images/thm_organizer could not be created.';
            $instColor  = 'yellow';
            $instStatus = 'Problems occurred while installing THM Organizer.';
            $status     = 'com_thm_organizer_failure';
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
            var status = "<?php echo $status; ?>";
            window.addEvent('domready', function () {
                $('com_thm_organizer_fieldset').addClass(status);
            });
        </script>
        <fieldset id="com_thm_organizer_fieldset" style="border-radius:10px;">
            <legend>
                <img style="float:none;" src="../components/com_thm_organizer/images/thm_organizer.png"
                     alt="THM Organizer Logo"/>
            </legend>
            <div style="padding-left:17px;">
                <div style="color:#146295; font-size: 1.182em; font-weight:bold; padding-bottom: 17px">
                    THM Organizer is a component designed to handle the scheduling and planning needs of the
                    University of Applied Sciences Central Hessen in Giessen, Germany.
                </div>
                <div style="width: 100%;">
                    Released under the terms and conditions of the
                    <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
                </div>
                <table style="border-radius: 5px; border-style: dashed; margin-top: 17px;">
                    <tbody>
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
                if ($dirCreated) {
                    ?>
                    <h4>Please ensure that THM Organizer has write access to the directory mentioned above.</h4>
                    <?php
                } else {
                    ?>
                    <h4>Please ensure that the /images/thm_organizer directory exists.</h4>
                    <?php
                }
                ?>
            </div>
        </fieldset>
        <?php
    }

	/**
	 * Removes folder contents before update to ensure removal of deprecated files
	 *
	 * @param string $type   the type of action being performed with the component.
	 * @param object $parent the 'parent' running this script
	 *
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function preflight($type, $parent)
	{
		$languageFiles = [
			'/language/de-DE/de-DE.com_thm_organizer.ini',
			'/language/de-DE/de-DE.com_thm_organizer.sys.ini',
			'/language/en-GB/en-GB.com_thm_organizer.ini',
			'/language/de-DE/en-GB.com_thm_organizer.sys.ini'
		];
		foreach ($languageFiles as $languageFile) {
		    if (file_exists(JPATH_ADMINISTRATOR . $languageFile)) {
		        unlink(JPATH_ADMINISTRATOR . $languageFile);
            }
			if (file_exists(JPATH_SITE . $languageFile)) {
				unlink(JPATH_SITE . $languageFile);
			}
        }
	}

    /**
     * Method to uninstall the component
     *
     * @param object $parent the class calling this method
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall($parent)
    {
        $dirDeleted = Folder::delete(JPATH_ROOT . '/images/thm_organizer');
        if (!$dirDeleted) {
            echo Text::_('The directory located at &quot;/images/thm_organizer&quot; could not be removed.');
        }
    }

    /**
     * Provides an output once Joomla! has finished the update process.
     *
     * @param Object $parent \Joomla\CMS\Installer\Adapter\ComponentAdapter
     *
     * @return void
     */
    public function update($parent)
    {
        $logoURL     = 'components/com_thm_organizer/images/thm_organizer.png';
        $licenseLink = '<a href="http://www.gnu.org/licenses/gpl-2.0.html" ';
        $licenseLink .= 'target="_blank">GNU General Public License</a>';
        $version     = (string)$parent->get('manifest')->version;

        $dirSpan    = '';
        $imagePath  = '/images/thm_organizer';
        $dirCreated = $this->createImageDirectory();
        if (!$dirCreated) {
            $failText = sprintf(Text::_('THM_ORGANIZER_IMAGE_FOLDER_FAIL'), $imagePath);
            $dirSpan  .= '<span style="color:red" >' . $failText . '</span>';
        }
        $updateText = sprintf(Text::_('THM_ORGANIZER_UPDATE_MESSAGE'), $version, $licenseLink);
        ?>
        <div class="span5 form-vertical">
            <?php echo HTMLHelper::_('image', $logoURL, Text::_('THM_ORGANIZER')); ?>
            <br/>
            <p><?php echo $updateText . ' ' . $dirSpan; ?></p>
            <br/>
        </div>
        <?php
    }
}
