<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/*
 * Implements the view for the publishers overview.
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

        <div class="row">

            <?php
            // Insert an album card for every publisher in $this->publishers (passed from PublishersController).
            foreach ($this->publishers as $publisher) {
                if ($publisher["VolumesCount"] > 0) {
                    ?>

                    <div class="col-6 col-md-4 col-lg-4 col-xl-2 card-group">
                        <div class="card mb-4 shadow-lg">
                            <a href="/publisher/<?php print($publisher["PublisherID"]); ?>">
                                <img class="card-img-top card-img-top-stretch"
                                     src="<?php print(self::$CachePath . $publisher["ImageFileName"]) ?>"></a>
                            <div class="card-body d-flex flex-column text-center">
                                <p class="card-text"><?php print($publisher["Name"]); ?></p>
                                <a class="btn btn-primary"
                                   href="/publisher/<?php print($publisher["PublisherID"]); ?>">
                                    <i class="fas fa-archive"></i>
                                    <span class="d-none d-lg-inline"> Show Volumes</span>
                                    <span class="d-lg-none"> Volumes</span>
                                </a>
                                <small class="text-muted mt-3"><?php print($publisher["VolumesCount"]); ?>
                                    Volume<?php if ($publisher["VolumesCount"] > 1) print "s"; ?></small>
                            </div>
                        </div>
                    </div>

                <?php }
            } ?>

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