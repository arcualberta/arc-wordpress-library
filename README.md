# ARC Image Grid
The ARC Image Grid is a wordpress plugin which allows you to display a grid of images linking to posts and pages.

# Installation
1. Download the code from this repository.
2. Place the entire arc-image-grid folder into your plugins directory(Usually <wordpress directory>/wp-content/plugins).
3. In the wordpress administration page find the entry "ARC Image Grid"
4. Click on activate.

# How to use
## Adding to PHP
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

## Adding to a Post or Page
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