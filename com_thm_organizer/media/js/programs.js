jQuery(document).ready(function () {
    jQuery('#jformprogramID').change(function () {
        var selectedPrograms = jQuery('#jformprogramID').val();
        if (selectedPrograms === null)
        {
            selectedPrograms = '';
        }
        else
        {
            selectedPrograms = selectedPrograms.join(',');
        }
        var oldSelectedParents = jQuery('#jformparentID').val();
        if (jQuery.inArray('-1', selectedPrograms) != '-1')
        {
            jQuery("#jformprogramID").find('option').removeAttr("selected");
            return false;
        }
        var poolUrl = "<?php echo JUri::root(); ?>index.php?option=com_thm_organizer";
        poolUrl += "&view=pool_ajax&format=raw&task=poolDegreeOptions";
        poolUrl += "&ownID=<?php echo $this->form->getValue('id'); ?>";
        poolUrl += "&programID=" + selectedPrograms;
        poolUrl += "&languageTag=" + '<?php echo $language; ?>';
        jQuery.get(poolUrl, function (options) {
            jQuery('#jformparentID').html(options);
            var newSelectedParents = jQuery('#jformparentID').val();
            var selectedParents = [];
            if (newSelectedParents !== null && newSelectedParents.length)
            {
                if (oldSelectedParents !== null && oldSelectedParents.length)
                {
                    selectedParents = jQuery.merge(newSelectedParents, oldSelectedParents);
                }
                else
                {
                    selectedParents = newSelectedParents;
                }
            }
            else if (oldSelectedParents !== null && oldSelectedParents.length)
            {
                selectedParents = oldSelectedParents;
            }
            jQuery('#jformparentID').val(selectedParents);
        });
    });
});
