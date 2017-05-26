<script type='text/javascript' charset='utf-8'>

jQuery(document).ready(function() {

    var defaultDepartment = <?php echo JComponentHelper::getParams('com_thm_organizer')->get("department", 5); ?> ;

    var old_department;

    var departmentID = jQuery('#jform_departmentID');
    var isPrepCourse = jQuery('#jform_is_prep_course');

    var advanced = [
        'prerequisites',
        'postrequisites'
    ].map(
        function(elem)
        {
            return jQuery('#jform' + elem).parents('.control-group');
        }
    );

    var header = jQuery("[href*='#mappings']").parent();

    var irrelevant_fields = [
        'aids_de',
        'aids_en',
        'creditpoints',
        'duration',
        'evaluation_de',
        'evaluation_en',
        'expenditure',
        'externalID',
        'frequencyID',
        'independent',
        'literature',
        'lsfID',
        'method_de',
        'method_en',
        'preliminary_work_de',
        'preliminary_work_en',
        'prerequisites_de',
        'prerequisites_en',
        'present',
        'proof_de',
        'proof_en',
        'recommended_prerequisites_de',
        'recommended_prerequisites_en',
        'sws',
        'used_for_de',
        'used_for_en',
        'expertise',
        'method_competence',
        'self_competence',
        'social_competence'
    ].map(
        function(elem)
        {
            return jQuery('#jform_' + elem).parents('.control-group');
        }
    );

    /**
     * Enable/Disable irrelevant fields for preparatory courses
     */
    function prepareForm()
    {

        if (isPrepCourse.val() == 1)
        {
            old_department = departmentID.val();
            departmentID.prop('value', defaultDepartment).trigger("liszt:updated");
            departmentID.prop('disabled', true).trigger("liszt:updated");

            irrelevant_fields.concat(advanced).concat(header).forEach(
                function(elem)
                {
                elem.prop('hidden', true).trigger("liszt:updated");
                }
            );
        }
        else
        {
            departmentID.prop('value', old_department).trigger("liszt:updated");
            departmentID.prop('disabled', false).trigger("liszt:updated");

            irrelevant_fields.concat(advanced).concat(header).forEach(
                function(elem)
                {
                elem.prop('hidden', false).trigger("liszt:updated");
                }
            );
        }

    }

    isPrepCourse.change(prepareForm);
    prepareForm();

});

</script>