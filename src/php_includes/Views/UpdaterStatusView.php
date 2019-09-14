<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the "Updating database and cache" message.
 */

?>
<html lang="en">

<head>
    <title>ComicLib</title>
    <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/PWAHead.html"
    ?>
</head>

<header>
    <?php
    /*
     * Include the headers for Bootstrap and FontAwesome.
     */
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapHeader.html";
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/FontAwesomeHeader.html";
    ?>

</header>
<body>

<!-- Full-screen background. -->
<div style='background: url("/resources/images/Background.jpg") no-repeat center center fixed; background-size:
cover; position: fixed; height: 100%; width: 100%;'>

    <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
    ?>

    <div class="text-center text-light" style="min-height: 100%; display: flex; align-items: center" id="message">
        <div class="container">

            <?php
            if ($this->updaterRunning) {
                ?>
                <h1 class="display-3"><i class="fas fa-sm fa-sync fa-spin"></i> <b>Update
                        running</b>
                </h1><br>
                <h1 class="display-4" style="font-size: 2rem">Running updates on database and image cache.</h1>
                <?php
            } else {
                ?>
                <h1 class="display-3"><i class="fas fa-sm fa-check"></i> <b>Updates Done</b>
                </h1><br>
                <h1 class="display-4" style="font-size: 2rem">No updates running at the moment.</h1>
                <?php
            }
            ?>
        </div>
    </div>

</div>

<!-- Refresh status message via AJAX. -->
<script src="/resources/javascript/CustomJavaScript.js"></script>
<script type="text/javascript">
    // Refresh every 3 seconds.
    var t = setInterval('refreshElement("message", document.URL)', 3000);
</script>

<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>
</body>
</html>