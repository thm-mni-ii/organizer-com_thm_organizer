window.onload = function () {

    const blockID = document.getElementById("jform_blockID"),
        eventID = document.getElementById("jform_eventID");

    oldObj = {};
    saveAsCopyBtn = document.getElementsByClassName("btn btn-small button-save-copy");

    saveAsCopyBtn[0].disabled = true;
    oldObj.blockID = blockID.options[blockID.selectedIndex].value;
    oldObj.eventID = eventID.options[eventID.selectedIndex].value;
}

function disableBtns() {
    const blockID = document.getElementById("jform_blockID"),
        eventID = document.getElementById("jform_eventID"),
        newObj = {},
        saveBtn = document.getElementsByClassName("btn btn-small button-save");

    newObj.blockID = blockID.options[blockID.selectedIndex].value;
    newObj.eventID = eventID.options[eventID.selectedIndex].value;

    if (oldObj.eventID !== newObj.eventID || oldObj.blockID !== newObj.blockID) {
        saveBtn[0].disabled = true;
        saveAsCopyBtn[0].disabled = false;
    } else if (oldObj.eventID === newObj.eventID && oldObj.blockID === newObj.blockID) {
        saveBtn[0].disabled = false;
        saveAsCopyBtn[0].disabled = true;
    }
}