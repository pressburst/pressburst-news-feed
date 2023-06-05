<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Invalid request.' );
}


/* 
   ======================
   Admin Styles & Scripts
   ======================
*/

function pressburst_admin_styles_and_scripts() {

  // Admin Styles
  wp_enqueue_style('pressburst-admin', plugins_url( '/css/pressburst-admin.css', __DIR__ ),'','', 'screen');

  // Scripts
  wp_register_script( 'pressburst-scripts', plugins_url( '/js/pressburst-scripts.js', __DIR__ ), array('jquery'),'',true  );
  wp_enqueue_script( 'pressburst-scripts' );
  $localData = array(
    'siteURL' => site_url()
  );

  wp_localize_script( 'pressburst-scripts', 'js_var', $localData );

}

add_action('admin_enqueue_scripts', 'pressburst_admin_styles_and_scripts');