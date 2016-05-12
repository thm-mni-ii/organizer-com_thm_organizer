jQuery( document ).ready(function()
{
    Joomla.submitbutton = function (task)
    {
        var match = task.match(/\.cancel$/), form;
        if (match !== null || document.formvalidator.isValid(document.id('item-form')))
        {
            form = document.getElementById('item-form');
            form.task.value = task;
            form.submit();
        }
    }
});