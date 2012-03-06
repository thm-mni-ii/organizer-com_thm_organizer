<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @description component installer script file
 * @author      James Antrim jamesDOTantrimATmniDOTthmDOTde
 * @author      Wolf Rost wolfDOTrostATmniDOTthmDOTde
 * @copyright   TH Mittelhessen 2012
 * @license     GNU GPL v.2
 * @link        www.mni.thm.de
 * @version     2.5
 */
defined('_JEXEC') or die;
class com_thm_organizerInstallerScript
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
        echo "<div><div style='width: 100%;'>";
        echo "Released under the terms and conditions of the ";
        echo "<a href='http://www.gnu.org/licenses/gpl-2.0.html' target='_blank'>GNU General Public License</a>.";
        echo "</div><div style='width: 100%;'><code>Directory Status:<br />";
        if($direxists)
        {
            echo "<font color='green'>The directory/images/thm_organizer had already been created.</font><br />";
        }
        else if($makedir)
        {
            echo "<font color='green'>The directory /images/thm_organizer has been created.</font><br />";
            if($categorydir)
                echo "<font color='green'>The directory /images/thm_organizer/categories has been created.</font><br />";
            else
                echo "<font color='red'>The directory /images/thm_organizer/categories could not be created.</font><br />";
            if($resourcedir)
                echo "<font color='green'>The directory /images/thm_organizer/resources has been created.</font><br />";
            else
                echo "<font color='red'>The directory /images/thm_organizer/resources could not be created.</font><br />";
            if($downmakedir)
                echo "<font color='green'>The directory /components/com_thm_organizer/down has been created.</font><br />";
            else
                echo "<font color='red'>The directory /components/com_thm_organizer/down could not be created.</font><br />";
        }
        else echo "<font color='red'>The directory /images/thm_organizer could not be created.</font><br />";
        echo "</code></div><div style='width: 100%;'><code>Installation Status:<br />";
        if(($direxists) || ($makedir))
        {
            echo "<font color='green'><b>THM - Organizer successfully installed.</b></font><br />";
            echo "Ensure that THM - Organizer has write access to the directories shown above.";
        }
        else
        {
            echo "<font color='red'><b>THM - Organizer could not be successfully installed.</b></font>";
            echo "<br /><br />Please check following directories:<br /><ul>";
            echo "<li>/images/thm_organizer</li>";
            echo "<li>/images/thm_organizer/events</li>";
            echo "<li>/images/thm_organizer/objects</li>";
            echo "</ul><br />";
            echo "If they do not exist, please create these directories, and ensure THM - Organizer has write access to them.<br />";
            echo "Failure to do so will prevent THM - Organizer from being able upload images.";
        }
        echo "</code></div></div>";
    }

    function uninstall($parent)
    {
        echo "<p>".JText::_('COM_THM_ORGANIZER_UNINSTALL_TEXT')."</p>";
    }

    function update($parent)
    {
        echo "<p>".JText::_('COM_THM_ORGANIZER_UPDATE_TEXT')."</p>";
    }
}