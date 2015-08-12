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

/** Image Grid Models **/

var ArcImageGridImage = function (data) {
    this.imageUrl = data.metadata['_arc_image_grid_img'];
    this.data = data;
};
var ArcImageGrid = function (id, imageWidth, imageHeight, maxColCount, images, content, buttonText, timer) {
    this.id = id;
    this.imageWidth = imageWidth;
    this.imageHeight = imageHeight;
    this.maxColCount = maxColCount;
    this.pagesPerGrid = maxColCount * maxColCount;
    this.page = 0;
    this.images = images;
    this.content = content;
    this.buttonText = buttonText;
    var _this = this;
    this.timeout = timer > 0 ? timer * 1000 : 0;
    this.timer = null;
    

    ArcImageGrid.grids[id] = this;

    var button = document.getElementById(this.id + "_left");
    button.onclick = function () {
        var _this = ArcImageGrid.grids[id];
        _this.turnPage(-_this.pagesPerGrid);
    };

    button = document.getElementById(this.id + "_right");
    button.onclick = function () {
        var _this = ArcImageGrid.grids[id];
        _this.turnPage(_this.pagesPerGrid);
    };
    
    window.addEventListener('resize', function(){
        _this.resize();
    }, true);

    this.resize();
};
ArcImageGrid.grids = {};
ArcImageGrid.prototype.getContent = function (imageId) {
    var match;
    var result;
    var content = this.content;
    var reg = /{([^}]+)}/g;

    while (match = reg.exec(content)) {
        result = match[1].replace(/&#8220;|&#8221;|&#8216;|&#8217;/gi, '"');
        result = eval("this.images[" + imageId + "].data." + result);

        content = content.replace(match[0], result);
    }

    result = "<div class='content-main'>" + content + "</div><a class='content-button' href='" + this.images[imageId].data.url + "'>" + this.buttonText + "</a>";
    return result;
};
ArcImageGrid.prototype.turnPage = function (pageAmount) { // Negative for left and positive for right
    var changeAmount = this.page + pageAmount;
    
    while(changeAmount < 0){
        changeAmount += this.pagesPerGrid;
    };
    
    if(changeAmount >= this.images.length){
        changeAmount = changeAmount % this.images.length;
    };
    
    this.selectPage(changeAmount);
};
ArcImageGrid.prototype.selectPage = function (page) {
    this.page = page;

    var div = document.getElementById(this.id);
    var children = div.childNodes;
    var i = div.childElementCount - 1;
    var j = 2;
    var image = null;
    var imageIndex = 0;

    for (i = 0; i < div.childElementCount; ++i) {
        var fDiv = children[i];
        imageIndex = (page + i) % this.images.length;
        image = this.images[imageIndex];

        if (image && image !== null) {
            fDiv.style.visibility = 'visible';
            j = 1;
            do {
                
                var cDiv = fDiv.childNodes[j];

                cDiv.style.backgroundImage = 'url("' + image.imageUrl + '")';

                if (cDiv.classList.contains("back")) {
                    var contentDiv = cDiv.childNodes[0];
                    contentDiv.innerHTML = this.getContent(imageIndex);
                }
            } while (j--);
        } else {
            fDiv.style.visibility = 'hidden';
        }
    }
};
ArcImageGrid.prototype.resize = function () {
    var container = document.getElementById(this.id + "_container");
    var div = document.getElementById(this.id);
    var width = (this.imageWidth * this.maxColCount);
    var _this = this;

    div.style.width = width + "px";
    var itemsX = this.maxColCount;
    var currentWidth = div.offsetWidth;
    
    if(currentWidth < width){
        itemsX = ~~(currentWidth / this.imageWidth); // Quick floor
        
        if(itemsX < 1){
            itemsX = 1;
        }
        width = itemsX * this.imageWidth;
        div.style.width = width + "px";
    }
    div.innerHTML = "";
    this.pagesPerGrid = itemsX * itemsX;
    
    if(this.timer !== null){
        clearInterval(this.timer);
    }
    
    var i = this.pagesPerGrid - 1;

    do {
        var iDiv = document.createElement('div');
        iDiv.className = 'flip';
        iDiv.style.width = this.imageWidth + "px";
        iDiv.style.height = this.imageHeight + "px";

        var cDiv = document.createElement('div');
        cDiv.className = 'front';
        iDiv.appendChild(cDiv);

        cDiv = document.createElement('div');
        cDiv.className = 'back';

        var contentDiv = document.createElement('div');
        contentDiv.className = 'content';
        cDiv.appendChild(contentDiv);

        iDiv.appendChild(cDiv);

        div.appendChild(iDiv);
    } while (i--);
    
    if(this.timeout > 0){
        this.timer = setInterval(function(){
            _this.turnPage(_this.pagesPerGrid);
        }, this.timeout);
    }
    
    this.selectPage(this.page);
};