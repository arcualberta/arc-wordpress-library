# ARC Image Grid
The ARC Image Grid is a wordpress plugin which allows you to display a grid of images linking to posts and pages.

# Installation
1. Download the code from this repository.
2. Place the entire arc-image-grid folder into your plugins directory(Usually <wordpress directory>/wp-content/plugins).
3. In the wordpress administration page find the entry "ARC Image Grid"
4. Click on activate.

# How to use
## Adding to PHP
To add the image grid to a php page, simply call the function `arc_image_grid_add_grid($name, $img_width, $img_height, $max_col_count, $content)` using the following paramaters:
*$name*             The name of the grid to use. This name will be defined on a post or page entry.
*$img_width*        The width of each image(in pixels) in the grid.
*$img_height*       The height of each image(in pixels) in the grid.
*$max_col_count*    The maximum number of columns that are displayed on this grid.
*$content*          HTML content that can appear behind the image on the grid.

Example
```
arc_image_grid_add_grid('ALBUM', 215, 215, 2, '<p style="color: white;">Grid Name: {metadata["_arc_image_grid_name"]}<a style="color: white;" href="{url}">Go to {name}</a>');
```

## Adding to a Post or Page
To add an image grid to a post or page call the shortcode function `arc_add_image_grid` with the backside content specified in-line and the following parameters:
*name*             The name of the grid to use. This name will be defined on a post or page entry.
*img_width*        The width of each image(in pixels) in the grid.
*img_height*       The height of each image(in pixels) in the grid.
*max_col_count*    The maximum number of columns that are displayed on this grid.

Example
```
[arc_add_image_grid name="TEST" img_width=215 img_height=215 max_col_count=2]
<p style="color: white;">Grid Name: {metadata['_arc_image_grid_name']}
<a style="color: white;" href="{url}">Go to {name}</a>
[/arc_add_image_grid]
```