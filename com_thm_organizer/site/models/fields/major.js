/*globals Request */
window.addEvent("domready", function() {
    //alert("asd");
    });

function loadSemesters(id)
{
    "use strict";
    var ajaxCall = new Request(
    {
        url : "index.php?option=com_thm_organizer&task=assets.getSemester&tmpl=component&id=" + id,
        onRequest : function() {},
        onComplete : function(response)
        {
            var ret = JSON.decode(response);

            //var semesterIds = response.split(',');
            var existentSelectField = document
            .getElementById('jformparamssemesters');

            var i;
            for (i = existentSelectField.options.length - 1; i >= 0; i--)
            {
                existentSelectField.remove(i);
            }

            var selectField = document.createElement("SELECT");

            selectField.name = "jform[params][semesters][]";
            selectField.multiple = "multiple";

            ret.each(function(item)
            {
                var optn = document.createElement("OPTION");
                optn.text = item.name;
                optn.value = item.id;

                existentSelectField.options.add(optn);
            });
        }
    }).send();
}