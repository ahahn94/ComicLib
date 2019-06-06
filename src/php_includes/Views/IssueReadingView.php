<?php
/**
 * Created by ahahn94
 * on 30.05.19
 */

/*
 * Implements the view for reading comic issues.
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
<body data-view-name="IssueReadingView">
<div id="menu">
    <?php
    require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/Menu.html";
    ?>
</div>

<div id="myCarousel" class="carousel slide mx-auto h-auto" data-ride="carousel" data-interval="false" data-wrap="false">

    <form id="sliderForm" oninput="pageSliderSelection.value= 'Page ' + (parseInt(pageSlider.value)) +
            '/<?php print (sizeof($this->images)); ?>'">
        <input type="range" min="1" max="<?php $size = print (sizeof($this->images)); ?>" step="1"
               value="<?php print($this->currentPage); ?>" class="slider" id="pageSlider" name="pageSlider"
        title="Go to page">
        <div class="carousel-caption">
            <strong class="bg-dark bootstrap-transparency">
                <output name="pageSliderSelection"><?php print ("Page " . ($this->currentPage) . "/" . sizeof($this->images)); ?></output>
            </strong>
        </div>
    </form>

    <div id="swipeZone">
        <div class="carousel-inner" id="carousel" data-total-page-number="<?php print(sizeof($this->images)); ?>">
            <?php
            $i = 1;
            foreach ($this->images as $image) {
                if ($i == $this->currentPage) {
                    ?>
                    <div class="carousel-item text-center active" data-page-number="<?php print ($i); ?>">
                        <img src="<?php print($image); ?>" class="comic-page" alt="">
                    </div>
                    <?php
                } else {
                    ?>
                    <div class="carousel-item text-center" data-page-number="<?php print ($i); ?>">
                        <img data-src="<?php print($image); ?>" class="comic-page" alt="">
                    </div>
                    <?php
                }
                $i++;
            }
            ?>
        </div>
    </div>

    <div id="controls">
        <button class="btn btn-secondary bootstrap-transparency fullscreen-button" id="toggleFullscreenButton"
                title="Toggle fullscreen">
            <i class="fas fa-expand fa-lg"></i>
        </button>

        <a class="carousel-control-prev bg-dark bootstrap-transparency" href="#myCarousel" role="button" id="prev"
           data-slide="prev" title="Previous page">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next bg-dark bootstrap-transparency" href="#myCarousel" role="button" id="next"
           data-slide="next" title="Next page">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
</div>

<?php
/*
 * Include the JavaScript libraries for Bootstrap.
 */
require_once $_SERVER["DOCUMENT_ROOT"] . "/resources/html/BootstrapBody.html";
?>

<!-- Javascript for hiding the menu and controls. -->
<script src="/resources/javascript/CustomJavaScript.js"></script>
<!-- Javascript for swiping. -->
<script src="/resources/javascript/Swiping.js"></script>
<!-- Javascript for carousel control, updating ReadStatus, etc. -->
<script src="/resources/javascript/ReadingView.js"></script>

</body>
</html>