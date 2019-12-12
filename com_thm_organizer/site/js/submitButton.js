jQuery(document).ready(function () {
    Joomla.submitbutton = function (task) {
        var match = task.match(/\.cancel$/),
            adminForm = document.getElementById('adminForm');

        if (match !== null || document.formvalidator.isValid(adminForm)) {
            adminForm.task.value = task;
            adminForm.submit();
        }
    }
});
