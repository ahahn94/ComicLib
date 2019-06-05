/*
* A simple swiper to to trigger actions when swiping left or right.
* */
var swiper = {

    startX: 0,
    startY: 0,
    endX: 0,
    endY: 0,
    minSwipeDistance: 30, // Minumum distance to trigger swiping.
    maxSwipeVertical: 50, // Maximum vertical distance to avoid accidental triggering when scrolling.
    swipeLeft: function () {
    },
    swipeRight: function () {
    },

    Directions: Object.freeze({"Left": 0, "Right": 1}),

    init: function (detectionZone) {
        // Touch.
        detectionZone.addEventListener('touchstart', function (event) {
            swiper.startX = event.changedTouches[0].screenX;
            swiper.startY = event.changedTouches[0].screenY;
        }, false);
        detectionZone.addEventListener('touchend', function (event) {
            swiper.endX = event.changedTouches[0].screenX;
            swiper.endY = event.changedTouches[0].screenY;
            swiper.handleSwipeGesture();
        }, false);

        // Mouse.
        detectionZone.addEventListener('mousedown', function (event) {
            swiper.startX = event.clientX;
            swiper.startY = event.clientY;
        });
        detectionZone.addEventListener('mouseup', function (event) {
            swiper.endX = event.clientX;
            swiper.endY = event.clientY;
            swiper.handleSwipeGesture();
        });
    },

    handleSwipeGesture: function () {
        var direction, distanceX, distanceY;
        if (swiper.endX < swiper.startX) {
            distanceX = Math.abs(swiper.startX - swiper.endX);
            distanceY = Math.abs(swiper.startY - swiper.endY);
            direction = swiper.Directions.Left;
        } else if (swiper.endX > swiper.startX) {
            distanceX = Math.abs(swiper.startX - swiper.endX);
            distanceY = Math.abs(swiper.startY - swiper.endY);
            direction = swiper.Directions.Right;
        } else {
            // Start equals end, ignore.
        }
        if (distanceX > swiper.minSwipeDistance && distanceY < swiper.maxSwipeVertical) {
            if (direction === swiper.Directions.Left) {
                swiper.swipeLeft();
            } else if (direction === swiper.Directions.Right){
                swiper.swipeRight();
            }
        }
    },

    setSwipeLeft: function (swipeLeftFunction) {
        swiper.swipeLeft = swipeLeftFunction;
    },

    setSwipeRight: function (swipeRightFunction) {
        swiper.swipeRight = swipeRightFunction;
    },

    setSwipeGeneral: function (swipeGeneralFunction) {
        swiper.swipeLeft = swipeGeneralFunction;
        swiper.swipeRight = swipeGeneralFunction;
    }
};