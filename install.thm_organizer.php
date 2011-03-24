<?php
/**
 * @package     Joomla.Site | Joomla.Administrator
 * @subpackage  [typ]_thm_[name]
 * @author      [Vorname] [Nachname] [Email]
 * @copyright   TH Mittelhessen <Jahr>
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     [versionsnr]
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

function com_install()
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
            $eventdir = JFolder::create(JPATH_SITE.'/images/thm_organizer/events');
            if(!isset($eventdir)) $eventdir = false;
            $objectdir = JFolder::create(JPATH_SITE.'/images/thm_organizer/events');
            if(!isset($objectdir)) $objectdir = false;
        }
    }

    $downexists = JFolder::exists( JPATH_SITE.'/components/thm_organizer/down' );
    if(!isset($downexists)) $downexists = false;
    if(!$downexists)
    {
    	$downmakedir = JFolder::create( JPATH_SITE.'/components/thm_organizer/down' );
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
        <?php if($eventdir){ ?>
            <font color='green'>The directory /images/thm_organizer/events has been created.</font><br />
        <?php }else{ ?>
            <font color='red'>The directory /images/thm_organizer/events could not be created.</font><br />
        <?php } ?>
        <?php if($objectdir){ ?>
            <font color='green'>The directory /images/thm_organizer/objects has been created.</font><br />
        <?php }else{ ?>
            <font color='red'>The directory /images/thm_organizer/objects could not be created.</font><br />
        <?php }
        if($downmakedir){ ?>
            <font color='green'>The directory /components/thm_organizer/down has been created.</font><br />
        <?php }else{ ?>
            <font color='red'>The directory /components/thm_organizer/down could not be created.</font><br />
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
?>