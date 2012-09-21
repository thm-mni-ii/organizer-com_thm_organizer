/**
 * @package  	Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @author   	Markus Baier <markus.baier@mni.thm.de>
 * @copyright	THM Mittelhessen 2011
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link     	http://www.mni.thm.de
 * @version		$Id$
 **/

function setEcollabLink(element) {

    /* ajax request */
    getModuleCode(element);
	
}

/**
 * Performs a AJAX-Call, in order to get the module code of the given asset
 * @param element
 */
function getModuleCode(element) {

    var ajaxCall = new Request(
    {
        url : "index.php?option=com_thm_organizer&tmpl=component&task=mappings.getAssetRecord&id="
        + element.options[element.selectedIndex].value,

        onRequest : function() {

        },
        onComplete : function(response) {
							
            var record = JSON.decode(response);
							
            /* store the ajax response to the hidden field */	
            document.getElementById("jform_note_ifr").contentWindow.document.body.innerHTML = record.note;
            document.getElementById("jform_ecollaboration_link").value = record.ecollaboration_link;
							
            for (var i=0; i < document.getElementById('color_id').length; i++) {
                if (document.getElementById('color_id')[i].value == record.color_id) {
                    document.getElementById('color_id')[i].selected = true;
                }
            }

            if(record.menu_link == 0) {	
                document.getElementById('jform_menu_link').selectedIndex='-1';
            } else {
                for (var i=0; i < document.getElementById('jform_menu_link').length; i++) {
                    if (document.getElementById('jform_menu_link')[i].value == record.menu_link) {
                        document.getElementById('jform_menu_link')[i].selected = true;
                    }
                }
            }		
        }
    }).send();

}