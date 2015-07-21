var ArcImageGrid = function(id, name, imageWidth, imageHeight, maxColCount){
    this.id = id;
    this.name = name;
    this.imageWidth = imageWidth;
    this.imageHeight = imageHeight;
    this.maxColCount = maxColCount;
    
    this.imageBuffer = [];
    
    ArcImageGrid.grids[id] = this;
    
    this.resize();
};
ArcImageGrid.grids = {};
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
    }while(i--)
};