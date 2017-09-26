/**
 * Created by James Antrim on 11/15/2016.
 */

$(document).ready(function () {
	$('label').tooltip({delay: 200, placement: 'right'});
});

/**
 * Clear the current list and add new pools to it
 *
 * @param   {object}  pools   the pools received
 */
function addPools(pools)
{
	"use strict";

	var poolSelection = $('#poolIDs'), selectedPools = poolSelection.val(), selected;

	poolSelection.children().remove();

	$.each(pools, function (name, id) {
		selected = $.inArray(id, selectedPools) > -1 ? 'selected' : '';
		poolSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
	});

	if (si !== true)
	{
		poolSelection.chosen("destroy");
		poolSelection.chosen();
	}
}

/**
 * Clear the current list and add new programs to it
 *
 * @param   {object}  programs   the programs received
 */
function addPrograms(programs)
{
	"use strict";

	var programSelection = $('#programIDs'), selectedPrograms = programSelection.val(), selected;

	programSelection.children().remove();

	$.each(programs, function (key, value) {
		var name = value.name == null ? value.ppName : value.name;
		selected = $.inArray(value.id, selectedPrograms) > -1 ? 'selected' : '';
		programSelection.append("<option value=\"" + value.id + "\" " + selected + ">" + name + "</option>");
	});

	if (si !== true)
	{
		programSelection.chosen("destroy");
		programSelection.chosen();
	}
}

/**
 * Clear the current list and add new rooms to it
 *
 * @param   {object}  rooms   the rooms received
 */
function addRooms(rooms)
{
	"use strict";

	var roomSelection = $('#roomIDs'), selectedRooms = roomSelection.val(), selected;

	roomSelection.children().remove();

	$.each(rooms, function (name, id) {
		selected = $.inArray(id, selectedRooms) > -1 ? 'selected' : '';
		roomSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
	});

	if (si !== true)
	{
		roomSelection.chosen("destroy");
		roomSelection.chosen();
	}
}

/**
 * Clear the current list and add new teachers to it
 *
 * @param   {object}  teachers   the teachers received
 */
function addTeachers(teachers)
{
	"use strict";

	var teacherSelection = $('#teacherIDs'), selectedTeachers = teacherSelection.val(), selected;

	teacherSelection.children().remove();

	$.each(teachers, function (name, id) {
		selected = $.inArray(id, selectedTeachers) > -1 ? 'selected' : '';
		teacherSelection.append("<option value=\"" + id + "\" " + selected + ">" + name + "</option>");
	});

	if (si !== true)
	{
		teacherSelection.chosen("destroy");
		teacherSelection.chosen();
	}
}

/**
 * Creates a link to a generated ics file
 *
 * @returns {boolean}
 */
function copyLink()
{
	var format, url, myschedule, selectedPools, emptyPools, selectedRooms, emptyRooms, selectedTeachers, emptyTeachers;

	format = $("input[name=format]").val();

	if (format !== 'ics')
	{
		return true;
	}

	url = rootURI + 'index.php?option=com_thm_organizer&view=schedule_export&format=ics';

	myschedule = $('#myschedule:checked').val();

	if (myschedule === 'on' && username !== undefined && auth !== undefined)
	{
		url += '&myschedule=1&username=' + username + '&auth=' + auth;
	}
	else
	{
		selectedPools = $('#poolIDs').val();
		emptyPools = selectedPools == undefined || selectedPools == null || selectedPools.length === 0;

		if (!emptyPools)
		{
			url += '&poolIDs=' + selectedPools;
		}

		selectedRooms = $('#roomIDs').val();
		emptyRooms = selectedRooms == undefined || selectedRooms == null || selectedRooms.length === 0;

		if (!emptyRooms)
		{
			url += '&roomIDs=' + selectedRooms;
		}

		selectedTeachers = $('#teacherIDs').val();
		emptyTeachers = selectedTeachers == undefined || selectedTeachers == null || selectedTeachers.length === 0;

		if (!emptyTeachers)
		{
			url += '&teacherIDs=' + selectedTeachers;
		}
	}

	window.prompt(copyText, url);

	return false;
}

function handleSubmit()
{
	var validSelection = validateSelection(), formatValue = $("input[name=format]").val();

	if (!validSelection)
	{
		return false;
	}

	if (formatValue == 'ics')
	{
		copyLink();
		return true;
	}

	$("#adminForm").submit();

	return true;
}

/**
 * Load pools dependent on the selected departments and programs
 */
function repopulateResources()
{
	"use strict";

	var selectedDepartments = $('#departmentIDs').val(), selectedPrograms = $('#programIDs').val(),
		invalidDepartments, invalidPrograms, allIndex, componentParameters, selectionParameters = '';

	invalidDepartments = selectedDepartments == null || selectedDepartments.length === 0;
	invalidPrograms = selectedPrograms == null || selectedPrograms.length === 0;

	// The all selection was revoked from something.
	if (invalidDepartments && invalidPrograms)
	{
		return;
	}

	componentParameters = 'index.php?option=com_thm_organizer&format=raw&task=getPlanOptions';

	if (!invalidDepartments)
	{
		selectionParameters += '&departmentIDs=' + selectedDepartments;
	}

	if (!invalidPrograms)
	{
		selectionParameters += '&programIDs=' + selectedPrograms;
	}

	$.ajax({
		type: 'GET',
		url: rootURI + componentParameters + selectionParameters + '&view=pool_ajax',
		dataType: 'json',
		success: function (data) {
			addPools(data);
		},
		error: function (xhr, textStatus, errorThrown) {
			if (xhr.status === 404 || xhr.status === 500)
			{
				$.ajax(repopulateResources());
			}
		}
	});

	$.ajax({
		type: 'GET',
		url: rootURI + componentParameters + selectionParameters + '&view=teacher_ajax',
		dataType: 'json',
		success: function (data) {
			addTeachers(data);
		},
		error: function (xhr, textStatus, errorThrown) {
			if (xhr.status === 404 || xhr.status === 500)
			{
				$.ajax(repopulateResources());
			}
		}
	});

	$.ajax({
		type: 'GET',
		url: rootURI + componentParameters + selectionParameters + '&view=room_ajax',
		dataType: 'json',
		success: function (data) {
			addRooms(data);
		},
		error: function (xhr, textStatus, errorThrown) {
			if (xhr.status === 404 || xhr.status === 500)
			{
				$.ajax(repopulateResources());
			}
		}
	});
}

/**
 * Load programs dependent on the selected departments
 */
function repopulatePrograms()
{
	"use strict";

	var componentParameters, selectedDepartments = $('#departmentIDs').val(), allIndex, selectionParameters;
	componentParameters = '/index.php?option=com_thm_organizer&view=program_ajax&format=raw&task=getPlanOptions';

	if (selectedDepartments == null)
	{
		return;
	}

	selectionParameters = '&departmentIDs=' + selectedDepartments;

	$.ajax({
		type: 'GET',
		url: rootURI + componentParameters + selectionParameters,
		dataType: 'json',
		success: function (data) {
			addPrograms(data);
		},
		error: function (xhr, textStatus, errorThrown) {
			if (xhr.status === 404 || xhr.status === 500)
			{
				$.ajax(repopulatePrograms());
			}
		}
	});
}

function setFormat()
{
	var formatValue = $('#format').find(":selected").val(), formatArray = formatValue.split('.'),
		format = formatArray[0], documentFormat = formatArray[1], actionButton = $("#action-btn"),
		linkContainer = $('#link-container'), linkTarget = $('#link-target'), formatInput = $("input[name=format]"),
		displayFormatContainer = $("#displayFormat-container"), dateContainer = $("#date-container"),
		dateRestrictionContainer = $("#dateRestriction-container"), pdfFormatContainer = $("#pdfWeekFormat-container"),
		xlsFormatContainer = $("#xlsWeekFormat-container"), documentFormatContainer = $("input[name=documentFormat]");

	switch (format)
	{
		case 'ics':
			formatInput.val(format);
			actionButton.text(generateText + ' ').append('<span class="icon-feed"></span>');
			displayFormatContainer.hide();
			dateContainer.hide();
			dateRestrictionContainer.hide();
			pdfFormatContainer.hide();
			xlsFormatContainer.hide();
			break;
		case 'xls':
			formatInput.val(format);
			documentFormat = documentFormat === undefined ? 'si' : documentFormat;
			documentFormatContainer.val(documentFormat);
			actionButton.text(downloadText + ' ').append('<span class="icon-file-xls"></span>');
			linkContainer.hide();
			linkTarget.text('');
			displayFormatContainer.hide();
			pdfFormatContainer.hide();
			dateContainer.show();
			dateRestrictionContainer.show();
			xlsFormatContainer.show();
			break;
		case 'pdf':
		default:
			formatInput.val(format);
			documentFormat = documentFormat === undefined ? 'a4' : documentFormat;
			documentFormatContainer.val(documentFormat);
			actionButton.text(downloadText + ' ').append('<span class="icon-file-pdf"></span>');
			linkContainer.hide();
			linkTarget.text('');
			displayFormatContainer.show();
			dateContainer.show();
			dateRestrictionContainer.show();
			pdfFormatContainer.show();
			xlsFormatContainer.hide();
			break;
	}
}

/**
 * Toggles the output of resource and filter fields depenent on the selection of my schedule
 */
function toggleMySchedule()
{
	var myschedule = $('#myschedule:checked').val();

	if (myschedule === 'on')
	{
		$("#filterFields").hide();
		$("#poolIDs-container").hide();
		$("#roomIDs-container").hide();
		$("#teacherIDs-container").hide();
		$("input[name=myschedule]").val(1);
	}
	else
	{
		$("#filterFields").show();
		$("#poolIDs-container").show();
		$("#roomIDs-container").show();
		$("#teacherIDs-container").show();
		$("input[name=myschedule]").val(0);
	}

}

function validateSelection()
{
	var myschedule = $('#myschedule:checked').val(),
		selectedPools = $('#poolIDs').val(), emptyPools,
		selectedRooms = $('#roomIDs').val(), emptyRooms,
		selectedTeachers = $('#teacherIDs').val(), emptyTeachers;

	if (myschedule === 'on')
	{
		return true;
	}

	emptyPools = selectedPools == null || selectedPools.length === 0;
	emptyRooms = selectedRooms == null || selectedRooms.length === 0;
	emptyTeachers = selectedTeachers == null || selectedTeachers.length === 0;

	if (emptyPools && emptyRooms && emptyTeachers)
	{
		alert(selectionWarning);
		return false;
	}

	return true;
}