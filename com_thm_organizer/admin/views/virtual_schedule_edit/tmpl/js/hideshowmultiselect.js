/* globals Ext: false */
function setRessource()
{
    "use strict";
    var types = Ext.get('vscheduler_types');
    var classes = Ext.get('vscheduler_classes');
    var rooms = Ext.get('vscheduler_rooms');
    var teachers = Ext.get('vscheduler_teachers');

    var roomDepartments = Ext.get('vscheduler_roomDepartments');
    var teacherDepartments = Ext.get('vscheduler_teacherDepartments');
    var classesDepartments = Ext.get('vscheduler_classesDepartments');

    classes.dom.parentNode.style.display = "none";
    rooms.dom.parentNode.style.display = "none";
    teachers.dom.parentNode.style.display = "none";

    roomDepartments.dom.parentNode.style.display = "none";
    teacherDepartments.dom.parentNode.style.display = "none";
    classesDepartments.dom.parentNode.style.display = "none";

    if(types.getValue() === "class")
    {
        classes.dom.parentNode.style.display = "block";
        classesDepartments.dom.parentNode.style.display = "block";
    }
    else
    if(types.getValue() === "room")
    {
        rooms.dom.parentNode.style.display = "block";
        roomDepartments.dom.parentNode.style.display = "block";
    }
    else
    if(types.getValue() === "teacher")
    {
        teachers.dom.parentNode.style.display = "block";
        teacherDepartments.dom.parentNode.style.display = "block";
    }
}

Ext.onReady(function()
{
    "use strict";
    setRessource();
});
