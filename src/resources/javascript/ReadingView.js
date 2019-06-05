/*
 * Javascript functions for the IssueReadingView.
 */

/*
 * Bind functions to buttons.
 */

// Hide spacer behind navbar.
switchHide(null, "menu-spacer");

// Hide/show navbar, controls, slider and captions on click on the carousel.
document.getElementById("carousel").onclick = function (e) {
    switchHide(e, "menu");
    switchHide(e, "sliderForm");
    switchInvisible(e, "controls"); // Make prev/next buttons invisible but keep them clickable.
    $captions = document.getElementsByName("caption");
    $captions.forEach(function ($item, $index) {
        switchHide(e, $item.id);
    })
};

// Bind keyboard arrow left and right to previous and next. Bind ESC to show/hide controls.
document.addEventListener("keydown", function (e) {
    if (e.code === "ArrowLeft") {
        document.getElementById("prev").click();
    }
    if (e.code === "ArrowRight") {
        document.getElementById("next").click();
    }
    if (e.code === "Escape") {
        document.getElementById("carousel").click();
    }
});

// Select slide via slider. Jump to slide on releasing click on slider.
document.getElementById("pageSlider").onchange = function (e) {
    // Convert slider value to int (from string).
    var pageNumber = parseInt(this.value);
    // Go to pageNumber on the carousel and pause there.
    $('#carousel').carousel(pageNumber).carousel("pause");
};

// Handle swiping.
swiper.init(document.getElementById("swipeZone"));
swiper.setSwipeLeft(function () {
    document.getElementById("next").click();
});
swiper.setSwipeRight(function () {
    document.getElementById("prev").click();
});

/*
 * jQuery stuff.
 * The functions of the Bootstrap Carousel are only documented for jQuery.
 */
$(document).ready(function () {

    /*
     * Update slider and page number.
     */
    $("#myCarousel").on("slide.bs.carousel", function (e) {
        var toSlide = $(e.relatedTarget).index();
        $("#pageSlider").val(toSlide + 1);  // add 1 because the carousel counts from 0 while the slider counts from 1.
        $("#sliderForm").trigger("input");
    })

    /*
     * Lazy loading for images (to avoid loading the whole comic at page load).
     * Load previous and next page too to speed up linear reading.
     */
        .on("slide.bs.carousel", function (e) {
            var activeSlide = $(e.relatedTarget);
            var previousSlide = activeSlide.prev();
            var nextSlide = activeSlide.next();
            var activeImage = activeSlide.find("img[data-src]");
            activeImage.attr("src", activeImage.data("src"));
            var nextImage = nextSlide.find("img[data-src]");
            nextImage.attr("src", nextImage.data("src"));
            var previousImage = previousSlide.find("img[data-src]");
            previousImage.attr("src", previousImage.data("src"));
        })

        /*
         * POST update of the ReadStatus to the server.
         */
        .on("slide.bs.carousel", function (e) {
            // Get current page and total page number.
            var pageNumber = $(e.relatedTarget).data("pageNumber");
            var totalPageNumber = $("#carousel").data("totalPageNumber");
            // If the current page is the last page, set IsRead to true.
            var isRead = pageNumber === totalPageNumber;
            var currentPage = isRead ? 0 : pageNumber;  // Reset current page to 0 if isRead.
            // Build POST form.
            var formData = new FormData();
            formData.append("CurrentPage", currentPage);
            formData.append("IsRead", isRead);

            // Send form.
            var request = new XMLHttpRequest();
            request.open("POST", window.location.href, true);
            request.send(formData);
        });

});