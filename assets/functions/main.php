<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Invalid request.' );
}


/* 
   ===================
   Adding Sync To CRON
   ===================
*/

add_action( 'pressburst_schedule_sync_posts', 'pressburst_sync_posts' );

if ( ! wp_next_scheduled( 'pressburst_schedule_sync_posts' ) ) {
    wp_schedule_event( time(), 'hourly', 'pressburst_schedule_sync_posts' );
}



/* 
   ===================
   Plugin Deactivation
   ===================
*/

register_deactivation_hook( __FILE__, 'pressburst_plugin_deactivation' );

function pressburst_plugin_deactivation() {
  $timestamp = wp_next_scheduled( 'pressburst_schedule_sync_posts' );
  wp_unschedule_event( $timestamp, 'pressburst_schedule_sync_posts' );
}



/* 
   ======================
   Sync Posts To WP Posts
   ======================
*/

function pressburst_sync_posts() {

    global $wpdb;
    
    // Get initial VARs
    $options = get_option( 'pressburst_option__api_settings' );
    $pressburst_api_key = sanitize_text_field( $options['key'] );
    $pressburst_api_channel_code = sanitize_text_field( $options['channel_code'] );

    // Return if no API/Channel Code set
    if(!$pressburst_api_key || !$pressburst_api_channel_code) {
      return;
    }

    // VARs
    $config = get_option( 'pressburst_option__configuration' );
    $config__tag = sanitize_text_field($config['tag']);
    $config__user_id = sanitize_text_field($config['user_id']);
    $imported_posts = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM wp_options WHERE option_name = %s", 'newsitemids' ));
    $check_imported_posts = $newsitemids = $import_stats = array();

    // Set limit
    $limit = 10;

    // Convert imported post list to usable array
    if($imported_posts) {
      $check_imported_posts = unserialize($imported_posts->option_value);
    }

    // Request API feed
    $request = wp_remote_get( 'https://api.pressburst.app/v3/public/news/'.esc_html($pressburst_api_channel_code).'/?apikey='.esc_html($pressburst_api_key).'&per_page=99999999' );

    // Return if error
    if( is_wp_error( $request ) ) {
      return false;
    }

    // Parse data
    $body = wp_remote_retrieve_body( $request );
    $data = json_decode( $body );

    // Check if data present
    if( ! empty( $data ) ) {

      // Set total VAR
      $total = 0;

      // Loop through each news instance
      foreach( $data->newsitems as $item ) {

        // Get current newsitemid
        $newsitemid = sanitize_text_field($item->newsitemid);

        // Check newsitemid against previous imports
        if(in_array(esc_html__($newsitemid), $check_imported_posts)) {
          continue;
        }
        
        // Set VARs
        $tag = $user_id = $body_images = '';

        // Set tag
        if($config__tag == 1) {
          $tag = '<p style="display:block;text-align:right;color:#ccc">' . esc_html__('News powered by','pressburst-news-feed') . ' <a target="_blank" href="https://pressburst.com" rel="noopener">Pressburst</a>.</p>';
        }

        // Set post author, defaults to logged in user if not set
        if($config__user_id) {
          $user_id = $config['user_id'];
        }

        // Get data
        $title = sanitize_text_field($item->headline);
        $date = sanitize_text_field($item->publishdate);
        $standfirst = wp_kses_post($item->standfirst);
        $body = wp_kses_post($item->body);

        // Get images
        foreach( $item->media as $image ) {
          if($image->isprimary === 1) {
            $image_url = sanitize_url($image->sizes->default->filesslurl);
            $image_name = sanitize_title($image->filename).'.'.sanitize_text_field($image->fileext);
          } else if($image->isprimary === 0) {
            $grid_image_url = sanitize_url($image->filesslurl);
            $grid_image_url_square = sanitize_url($image->sizes->square->filesslurl);
            $grid_image_name = sanitize_title($image->filename);
            $body_images .= '<a data-fancybox="pressburst-gallery" href="'.esc_url($grid_image_url).'"><img src="'.esc_url($grid_image_url_square).'" alt="'.esc_html($grid_image_name).'"/></a>';
          }
        }

        // Wrap body images in grid
        if($body_images) {
          $body_images = '<div class="pressburst-grid-row">'.$body_images.'</div>';
        }

        $new_post = array(
          'post_title' => esc_html($title),
          'post_excerpt' => wp_kses_post($standfirst),
          'post_content' => wp_kses_post($body.$body_images.$tag),
          'post_status' => 'draft',
          'post_date' => esc_html($date),
          'post_author' => esc_html($user_id),
          'post_type' => 'post'
        );

        $post_id = wp_insert_post( $new_post );

        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents(esc_url($image_url));
        $filename = $image_name;
        if(wp_mkdir_p($upload_dir['path']))
            $file = $upload_dir['path'] . '/' . $filename;
        else
            $file = $upload_dir['basedir'] . '/' . $filename;
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null );
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => esc_html__($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );

        set_post_thumbnail( $post_id, $attach_id );

        // Store news ID
        $newsitemids[] = esc_html__($newsitemid);

        // Increment total
        $total++;

        // Store import list
        $import_stats[] = '<tr><td>'.esc_html__('Imported','pressburst-news-feed').'</td><td>'.esc_html__($title).'</td><td>'.esc_html__($newsitemid).'</td></tr>';

        // Break after limit
        if($total == $limit) {
          break;
        }

      }

      // Build import table output
      echo '<table class="imported_posts">';

      if($import_stats) {
        echo '<thead>';
        echo '<tr><th>'.esc_html__('Status','pressburst-news-feed').'</th><th>'.esc_html__('Title','pressburst-news-feed').'</th><th>'.esc_html__('ID','pressburst-news-feed').'</th></tr>';
        echo '</thead>';
      } else {
        echo '<tr><td>'.esc_html__('No more posts.','pressburst-news-feed').'</td></tr>';
      }

      echo '<tbody>';

      // Print import list
      foreach($import_stats as $import_stat) {
        echo wp_kses_post($import_stat);
      }

      echo '</tbody>';
      echo '</table>';

      // Save imported posts to array
      if($newsitemids) {
        pressburst_save_news_ids($newsitemids);
      }

    }

  }


  function pressburst_save_news_ids($newsitemids) {

    global $wpdb;

    if(get_option('newsitemids')) {

      $imported_posts = $wpdb->get_row( $wpdb->prepare( "SELECT option_value FROM wp_options WHERE option_name = %s", 'newsitemids' ));
      $imported_posts = unserialize($imported_posts->option_value);
      $update_imported_posts = array_merge($imported_posts,$newsitemids);

      update_option('newsitemids',$update_imported_posts);

    } else {

      $wpdb->insert('wp_options', array(
        'option_name' => 'newsitemids',
        'option_value' => serialize($newsitemids)
      ));

    }

  }


/* 
   =============
   Get Post Feed
   =============
*/

function pressburst_get_post_feed() {

  $options = get_option( 'pressburst_option__api_settings' );
  $pressburst_api_key = sanitize_text_field( $options['key'] );
  $pressburst_api_channel_code = sanitize_text_field( $options['channel_code'] );

  // Return if no API/Channel Code set
  if(!$pressburst_api_key || !$pressburst_api_channel_code) {
    return;
  }

  // Set posts per page fallback
  $ppp = 18;

  if(sanitize_text_field(isset($_GET['posts_per_page']))) {
    $ppp = sanitize_text_field($_GET['posts_per_page']);
    $ppp = intval($ppp);
  }

  $request = wp_remote_get( 'https://api.pressburst.app/v3/public/news/'.esc_html($pressburst_api_channel_code).'/?apikey='.esc_html($pressburst_api_key).'&per_page='.esc_attr($ppp) );

  if( is_wp_error( $request ) ) {
    return false;
  }

  $body = wp_remote_retrieve_body( $request );
  $data = json_decode( $body );

  if( ! empty( $data ) ) {

    echo '<ul class="pressburst-grid-row">';

    foreach( $data->newsitems as $item ) {

      $image = sanitize_url($item->media[0]->sizes->header->filesslurl);
      $title = sanitize_text_field($item->headline);
      $date = sanitize_text_field($item->publishdate);
      $standfirst = $item->standfirst;
      $body = $item->body;

      echo '<li>';
      echo '<article>';
      echo '<picture>';
      echo '<img src="'.esc_url($image).'">';
      echo '<picture>';
      echo '<h2>'.esc_html($title).'</h2>';
      echo '<span>'.esc_html(date('d/m/Y',strtotime($date))).'</span>';

      if($standfirst) {
        echo '<div class="intro-content">';
        echo wp_kses_post(wpautop($standfirst));
        echo '<button class="button button-primary">'.esc_html__('Read More','pressburst-news-feed').'</button>';
        echo '</div>';
        echo '<div class="main-content">';
        echo wp_kses_post(wpautop($body));
        echo '</div>';
      } else {
        echo wp_kses_post(wpautop($body));
      }

      echo '</article>';
      echo '</li>';

    }

    echo '</ul>';

  }

}