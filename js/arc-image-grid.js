var ArcImageGridImage = function(data){
    this.imageUrl = data.metadata['_arc_image_grid_img'];
    this.data = data;
};

var ArcImageGrid = function(id, imageWidth, imageHeight, maxColCount, images, content){
    this.id = id;
    this.imageWidth = imageWidth;
    this.imageHeight = imageHeight;
    this.maxColCount = maxColCount;
    this.page = 0;
    this.images = images;
    this.content = content;
    
    ArcImageGrid.grids[id] = this;
    
    var button = document.getElementById(this.id + "_left");
    button.onclick = function() { turnPage(-1); };
    
    button = document.getElementById(this.id + "_right");
    button.onclick = function() { turnPage(1); };
    
    this.resize();
};
ArcImageGrid.grids = {};
ArcImageGrid.prototype.getContent = function(imageId){
    var match;
    var result;
    var content = this.content;
    var reg = /{([^}]+)}/g;
    
    while(match = reg.exec(content)){
        result = match[1].replace(/&#8220;|&#8221;|&#8216;|&#8217;/gi, '"');
        result = eval("this.images[" + imageId + "].data." + result);
        
        content = content.replace(match[0], result);
    }
    
    return content;
};
ArcImageGrid.prototype.turnPage = function(pageAmount){ // Negative for left and positive for right
    this.selectPage(this.page + pageAmount);
};
ArcImageGrid.prototype.selectPage = function(page){
    this.page = page;
    
    var div = document.getElementById(this.id);
    var children = div.childNodes;
    var i = div.childElementCount - 1;
    var j = 2;
    var image = null;
    
    for(i = 0; i < div.childElementCount; ++i){
        var fDiv = children[i];
        
        j = 1;
        do{
            var cDiv = fDiv.childNodes[j];
            image = this.images[page + i];
            cDiv.style.backgroundImage = 'url("' + image.imageUrl + '")';
            
            if(cDiv.classList.contains("back")){
                var contentDiv = cDiv.childNodes[0];
                contentDiv.innerHTML = this.getContent(page + i);
            }
        } while(j--);
    };
};
ArcImageGrid.prototype.resize = function(){
    var div = document.getElementById(this.id);
    
    div.style.width = (this.imageWidth * this.maxColCount) + "px";
    
    div.innerHTML = "";
    
    var itemsX = this.maxColCount;
    var i = (itemsX * itemsX) - 1;
    
    do{        
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
    }while(i--);
    
    this.selectPage(this.page);
};