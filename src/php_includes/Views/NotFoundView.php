<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the "404 - Not Found" error message.
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
<body>

<!-- Full-screen background. -->
<div style='background: url("/resources/images/NotFoundBackground.jpg") no-repeat center center fixed; background-size:
cover; position: fixed; height: 100%; width: 100%;'>

    <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
    ?>

    <div class="text-center text-light" style="min-height: 100%; display: flex; align-items: center">
        <div class="container">
            <h1 class="display-3"><i class="fas fa-search fa-sm"></i> <b>Page not found</b>
            </h1><br>
            <h1 class="display-4">Maybe the page you are looking for is <b>somewhere</b> out
                there.</h1>
            <h1 class="display-4"><b>But it certainly is not here.</b></h1>
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