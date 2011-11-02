<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_thm_organizer
 * @name        template for display of content on registered monitors
 * @author      James Antrim jamesDOTantrimATyahooDOTcom
 * @copyright   TH Mittelhessen 2011
 * @license     GNU GPL v.2
 * @link        www.mni.fh-giessen.de
 * @version     1.7.0
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<iframe src="images/thm_organizer/<?php echo $this->content ?>#view=fit&scrollbar=0&toolbar=0&navpanes=0&statusbar=0"
        height="100%"
        width ="100%">
</iframe>
