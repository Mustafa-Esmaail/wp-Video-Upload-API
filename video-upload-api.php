<?php
/*
Plugin Name: Video Upload API
Plugin URI: https://www.example.com/
Description: Custom API for video uploads.
Version: 1.0
Author: Mustafa Esmaail
Author URI: https://www.example.com/
*/

// Prevent direct access to the plugin file
if (!defined('ABSPATH')) {
    exit;
}

// Register API endpoint
add_action('rest_api_init', 'register_video_upload_endpoint');

function register_video_upload_endpoint()
{
    register_rest_route(
        'video-upload-api/v1',
        '/upload',
        array(
            'methods' => 'POST',
            'callback' => 'handle_video_upload',
            'permission_callback' => '__return_true',
        )
    );
}

function handle_video_upload(WP_REST_Request $request)
{
    $file = $request->get_file_params();
    $params = $request->get_json_params();
    $Getparams = $request->get_query_params();

    
    global $wpdb;
    $addtitle = $params['post_title'];
    $addDesc = $params['post_description'];
    $addCat = $params['category'];
    $addPrice = $params['price'];
    $addPhone = $params['phone'];
    $postData = array(
        'post_title' => $addtitle,
        'post_author' =>  $Getparams['ur_id'],
        'post_content' => $addDesc,
        'post_type' => 'listing',
        'post_status' => 'draft'

    );


    // Insert the post into the database
    $post_id = wp_insert_post($postData);

    // add category 

    $category_id = $addCat;  // Set the ID of the category you want to assign to the post
    wp_set_post_terms($post_id, $category_id, 'category');

    // save  to database
    // Replace with the ID of the post you want to add metadata to.
    add_post_meta($post_id, '_phone', $addPhone);
    // serialize price 


    add_post_meta($post_id, '_menu_status', 'on');
    add_post_meta($post_id, '_hide_pricing_if_bookable', '0');

    // serialize price 
    // Define your query

    $num = $addPrice;
    $numlength = strlen((string)$num);
    $pr = 'a:1:{i:0;a:1:{s:13:"menu_elements";a:1:{i:0;a:4:{s:4:"name";s:10:"السعر";s:5:"price";s:' . $numlength . ':"' . $addPrice . '";s:16:"bookable_options";s:7:"onetime";s:11:"description";s:0:"";}}}}';

    // Run the query
    $dataMenu = array(
        'post_id' => $post_id,
        'meta_key' => '_menu',
        'meta_value' => $pr,

    );

    $result = $wpdb->insert('wp_postmeta', $dataMenu, array('%d', '%s', '%s'));
    // 
    // upload video




    // $file_path = $file['tmp_name'];
    // $file_name = $file['name'];
    // $file_type = $file['type'];

    // // Perform additional validation or processing if needed

    // // Move the uploaded file to a permanent location
    // $upload_dir = wp_upload_dir();
    // $target_path = $upload_dir['path'] . '/' . $file_name;

    // if (!move_uploaded_file($file_path, $target_path)) {
    //     return new WP_Error('file_move_error', 'Failed to move the uploaded file', array('status' => 500));
    // }

    // upload video
    if (empty($file)) {
        $video_name = $file['name'];
        $video_tmp_name =  $file['tmp_name'];
        $video_type =  $file['type'];

        // Check if the file is a video
        if ($video_type != 'video/mp4' && $video_type != 'video/mpeg') {
            echo 'Error: Only MP4 and MPEG videos are allowed.';
            exit;
        }

       
        // $fileV = $_FILES['video'];
        $upload = wp_upload_bits($video_name, null, file_get_contents( $video_tmp_name));
        
        $videoData['path'] = $upload['file'];

        $Videotitle = $addtitle;
        $user_id =$Getparams['ur_id'];
        $postID = $post_id;
        $Videodescription = $addDesc;
        $Videoprivacy = 'public';
        $Videotags = explode(' ', $_POST['tags']);




        // save  to database
        global $wpdb;

        $table_name =  'wp_videos';

        $data = array(
            'title' => $Videotitle,
            'user_id' => $user_id,
            'post_id' => $postID,
            'desc' => $Videodescription,
            'privacy' => $Videoprivacy,
            'tags' => $Videotags,
            'video_path' => $videoData['path'],
            'status' => 'pending',
        );

        $result = $wpdb->insert($table_name, $data, array('%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s'));
        if ($result) {
            echo 'Data inserted successfully';
        } else {
            echo 'Error inserting data';
            print_r($result);
            echo $result;
        }
    }

    $response = array(
        'message' => 'Post added  successfully'
    );

    return $response;
}
