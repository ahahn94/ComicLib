/**
 * Refresh an element of the document via AJAX.
 * @param $elementID ID of the element to refresh.
 * @param $url URL of the document to get the new data from.
 */
function refreshElement($elementID, $url) {
    var xmlhttp = new XMLHttpRequest();

    xmlhttp.responseType = "document";

    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.readyState === XMLHttpRequest.DONE) {
            if (xmlhttp.status === 200) {
                document.getElementById($elementID).innerHTML = xmlhttp.response.getElementById($elementID).innerHTML;
            }
        }
    };

    xmlhttp.open("GET", $url, true);
    xmlhttp.send();
}