var ArcImageGridImage = function(url, data){
    this.imageUrl = url;
    this.data = data;
};

var ArcImageGrid = function(id, imageWidth, imageHeight, maxColCount, images){
    this.id = id;
    this.imageWidth = imageWidth;
    this.imageHeight = imageHeight;
    this.maxColCount = maxColCount;
    this.page = 0;
    this.images = images;
    
    ArcImageGrid.grids[id] = this;
    
    this.resize();
};
ArcImageGrid.grids = {};
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
        iDiv.appendChild(cDiv);
        
        div.appendChild(iDiv);
    }while(i--);
    
    this.selectPage(this.page);
};