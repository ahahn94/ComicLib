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
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
?>

<div class="album py-5 bg-light">
    <div class="container-fluid">

        <?php
        if ($this->previousPage !== $this->nextPage) {
            // Show pages buttons if either a next page, a previous page or both exist.
            ?>

            <nav aria-label="Page navigation" class="text-center">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item  <?php if ($this->previousPage === "") print ("disabled"); ?>"><a
                                class="page-link" href="/volumes/<?php print ($this->previousPage); ?>">Previous
                        </a>
                    </li>

                    <?php
                    for ($i = 1; $i <= $this->pageCount; $i++) {
                        ?>

                        <li class="page-item <?php if ($i === $this->activePage) print ("active"); ?>"><a
                                    class="page-link" href="/volumes/<?php print ($i); ?>">
                                <?php print ($i); ?></a></li>
                        <?php
                    } ?>

                    <li class="page-item  <?php if ($this->nextPage === "") print ("disabled"); ?>"><a
                                class="page-link" href="/volumes/<?php print ($this->nextPage); ?>">Next
                        </a>
                    </li>
                </ul>

            </nav>
            <?php
        }
        ?>

        <div class="row">

            <?php
            // Insert an album card for every volume in $this->volumes (passed from VolumesController).
            foreach ($this->volumes as $volume) {
                if ($volume["IssueCount"] != 0) {
                    ?>

                    <div class="col-6 col-md-4 col-lg-4 col-xl-2 card-group">
                        <div class="card mb-4 shadow-lg">
                            <div>
                                <a href="/volume/<?php print($volume["VolumeID"]); ?>">
                                    <img class="card-img-top" alt=""
                                         src="<?php print(self::$CachePath . $volume["ImageFileName"]) ?>">
                                </a>
                                <?php
                                if ($volume["IsRead"] === "0") {
                                    ?>
                                    <div class="readStatusBadge bg-success text-light">New</div>
                                    <?php
                                }
                                ?>

                            </div>
                            <div class="card-body d-flex flex-column text-center">
                                <p class="card-text"><?php print($volume["Name"]); ?></p>

                                <form method="post">
                                    <input hidden name="VolumeID" value="<?php print($volume["VolumeID"]); ?>">
                                    <input hidden name="ReadStatus" value="<?php
                                    print (($volume["IsRead"] === "0") ? "true" : "false"); ?>">

                                    <div class="btn-group" role="group">

                                        <a href="/volume/<?php print($volume["VolumeID"]); ?>" class="btn btn-primary">
                                            <i class="fas fa-archive"></i>
                                            <span class="d-none d-lg-inline"> Show Issues</span>
                                            <span class="d-lg-none"> Issues</span>
                                        </a>

                                        <button id="btnGroupDrop1" type="button"
                                                class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right"
                                             aria-labelledby="btnGroupDrop1">

                                            <a class="dropdown-item"
                                               href="/publisher/<?php print ($volume["PublisherID"]); ?>">
                                                <i class="fas fa-building fa-fw"></i> Go to Publisher
                                            </a>

                                            <button class="dropdown-item" type="submit">
                                                <?php
                                                if ($volume["IsRead"] === "0") {
                                                    // Not yet read.
                                                    ?>
                                                    <i class="fas fa-eye fa-fw"></i> Mark as read
                                                    <?php
                                                } else {
                                                    // Already read. Grey out.
                                                    ?>
                                                    <i class="fas fa-eye fa-fw"></i> Mark as not read
                                                    <?php
                                                }
                                                ?>
                                            </button>
                                        </div>
                                    </div>

                                </form>
                                <small class="text-muted"><?php print($volume["IssueCount"]); ?>
                                    Issue<?php if ($volume["IssueCount"] > 1) print "s"; ?></small>
                            </div>
                        </div>
                    </div>

                <?php }
            } ?>

        </div>

        <?php
        if ($this->previousPage !== $this->nextPage) {
            // Show pages buttons if either a next page, a previous page or both exist.
            ?>

            <nav aria-label="Page navigation" class="text-center">
                <ul class="pagination justify-content-center flex-wrap">
                    <li class="page-item  <?php if ($this->previousPage === "") print ("disabled"); ?>"><a
                                class="page-link" href="/volumes/<?php print ($this->previousPage); ?>">Previous
                        </a>
                    </li>

                    <?php
                    for ($i = 1; $i <= $this->pageCount; $i++) {
                        ?>

                        <li class="page-item <?php if ($i === $this->activePage) print ("active"); ?>"><a
                                    class="page-link" href="/volumes/<?php print ($i); ?>">
                                <?php print ($i); ?></a></li>
                        <?php
                    } ?>

                    <li class="page-item  <?php if ($this->nextPage === "") print ("disabled"); ?>"><a
                                class="page-link" href="/volumes/<?php print ($this->nextPage); ?>">Next
                        </a>
                    </li>
                </ul>

            </nav>
            <?php
        }
        ?>

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