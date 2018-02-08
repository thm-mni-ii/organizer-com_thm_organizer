jQuery(document).ready(function () {
    Joomla.submitbutton = function (task) {
        var match = task.match(/\.cancel$/),
            itemForm = document.getElementById('item-form');

        if (match !== null || document.formvalidator.isValid(itemForm))
        {
            itemForm.task.value = task;
            itemForm.submit();
        }
    }
});
