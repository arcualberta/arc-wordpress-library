# ARC WordPress Library
## PHP Classes
### ARCPostCell  
This is used on the query functions to represent a post/page and its containing metadata.  
  
```
class ARCPostCell {
    public $id = 0;
    public $name = 'MISSINGNO.';
    public $post_type = 'post';
    public $url = '#';
    public $metadata = array();
    
    function get_post(){
        $post = get_post($this->id);
        
        return $post;
    }
}
```

## Javascript Classes
### ARCImage 
This is used as a parallel object to the PHP object ARCPostCell which provides easy access to the image metadata value.  
   
```  
var ArcImage = function (data) {
    this.imageUrl = data.metadata['_arc_image_grid_img'];
    this.data = data;
};
```  
  
### ARCImageGrid
A javascript object used to create and display and Image Grid on the page.  
  
```  
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
    this.timeout = timer > 0 ? timer * 1000 : 0;
    this.timer = null;
};
```  
  
## Helper Functions
### arc_convert_content($content, $data)
This function can be used to convert the $content string to include the content provided by the $data object. You can define content in the exact same way you would for a php double quotation string by referencing the $data object.  
Parameters:  
*$content*          The string to replace values in.  
*$data*             The object used to derive values for the given string.  
  
Example:  
```
$input = array("name" => "ARC Tools", "metadata" => array("url" => "https://github.com/arcualberta/arc-wordpress-library"));  
$result = arc_convert_content('Hi {$data->name}! Welcome to {$data->metadata["url"]}.', $input);
```
This will create the string: Hi ARC Tools! Welcome to https://github.com/arcualberta/arc-wordpress-library.  
  
### arc_image_grid_get_entries($name, $objectOutputFunction, $random = false, $limit = 100)
Obtains pages/posts whose Image Grid Name matches the value given by $name.  
  
*$name*                 The name of the image grid to use.  
*$objectOutputFunction* The function will pass the resulting objects from the query(one at a time) to the function to be handled.  
*$random*               If true then the results are extracted from the database randomly; otherwise, the results are given in ascending order from when they were published.  
*$limit*                The maximum number of results we wish to extract.  
  
Example:  
```
$test_array = array();

function test_add_result($current_object){
    global $test_array;

    array_push($test_array, $current_object);
}

arc_image_grid_get_entries('TEST', 'test_add_result', true, 100);
```  
 
### arc_get_posts_by_category($category, $objectOutputFunction, $random = false, $limit = 100)
Obtains posts from a given category.  
  
*$category*             The id of the category. Usually this is the name of the category in lowercase.  
*$objectOutputFunction* The function will pass the resulting objects from the query(one at a time) to the function to be handled.  
*$random*               If true then the results are extracted from the database randomly; otherwise, the results are given in ascending order from when they were published.  
*$limit*                The maximum number of results we wish to extract.  
  
Example:  
```
$test_array = array();

function test_add_result($current_object){
    global $test_array;

    array_push($test_array, $current_object);
}

arc_get_posts_by_category('event', 'test_add_result', true, 100);
```  

### arc_limit_content($data, $contentPath, $contentLimit, $breakChar = ".", $padding = "...")
The an input string and limits the resulting content. If the string is HTML content it will strip off all of the tag before converting.  
*$data*             The object containing the string the be used. Usually this is a post object.  
*$contentPath*      The path to the string being converted.  
*$breakChar*        The character to break the string at. By default this is a period in order to keep entire sentences.  
*$padding*          The string to use when then content is too long.  
  
Example:  
```
$input = array("name" => 'Test', "content" => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque tincidunt sem non tempus sodales. Donec ornare molestie urna, eu efficitur nulla. Nulla suscipit, dolor a pharetra rutrum, metus mi porta elit, vitae gravida velit nunc id nisl. Mauris pellentesque velit imperdiet convallis facilisis. Maecenas dictum velit sit amet quam eleifend laoreet non vitae nunc. Fusce vulputate quam maximus mauris bibendum, eu porta diam commodo. Suspendisse gravida iaculis velit a elementum. Mauris ex dolor, suscipit nec convallis non, suscipit accumsan orci. Maecenas varius augue risus, sit amet convallis erat mattis sit amet. Quisque dignissim lectus eget lectus faucibus finibus.');
$val = arc_limit_content($input, '$data->content', 100)
```
Result:  
```
$val = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque tincidunt sem non tempus sodales. Donec ornare molestie urna, eu efficitur nulla...'
```
  
## ARC Image Grid
The ARC Image Grid is a wordpress plugin which allows you to display a grid of images linking to posts and pages.

### Installation
1. Download the code from this repository.
2. Place the entire arc-image-grid folder into your plugins directory(Usually <wordpress directory>/wp-content/plugins).
3. In the wordpress administration page find the entry "ARC Image Grid"
4. Click on activate.

### How to use
#### Adding to PHP
To add the image grid to a php page, simply call the function `arc_image_grid_add_grid($name, $img_width, $img_height, $max_col_count, $content, $button_text, $random, $show_arrows, $timer_seconds, $limit)` using the following paramaters:  
*$name*             The name of the grid to use. This name will be defined on a post or page entry.  
*$img_width*        The width of each image(in pixels) in the grid.  
*$img_height*       The height of each image(in pixels) in the grid.  
*$max_col_count*    The maximum number of columns that are displayed on this grid.  
*$content*          HTML content that can appear behind the image on the grid.  
*$button_text*      The text to appear on the button which will navigate to the page.  
*$random*           A boolean value to state whether or not the results should be random or most recent.  
*$show_arrows*      A boolean value stating whether or not to show the navigation arrows.  
*$timer_seconds*    The timer, in seconds, to turn the page. If a value of 0 is given then no timer will be started.  
*$limit*            The total number of results to be extracted from the database.  
  
Example
```
arc_image_grid_add_grid('ALBUM', 200, 200, 4, '<p style="color: white;">{name}</p>', 'See Details', true, true, 20, 100);
```

### Adding to a Post or Page
To add an image grid to a post or page call the shortcode function `arc_add_image_grid` with the backside content specified in-line and the following parameters:  
*name*              The name of the grid to use. This name will be defined on a post or page entry.  
*img_width*         The width of each image(in pixels) in the grid.  
*img_height*        The height of each image(in pixels) in the grid.  
*max_col_count*     The maximum number of columns that are displayed on this grid.  
*button_text*       The text to appear on the button which will navigate to the page.  
*random*            A boolean value to state whether or not the results should be random or most recent.  
*show_arrows*       A boolean value stating whether or not to show the navigation arrows.  
  
Example
```
[arc_add_image_grid name="TEST" img_width=215 img_height=215 max_col_count=2 buton_text="Read More" random=false show_arrows=true]
<p style="color: white;">Grid Name: {metadata['_arc_image_grid_name']}</p>
[/arc_add_image_grid]
```