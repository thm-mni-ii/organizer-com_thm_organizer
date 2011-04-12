window.addEvent('domready', function() {
    document.formvalidator.setHandler('germandate',
        function (value) {
                regex=/^[0-3][0-9].[0-1][0-9].[0-9]{4}$/;
                return regex.test(value);
    });
});

function checkAll()
{
    var checkbox = document.getElementsByName('eventIDs[]');
    if(checkbox[0].checked == true)
        for (i = 0; i < checkbox.length; i++) checkbox[i].checked = true;
    else unCheckAll();
}
function unCheckAll()
{
    var checkbox = document.getElementsByName('eventIDs[]');
    for (i = 0; i < checkbox.length; i++) checkbox[i].checked = false ;
}
function submitForm(task)
{
    if(task == 'events.new')
    {
        unCheckAll();
        task = 'events.edit';
    }
    document.getElementById('task').value = task;
    document.getElementById('thm_organizer_el_form').submit();
}
function reSort( col, dir )
{
    document.getElementById('orderby').value=col;
    document.getElementById('orderbydir').value=dir;
    document.getElementById('thm_organizer_el_form').submit();
}
function resetForm()
{
    var searchTextInput = document.getElementById('jform_thm_organizer_el_search_text');
    searchTextInput.value='';
    var category = document.getElementById('categoryID');
    if(category != null)
    {
        var index = 0;
        for(index = 0; index < category.length; index++)
        {
            if(category[index].value == -1)
                category.selectedIndex = index;
        }
    }
    var limit = document.getElementById('limit');
    if(limit != null)
    {
        var index = 0;
        for(index = 0; index < limit.length; index++)
        {
            if(limit[index].value == '10') limit.selectedIndex = index;
        }
    }
    document.getElementById('jform_fromdate').value='';
    document.getElementById('jform_todate').value='';
    document.getElementById('thm_organizer_el_form').submit();
}

