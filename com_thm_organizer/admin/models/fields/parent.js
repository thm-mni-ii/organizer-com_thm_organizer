/**
 * @package  	Joomla.Administrator
 * @subpackage  com_thm_organizer
 * @author   	Markus Baier <markus.baier@mni.thm.de>
 * @copyright	THM Mittelhessen 2011
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @link     	http://www.mni.thm.de
 * @version		$Id$
 **/

/**
 * Disables the semester selection, if there is a selected parent
 */
function disableDropdown(element) {

    if (element.selectedIndex > 0) {
		
        /* disable the selectionbox */
        document.getElementById("semesters").disabled = true;
        document.getElementById("semesters").name = "bla";
		
        /* do a ajax request */
        getSemesterFromSelectedPool(element);
    } else {
        document.getElementById("semesters").disabled = false;
    }
}

/**
 * Performs a AJAX-Call, in order to get the related semesters of the chosen parent node
 * @param element
 */
function getSemesterFromSelectedPool(element) {

    var ajaxCall = new Request(
    {

        url : "index.php?option=com_thm_organizer&tmpl=component&task=mappings.getSemester&id="
        + element.options[element.selectedIndex].value,

        onRequest : function() {

        },
        onComplete : function(response) {
				
            /* store the ajax response to the hidden field */
            document.getElementById("jform_stud_sem_id_2").value = response;					

            var semesterIds = response.split(',');
            var hiddenField = document.getElementById('jform_stud_sem_id_2');
            var selectField = document.createElement("SELECT");
							
            selectField.name = "semesters[]";
            selectField.multiple =  "multiple";
								
            for (var i=0; i <= semesterIds.length; i++){
								
                if (typeof (semesterIds[i]) == "undefined") {
                    continue;
                }
								
                var optn = document.createElement("OPTION");
                optn.text = "";
                optn.value = semesterIds[i];
                optn.selected = true;
								
                selectField.options.add(optn);

            }
													
            hiddenField.appendChild(selectField);
						
        }
    }).send();

}