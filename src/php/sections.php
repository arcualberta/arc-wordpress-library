<?php
namespace Awl;
defined('ABSPATH') or die('No');


// Function arc_get_posts_by_category will be depercated and should not be used further
function arc_get_posts_by_category($category, $objectOutputFunction, $random = false, $limit = 100) {
    global $wpdb;
    global $result;

    $query = "
    SELECT p.ID AS ID, p.post_title AS post_title, p.post_excerpt AS post_excerpt, p.post_type AS post_type, p.guid AS url, pm.meta_key AS meta_key, pm.meta_value AS meta_value
    FROM $wpdb->posts p LEFT JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
    WHERE p.ID IN (SELECT tr.object_id
                    FROM $wpdb->terms t JOIN $wpdb->term_taxonomy tt ON t.term_id = tt.term_id JOIN $wpdb->term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                        WHERE tt.taxonomy = 'category'
                            AND t.slug = '" . $category . "')
        AND p.post_status = 'publish'
        AND p.post_date < NOW() ";

    if ($random) {
        $query .= "ORDER BY " . rand() . " ^ p.ID "; // An exclusive or is used with a rand to keep meta-data grouped together.
    } else {
        $query .= "ORDER BY p.ID, p.post_date DESC ";
    }

    $results = $wpdb->get_results($query, OBJECT);

    $currentId = null;
    $currentObj = null;
    $index = 0;
    foreach ($results as $result) {
        if ($result->ID != $currentId) {

            if ($currentObj != null) {
                call_user_func($objectOutputFunction, $currentObj);
                ++$index;
            }

            if ($index >= $limit) {
                $currentObj = null;
                break;
            }

            $currentObj = new ARCPostCell();
            $currentObj->id = $result->ID;
            $currentObj->name = $result->post_title;
            $currentObj->post_type = $result->post_type;
            $currentObj->url = $result->url;
            $currentObj->excerpt = $result->post_excerpt;
            $currentId = $result->ID;
        }

        $currentObj->metadata[$result->meta_key] = $result->meta_value;
    }

    if ($currentObj != null) {
        call_user_func($objectOutputFunction, $currentObj);
    }
}

function validate_get_posts($args) {
    $result = $args;
    if (!array_key_exists('term_name', $result)) {
        $result['term_name'] = "";
    }

    if (!array_key_exists('limit', $result)) {
        $result['limit'] = 100;
    }

    if (!array_key_exists('order', $result)) {
        $result['order'] = "desc";
    } else {
        $order = $result['order'];
        $order = strtolower($order);

        if ($order != "desc" && $order != "asc") {
            $order = "desc";
        }
        $result['order'] = $order;
    }
    
    if (!array_key_exists('order_by', $result)) {
        $result['order_by'] = "ID";
    } else {
        $order_by = strtolower($result['order_by']);
        if ($order_by == 'random') {
            $order_by = "RAND()";
        } elseif ($order_by = 'arc_id') {
            // * 1 allows to sort as numbers 
            $order_by = "_arc_index * 1"; 
        } else {
            $order_by = "ID";
        }
        $result['order_by'] = $order_by;
    }

    if (!array_key_exists('taxonomy', $result)) {
        $result['taxonomy'] = 'category';
    }

    return $result;

}

function fetch_db_posts($args) {
    global $wpdb;

    $args = validate_get_posts($args);
    $term_name = $args['term_name'];
    $limit = $args['limit'];
    $order = $args['order'];
    $order_by = $args['order_by'];
    $taxonomy = $args['taxonomy'];
    $meta_values = get_meta_values();

    // get first 

    $query = $wpdb->prepare("
        SELECT DISTINCT         
            posts.ID,
            posts.post_title,
            posts.post_content,
            posts.guid, 
            IF (_arc_indexmeta.meta_value is NULL 
            OR _arc_indexmeta.meta_value = '', ".PHP_INT_MAX.", _arc_indexmeta.meta_value)  
            AS _arc_index
        FROM $wpdb->posts posts, $wpdb->postmeta _arc_indexmeta
        INNER JOIN
            $wpdb->term_relationships term_relationships
            ON term_relationships.object_id = _arc_indexmeta.post_id
        INNER JOIN
            $wpdb->term_taxonomy term_taxonomy
            ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
        INNER JOIN
            $wpdb->terms terms
            ON terms.term_id = term_taxonomy.term_id
            AND term_taxonomy.taxonomy = %s
        WHERE
            posts.post_status = 'publish'
        AND posts.post_type = 'post'
        AND _arc_indexmeta.meta_key = '_arc_index'
        AND posts.ID = _arc_indexmeta.post_id
        AND terms.name = %s
        ORDER BY $order_by $order
        LIMIT %d",
        $taxonomy,
        $term_name,                
        $limit
    );
    
    $posts = $wpdb->get_results($query);
    return $posts;
}

function fetch_db_posts_meta($posts) {
    global $wpdb;
    // id to index reference
    $post_reference = array();

    $meta_query = "SELECT post_id, meta_key, meta_value FROM wp_postmeta WHERE ";
    $post_count = count($posts);
    $sql_join_text = " OR ";
    
    foreach($posts as $i => $post) {
        $post_reference[strval($post->ID)] = $i;
        $meta_query .= "post_id = " . $post->ID . $sql_join_text;
    }

    $meta_query = substr($meta_query, 0, strlen($meta_query) - strlen($sql_join_text) ) . ";";
    $meta_results = $wpdb->get_results($meta_query);

    // add meta values to each post
    foreach ($meta_results as $meta) {
        $post_index = $post_reference[strval($meta->post_id)];
        $post = $posts[$post_index];
        $post = (object) array_merge( (array)$post, array( $meta->meta_key => $meta->meta_value ) );
        $posts[$post_index] = $post;
    }
    return $posts;
}

function get_posts($args) {

    $posts = fetch_db_posts($args);
    $posts = fetch_db_posts_meta($posts);

    return $posts;

}

// deprecated, use get_posts instead
function get_posts_by_category($category = "", $limit = 100, $random = false, $order = "desc") {
    global $wpdb;
    $meta_values = get_meta_values();
    $meta_count = count($meta_values);
    $first_meta_name = $meta_values[0]["meta"] . "meta";

    $order = strtolower($order);

    if ($order != "desc" && $order != "asc") {
        $order = "desc";
    }

    $query = "
        SELECT DISTINCT         
            posts.post_title,
            posts.post_content,
            posts.guid ";
        
    for ($i=0; $i<$meta_count; ++$i) {
        $meta_value = $meta_values[$i];
        $query .= ", " . $meta_value["meta"]. "meta.meta_value AS " . $meta_value["meta"];
    }
    
    $query .= " FROM (SELECT p.* FROM $wpdb->posts p"; // This has been moved to a select statement where we limit the content inititally. This greatly improves the speed of the query.
    $query .= " INNER JOIN
        $wpdb->term_relationships term_relationships
        ON term_relationships.object_id = p.ID
    INNER JOIN
        $wpdb->term_taxonomy term_taxonomy
        ON term_taxonomy.term_taxonomy_id = term_relationships.term_taxonomy_id
    INNER JOIN
        $wpdb->terms terms
        ON terms.term_id = term_taxonomy.term_id
        AND terms.name = '" . $category . "'
        AND term_taxonomy = 'category'
        WHERE
    p.post_status = 'publish'
    AND p.post_type = 'post' ";
    if ($random) {
            $query .= "ORDER BY " . rand() . " ^ ID "; // An exclusive or is used with a rand to keep meta-data grouped together.
        } else {
            $query .= "ORDER BY ID ".$order.", post_date ".$order." ";
        }
    $query .= "LIMIT ".$limit;
    $query .= ") posts ";
    for ($i=0; $i<$meta_count; ++$i) {
        $meta_value = $meta_values[$i];
        $meta_name = $meta_value["meta"] . "meta ";
        $query .= " INNER JOIN $wpdb->postmeta " . $meta_name;
        $query .= " ON " . $meta_name . ".post_id = posts.ID";
        $query .= " AND " . $meta_name . ".meta_key = " . "'" . $meta_value["meta"] . "'";
    }   
    
    $query .= ";";
        // error_log($query);
    return $wpdb->get_results($query);
}

function arc_limit_content($data, $contentPath, $contentLimit, $breakChar = ".", $padding = "..."){
    $result = arc_convert_content($contentPath, $data);
    $result = strip_tags(trim($result));
	$result = strip_shortcodes($result);
    
    // Split the results to match the content limit. We will stop it at the periods
    if(strlen($result) > $contentLimit && false !== ($breakpoint = strpos($result, $breakChar, $contentLimit))){ // Is the breakpoint here in the line
        if($breakpoint < strlen($result) - 1){
            $result = substr($result, 0, $breakpoint) . $padding;
        }
    }
    
    $result = nl2br($result); 
    
    return $result;
}

function arc_create_section($id, $data, $imagePath, $titlePath = '', $contentPath = '', $urlPath = '', $classes = '', $isVertical = true, $textLimit = 140){
    ?>
<div id="<?php echo $id ?>" class="arc-sections <?php echo $classes ?>">
    <?php
    foreach($data as $key => $value){
        ?>
    <div class="arc-section">
        <img src="<?php echo arc_convert_content($imagePath, $value)?>"/>
        <h4><?php echo arc_convert_content($titlePath, $value)?></h4>
        <div class='arc-section-content'>
            <?php echo arc_limit_content($value, $contentPath, $textLimit) ?>
            <a class="arc-read-more" href="<?php echo arc_convert_content($urlPath, $value)?>">[Read More]</a>
        </div>
    </div>
            <?php
    }
        ?>
</div>
    <?php
}

function arc_section_by_category($id, $categoryName, $isVertical = true, $classes = '', $limit = 3, $textLimit = 140) {
    global $arc_carousel_array;
    $arc_carousel_array = array();
    
    arc_get_posts_by_category($categoryName, 'Awl\arc_carousel_array_push', false, $limit);
    arc_create_section($id, $arc_carousel_array, '{$data->metadata["_arc_image_grid_img"]}', '$data->name', '{$data->get_post()->post_content}', '$data->url', $classes, $isVertical, $textLimit);
}
