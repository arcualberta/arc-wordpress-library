/** Useful arc graphics functions **/

window.requestAnimFrame = (function () {
    return  window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            function (callback) {
                window.setTimeout(callback, 1000 / 30);
            };
})();

var arcCheckDocumentReady = function (inputFunction) {
    var testReady = function () {
        if (document.readyState !== 'complete') {
            requestAnimFrame(testReady);
        } else {
            inputFunction();
        }
    };

    testReady();
};

/* Useful Models */
var ArcImage = function (data) {
    this.imageUrl = data.metadata._arc_image_grid_img;
    this.data = data;
};
