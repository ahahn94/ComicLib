<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the volume issues overview.
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
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
?>

<div class="jumbotron">
    <div class="text-center">
    </div>
    <h1 class="display-2 text-center">
        <?php print($this->volume["Name"]); ?>
    </h1>

    <!-- Using the text-truncate class enables truncating the description. -->
    <!-- The "buttonDescription" button triggers some JavaScript code to add/remove this class -->
    <!-- to make the description collapse/expand. -->
    <div class="text-truncate" id="description">
        <?php
        print($this->volume["Description"]);
        ?>
    </div>

    <div class="text-center">
        <button class="btn btn-link" id="buttonDescription">(more)</button>
    </div>
</div>

<div class="album py-5 bg-light">
    <div class="container-fluid">

        <div class="row">

            <?php
            // Insert an album card for every issue in $this->volumeIssues (passed from VolumeIssuesController).
            foreach ($this->volumeIssues as $issue) {
                ?>

                <div class="col-6 col-sm-3 col-md-3 col-lg-2 col-xl-2 card-group">
                    <div class="card mb-4 shadow-sm">
                        <img class="card-img-top" alt=""
                             src="<?php print(self::$CachePath . $issue["ImageFileName"]) ?>">
                        <div class="card-body">
                            <p class="card-text"><?php print($issue["Name"]); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                            </div>
                        </div>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>
</div>

<!-- JavaScript for truncating the description. -->
<script src="/resources/javascript/CustomJavaScript.js"></script>
<script type="text/javascript">
    // Bind functions to buttons.
    document.getElementById("buttonDescription").onclick = function (e) {
        switchTruncation(e, "description");
    }
</script>

<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>
</body>
</html>