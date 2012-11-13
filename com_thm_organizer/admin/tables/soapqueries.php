<?php
/**
 * @package  	Joomla.Administrator
 * @subpackage  com_thm_lsf
 * @author   	Markus Baier <markus.baier@mni.fh-giessen.de>
 * @copyright	THM Mittelhessen 2011
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link     	http://www.mni.fh-giessen.de
 * @version		$Id: configuration.php 3035 2011-01-21 09:32:19Z m.baier $
 **/

defined('_JEXEC') or die('Restricted access');

class THM_OrganizerTableSoapqueries extends JTable
{    
    function __construct(&$db)
    {
        parent::__construct('#__thm_organizer_soap_queries', 'id', $db);
    }
    
}
?>