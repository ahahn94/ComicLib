<?php
/**
 * Created by ahahn94
 * on 03.06.19
 */

/*
 * Implements the view for the "Caching issue for reading" message.
 */

?>
<html lang="en">
<header>
    <?php
    /*
     * Include the headers for Bootstrap and FontAwesome.
     */
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapHeader.html";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/FontAwesomeHeader.html";
    ?>

</header>
<body data-view-name="IssueCachingView">

<!-- Full-screen background. -->
<div style='background: url("/resources/images/NotFoundBackground.jpg") no-repeat center center fixed; background-size:
cover; position: fixed; height: 100%; width: 100%;'>

    <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
    ?>

    <div class="text-center text-light" style="min-height: 100%; display: flex; align-items: center" id="message">
        <div class="container">
            <h1 class="display-3"><i class="fas fa-sm fa-sync fa-spin"></i> <b>Loading issue</b>
            </h1><br>
            <h1 class="display-4" style="font-size: 2rem">Adding issue to the cache. Please wait.</h1>
            <?php
            if ($this->isPDF) {
                ?>
                <h1 class="display-4" style="font-size: 2rem">The comic you requested is a PDF. These take especially
                    long.</h1>
                <?php
            }
            ?>
        </div>
    </div>

</div>

<!-- Refresh status message via AJAX. -->
<script src="/resources/javascript/CustomJavaScript.js"></script>
<script type="text/javascript">

    function checkCaching() {
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.responseType = "document";

        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === XMLHttpRequest.DONE) {
                if (xmlhttp.status === 200) {
                    var viewName = xmlhttp.response.body.dataset.viewName;
                    if (viewName !== "IssueCachingView") {
                        // Loaded document contains other view. Refresh page to show new view.
                        location.reload();
                    }
                } else {
                    // Loaded document contains other view. Refresh page to show new view.
                    location.reload();
                }
            }
        };

        xmlhttp.open("GET", window.location.href, true);
        xmlhttp.send();
    }

    // Refresh every 3 seconds.
    var t = setInterval('checkCaching()', 3000);
</script>

<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>
</body>
</html>