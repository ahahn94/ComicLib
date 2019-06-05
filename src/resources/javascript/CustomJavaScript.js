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

/**
 * Switch the d-none class of Bootstrap on an element on and off.
 * Can be used to hide elements.
 * @param e Event triggering the function.
 * @param $id ID of the element to switch d-none on.
 */
function switchHide(e, $id) {
    $className = "d-none";
    switchClass($id, $className);
}

/**
 * Switch the custom invisibility class on an element on and off.
 * Can be used to make elements invisible while keeping them clickable.
 * @param e Event triggering the function.
 * @param $id ID of the element to switch invisibility on.
 */
function switchInvisible(e, $id) {
    $className = "invisibility";
    switchClass($id, $className);
}

/**
 * Add or remove a css class on an element.
 * @param $id ID of the element to switch the class on.
 * @param $className Class to add/remove.
 */
function switchClass($id, $className) {
    $element = document.getElementById($id);
    if ($element.classList.contains($className)) {
        // Class is set. Remove class.
        $element.classList.remove($className);
    } else {
        // Class is not set. Add class.
        $element.classList.add($className);
    }
}