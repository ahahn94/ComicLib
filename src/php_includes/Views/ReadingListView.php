<?php
/**
 * Created by ahahn94
 * on 04.06.19
 */

/*
 * Implements the view for the reading list overview.
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

    require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ComicCache.php";
    ?>
</header>
<body>
<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
?>

<div class="jumbotron">
    <h1 class="display-2 text-center">
        <?php print("Your Reading List"); ?>
    </h1>

    <div class="text-center">
        <h1 class="display-4">
            <?php
            if (sizeof($this->issues) !== 0) {
                print ("These are the issues that you are currently reading.");
            } else {
                print ("There are no books on your reading list. Have a look at the Volumes or Publishers sections for"
                    ." comics to read.");
            }
            ?>
        </h1>
    </div>
</div>

<div class="album py-5 bg-light">
    <div class="container-fluid">

        <div class="row">

            <?php
            // Insert an album card for every issue in $this->volumeIssues (passed from VolumeIssuesController).
            foreach ($this->issues as $issue) {
                ?>

                <div class="col-6 col-md-4 col-lg-4 col-xl-2 card-group">
                    <div class="card mb-4 shadow-sm">
                        <img class="card-img-top" alt=""
                             src="<?php print(self::$CachePath . $issue["ImageFileName"]) ?>">

                        <?php
                        if ($issue["IsRead"] === "0") {
                            ?>
                            <div class="readStatusBadge bg-success text-light">New</div>
                            <?php
                        }
                        ?>

                        <div class="card-body d-flex flex-column text-center">
                            <p class="card-text"><?php print($issue["Name"]); ?></p>

                            <form method="post">
                                <!-- Form to update the ReadStatus of the issue. -->
                                <input hidden name="IssueID"
                                       value="<?php print($issue["IssueID"]); ?>">
                                <input hidden name="ReadStatus" value="<?php
                                print (($issue["IsRead"] === "0") ? "true" : "false");
                                ?>">

                                <div class="btn-group" role="group">

                                    <a href="/issue/<?php print($issue["IssueID"]); ?>"
                                       class="btn btn-primary<?php
                                       if (!ComicCache::isReadable($issue["IssueLocalPath"])) {
                                           print "bootstrap-transparency";
                                       } ?> btn-sm"><i class="fas fa-book-open"></i> Read this issue
                                    </a>

                                    <button id="btnGroupDrop1" type="button"
                                            class="btn btn-primary dropdown-toggle" data-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right"
                                         aria-labelledby="btnGroupDrop1">

                                        <a class="dropdown-item"
                                           href="/download/<?php print($issue["IssueID"]); ?>">
                                            <i class="fas fa-download"></i> Download
                                        </a>

                                        <button class="dropdown-item" type="submit"><?php
                                            if ($issue["IsRead"] === "0") {
                                                // Not yet read.
                                                ?><i class="fas fa-eye"></i> Mark as read<?php
                                            } else {
                                                // Already read. Grey out.
                                                ?><i class="fas fa-eye"> Mark as not read</i><?php
                                            }
                                            ?></button>

                                    </div>
                                </div>


                            </form>
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