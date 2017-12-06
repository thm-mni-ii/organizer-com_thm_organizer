jQuery(document).ready(function () {

	const isPrepCourse = jQuery('#jform_is_prep_course'),
		mappingsTab = jQuery("[href*='#mappings']").parent(),

		// <hr> Containers
		hrs = jQuery(".field-spacer");

	let advanced, relevant_fields, irrelevant_fields;

	// Input containers for field elements which have no relevance for courses
	relevant_fields = [
		'campusID'
	].map(
		function (elem) {
			return jQuery('#jform_' + elem).parents('.control-group');
		}
	);

	// Input containers for field elements which have no relevance for courses
	irrelevant_fields = [
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
		function (elem) {
			return jQuery('#jform_' + elem).parents('.control-group');
		}
	);

	// Irrelevant input containers with a different ID structure
	advanced = ['prerequisites', 'postrequisites'].map(
		function (elem) {
			return jQuery('#jform' + elem).parents('.control-group');
		}
	);

	/**
	 * Enable/Disable irrelevant fields for preparatory courses
	 */
	function prepareForm()
	{
		if (isPrepCourse.val() == 1)
		{
			relevant_fields.forEach(
				function (elem) {
					elem.prop('hidden', false).trigger("liszt:updated");
				}
			);
			irrelevant_fields
				.concat(advanced)
				.concat(mappingsTab)
				.concat(hrs)
				.forEach(
				function (elem) {
					elem.prop('hidden', true).trigger("liszt:updated");
				}
			);
		}
		else
		{
			relevant_fields.forEach(
				function (elem) {
					elem.prop('hidden', true).trigger("liszt:updated");
				}
			);
			irrelevant_fields.concat(advanced).concat(mappingsTab).forEach(
				function (elem) {
					elem.prop('hidden', false).trigger("liszt:updated");
				}
			);
		}

	}

	isPrepCourse.change(prepareForm);
	prepareForm();

})
;