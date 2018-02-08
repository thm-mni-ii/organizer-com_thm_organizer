function listAction(state)
{
    if (listItemChecked())
    {
        document.getElementById('participantState').value = state;
        return true;
    }

    event.preventDefault();
    document.getElementById('participantState').value = '';
    alert(chooseParticipants);
    return false;
}

function listItemChecked()
{
    let checkboxes = document.getElementsByName('checked[]'), checked = false;

    for (let index in checkboxes)
    {
        checked = checkboxes[index].checked;
        if (checked === true)
        {
            return true;
        }
    }

    return false;
}

function toggleAll(box)
{
    let checkboxes = document.getElementsByName('checked[]');

    for (let index in checkboxes)
    {
        checkboxes[index].checked = box.checked;
    }
}

function toggleToggle(box)
{
    let toggleBox = document.getElementById('toggleSelect'),
        checkboxes = document.getElementsByName('checked[]');

    // Deselecting one means deselects all box
    if (box.checked !== true)
    {
        toggleBox.checked = undefined;
        return true;
    }

    // If one box is not checked take no action
    for (let index in checkboxes)
    {
        if (!checkboxes.hasOwnProperty(index))
        {
            continue;
        }

        if (checkboxes[index].checked !== true)
        {
            return true;
        }
    }

    // All boxes are now checked => check all box
    toggleBox.checked = true;
    return true;
}