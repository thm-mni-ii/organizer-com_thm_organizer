/* global $, hideFilterText, showFilterText*/

function showPostLoader()
{
	var body = $("body");
	body.append('<div class="loading-background"></div>');
	body.append('<div class="postloader">Loading</div>');
}

Array.prototype.diff = function (oldValues) {
	return this.filter(function (newValues) {
		return newValues.indexOf(oldValues) < 0;
	});
};

function cleanSelection(elementID, container)
{
	var selectBox = jQuery('#' + elementID),
		selectedValues = selectBox.val(),
		allIndex,
		newValues;

	if (selectedValues === null)
	{
		selectedValues = ["-1"];
		window[container] = selectedValues;
		selectBox.val(selectedValues);
		selectBox.trigger("liszt:updated");
		return;
	}

	selectBox.chosen();

	newValues = selectedValues.diff(window[container]);

	// All index is new
	allIndex = newValues.indexOf('-1');
	if (allIndex >= 0)
	{
		selectedValues = ["-1"];
		window[container] = selectedValues;
		selectBox.val(selectedValues);
		selectBox.trigger("liszt:updated");
		return;
	}

	allIndex = selectedValues.indexOf('-1');
	if (allIndex >= 0)
	{
		selectedValues.splice(allIndex, 1);
	}

	window[container] = selectedValues;
	selectBox.val(selectedValues);
	selectBox.trigger("liszt:updated");
}