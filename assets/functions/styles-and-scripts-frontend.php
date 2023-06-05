<?php

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Invalid request.' );
}


/* 
   =========================
   Frontend Styles & Scripts
   =========================
*/

function pressburst_frontend_styles_and_scripts() {

  $options = get_option( 'pressburst_option__configuration' );
  $pressburst_fancybox = esc_attr( $options['fancybox'] );

  // Frontend Styles
  wp_enqueue_style('pressburst-frontend', plugins_url( '/css/pressburst-frontend.css', __DIR__ ),'','', 'screen');

  // Fancybox
  if($pressburst_fancybox == 1) {
    wp_enqueue_script('fancybox-js', plugins_url( '/js/jquery.fancybox.min.js', __DIR__ ), array('jquery'),'',true  );   
    wp_enqueue_style('fancybox-css', plugins_url( '/css/jquery.fancybox.min.css', __DIR__ ),'','', 'screen');
  }

}

add_action('wp_enqueue_scripts', 'pressburst_frontend_styles_and_scripts');