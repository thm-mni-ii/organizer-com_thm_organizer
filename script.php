<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of thm_Organizer component
 */
class com_thm_OrganizerInstallerScript
{
	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		jimport( 'joomla.filesystem.folder' );

	    $direxists = JFolder::exists( JPATH_SITE.'/images/thm_organizer' );
	    if(!isset($direxists)) $direxists = false;
	    if(!$direxists)
	    {
	        $makedir = JFolder::create( JPATH_SITE.'/images/thm_organizer');
	        if(!isset($makedir)) $makedir = false;
	        if($makedir)
	        {
	            $categorydir = JFolder::create(JPATH_SITE.'/images/thm_organizer/categories');
	            if(!isset($categorydir)) $categorydir = false;
	            $resourcedir = JFolder::create(JPATH_SITE.'/images/thm_organizer/resources');
	            if(!isset($objectdir)) $objectdir = false;
	        }
	    }

	    $downexists = JFolder::exists( JPATH_SITE.'/components/com_thm_organizer/down' );
	    if(!isset($downexists)) $downexists = false;
	    if(!$downexists)
	    {
	    	$downmakedir = JFolder::create( JPATH_SITE.'/components/com_thm_organizer/down' );
	    }
	    ?>
	    <div>
    		<div style="width: 100%;">
        		Released under the terms and conditions of the <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License</a>.
    		</div>
    	<div style="width: 100%;">
        <code>Directory Status:<br />
		<?php if($direxists) { ?>
		            <font color='green'>The directory/images/thm_organizer had already been created.</font><br />
		<?php } else if($makedir) { ?>
            <font color='green'>The directory /images/thm_organizer has been created.</font><br />
        <?php if($categorydir){ ?>
            <font color='green'>The directory /images/thm_organizer/categories has been created.</font><br />
        <?php } else { ?>
            <font color='red'>The directory /images/thm_organizer/categories could not be created.</font><br />
        <?php } if ($resourcedir){ ?>
            <font color='green'>The directory /images/thm_organizer/resources has been created.</font><br />
        <?php } else { ?>
            <font color='red'>The directory /images/thm_organizer/resources could not be created.</font><br />
        <?php }
        if($downmakedir){ ?>
            <font color='green'>The directory /components/com_thm_organizer/down has been created.</font><br />
        <?php }else{ ?>
            <font color='red'>The directory /components/com_thm_organizer/down could not be created.</font><br />
        <?php } ?>
		<?php }else { ?>
		        <font color='red'>The directory /images/thm_organizer could not be created.</font><br />
		<?php } ?>
		        </code>
		    </div>
		    <div style="width: 100%;">
		        <code>Installation Status:<br />
		<?php if(($direxists) || ($makedir)){ ?>
		            <font color="green"><b>THM - Organizer successfully installed.</b></font><br />
		            Ensure that THM - Organizer has write access to the directories shown above.
		<?php } else { ?>
		            <font color="red"><b>THM - Organizer could not be successfully installed.</b></font>
		            <br /><br />
		            Please check following directories:<br />
		            <ul>
		                <li>/images/thm_organizer</li>
		                <li>/images/thm_organizer/events</li>
		                <li>/images/thm_organizer/objects</li>
		            </ul>
		            <br />
		                If they do not exist, please create these directories, and ensure THM - Organizer has write access to them.<br />
		                Failure to do so will prevent THM - Organizer from being able upload images.
		<?php } ?>
		        </code>
		    </div>
		</div>
		<?php
	}

	/**
	 * method to uninstall the component
	 *
	 * @return void
	 */
	function uninstall($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_THM_ORGANIZER_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * method to update the component
	 *
	 * @return void
	 */
	function update($parent)
	{
		// $parent is the class calling this method
		echo '<p>' . JText::_('COM_THM_ORGANIZER_UPDATE_TEXT') . '</p>';
	}

	/**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_THM_ORGANIZER_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent)
	{
		// $parent is the class calling this method
		// $type is the type of change (install, update or discover_install)
		echo '<p>' . JText::_('COM_THM_ORGANIZER_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
	}
}