/**
 * Toggles the visibility of error details
 */
function toggle(detailID)
{
    var details = document.getElementByID(detailID);
    var visibility = details.style.display;
    if(visibility == 'block'){details.style.display = 'none';}
    else details.style.display = 'block';
}