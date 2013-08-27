window.addEvent('domready', function()
{
    "use strict";
    var typeElement = document.id("jform_type");
    var selectedElement = typeElement.getSelected();
    showTypeResource(selectedElement[0].index);

    typeElement.addEvent('change',function()
    {
        var selectedElement = this.getSelected();
        showTypeResource(selectedElement[0].index);
    }); 
});

function showTypeResource(index)
{
    "use strict";
    var teacherDepartmentElement = document.id("vse_teacherDepartment");
    var roomDepartmentElement = document.id("vse_roomDepartment");
    var classDepartmentElement = document.id("vse_classDepartment");
    var teachersElement = document.id("vse_teachers");
    var roomsElement = document.id("vse_rooms");
    var classesElement = document.id("vse_classes");

    teacherDepartmentElement.hide();
    roomDepartmentElement.hide();
    classDepartmentElement.hide();
    teachersElement.hide();
    roomsElement.hide();
    classesElement.hide();
    if(index === 0)
    {
        classDepartmentElement.show();
        classesElement.show();
    }
    else if(index === 1)
    {
        roomDepartmentElement.show();
        roomsElement.show();        
    }
    else
    {
        teacherDepartmentElement.show();
        teachersElement.show();        
    }
}