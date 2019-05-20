<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the publisher volumes overview.
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
        <img src="<?php print(self::$CachePath . $this->publisher["ImageFileName"]); ?>" style="max-height: 1em;">
        <?php print($this->publisher["Name"]); ?>
    </h1>

    <!-- Using the text-truncate class enables truncating the description. -->
    <!-- The "buttonDescription" button triggers some JavaScript code to add/remove this class -->
    <!-- to make the description collapse/expand. -->
    <div class="text-truncate" id="description">
        <?php
        print($this->publisher["Description"]);
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
            // Insert an album card for every volume in $this->volumes (passed from VolumesController).
            foreach ($this->publisherVolumes as $volume) {
                ?>

                <div class="col-6 col-md-4 col-lg-2 col-xl-2 card-group">
                    <div class="card mb-4 shadow-lg">
                        <a href="/volume/<?php print($volume["VolumeID"]); ?>">
                            <img class="card-img-top" src="<?php print(self::$CachePath . $volume["ImageFileName"]) ?>">
                        </a>
                        <div class="card-body d-flex flex-column">
                            <p class="card-text"><?php print($volume["Name"]); ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <a class="btn btn-primary btn-sm" href="/volume/<?php print($volume["VolumeID"]); ?>">
                                    <i class="fas fa-archive"></i> Issues</a>
                                <small class="text-muted"><?php print($volume["IssueCount"]); ?>
                                    Issue<?php if ($volume["IssueCount"] > 1) print "s"; ?></small>
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