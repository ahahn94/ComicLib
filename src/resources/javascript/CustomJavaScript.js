/**
 * Switch the text-truncate class of Bootstrap on an element on and off.
 * Can be used to expand and collapse sections with text.
 * @param e Event triggering the function.
 * @param $id ID of the element to switch text-truncate on.
 */
function switchTruncation(e, $id) {
    $button = e.target;
    $className = "text-truncate";
    $description = document.getElementById($id);
    if ($description.classList.contains($className)) {
        // Description is truncated. Remove truncation class.
        $description.classList.remove($className);
        $button.innerHTML = "(less)";
    } else {
        // Description is in long form. Add truncation class.
        $description.classList.add($className);
        $button.innerHTML = "(more)";
    }
}

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