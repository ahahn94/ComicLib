<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the volumes overview.
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

<div class="album py-5 bg-light">
    <div class="container-fluid">

        <div class="row">

            <?php
            // Insert an album card for every volume in $this->volumes (passed from VolumesController).
            foreach ($this->volumes as $volume) {
                ?>

                <div class="col-6 col-md-4 col-lg-2 col-xl-2 card-group">
                    <div class="card mb-4 shadow-lg">
                        <a href="/volume/<?php print($volume["VolumeID"]); ?>">
                            <img class="card-img-top" alt="" src="<?php print(self::$CachePath . $volume["ImageFileName"]) ?>">
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


<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>
</body>
</html>