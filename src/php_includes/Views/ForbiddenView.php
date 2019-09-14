<?php
/**
 * Created by ahahn94
 * on 21.07.19
 */

/*
 * Implements the view for the "403 - Forbidden" error message.
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
            <h1 class="display-3"><i class="fas fa-search fa-sm"></i> <b>Forbidden</b>
            </h1><br>
            <h1 class="display-4"><b>Your priviliges are insufficient to load this page.</b></h1>
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