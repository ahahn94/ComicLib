<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the "Empty Database" message.
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

    <div class="text-center text-light" style="min-height: 100%; display: flex; align-items: center">
        <div class="container">
            <h1 class="display-3"><i class="fas fa-database fa-sm"></i> <b>Empty Database</b>
            </h1><br>
            <h1 class="display-4">Looks like the database is empty. You can update the database via the button
            on the menu bar.</h1>
        </div>
    </div>

</div>


<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>
</body>
</html>