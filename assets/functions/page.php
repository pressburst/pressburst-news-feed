<?php

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Invalid request.' );
}


/* 
   ====================
   Add Page To WP-Admin
   ====================
*/

function pressburst_add_settings_page() {
    add_options_page( 'Pressburst', 'Pressburst', 'manage_options', 'pressburst-news-feed', 'pressburst_construct__api_settings_page' );
}

add_action( 'admin_menu', 'pressburst_add_settings_page' );



/* 
   =======================
   Construct Settings Page
   =======================
*/

function pressburst_construct__api_settings_page() {

    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get the active tab from the $_GET param
    $default_tab = null;
    $tab = sanitize_text_field( isset($_GET['tab']) ) ? sanitize_text_field( $_GET['tab'] ) : $default_tab;

    ?>

    <div class="wrap">

    <a class="pressburst-logo" href="https://pressburst.com/" target="_blank" rel="noopener"><img alt="Pressburst Logo" src="<?php echo plugins_url( '/img/pressburst_logo_colour_cropped.webp', __DIR__ ); ?>"></a>

    <nav class="nav-tab-wrapper">
        <a href="?page=pressburst-news-feed" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php echo esc_html__('Getting Started','pressburst-news-feed'); ?></a>
        <a href="?page=pressburst-news-feed&tab=api_settings" class="nav-tab <?php if($tab==='api_settings'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html__('API Settings','pressburst-news-feed'); ?></a>
        <a href="?page=pressburst-news-feed&tab=configuration" class="nav-tab <?php if($tab==='configuration'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html__('Configuration','pressburst-news-feed'); ?></a>
        <a href="?page=pressburst-news-feed&tab=import" class="nav-tab <?php if($tab==='import'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html__('Import Posts','pressburst-news-feed'); ?></a>
         <a href="?page=pressburst-news-feed&tab=posts" class="nav-tab <?php if($tab==='posts'):?>nav-tab-active<?php endif; ?>"><?php echo esc_html__('Pressburst Feed','pressburst-news-feed'); ?></a>
    </nav>

    <div class="tab-content">

    <?php

        switch($tab) :
            case 'api_settings':
                echo '<form action="options.php" method="post">';
                settings_fields( 'pressburst_option__api_settings' );
                do_settings_sections( 'pressburst_do_settings_sections__api_settings' );
                echo '<input name="submit" class="button button-primary" type="submit" value="'.esc_attr( 'Save' ).'" />';
                echo '</form>';
            break;
            case 'configuration':
                echo '<form action="options.php" method="post">';
                settings_fields( 'pressburst_option__configuration' );
                do_settings_sections( 'pressburst_do_settings_sections__configuration' );
                echo '<input name="submit" class="button button-primary" type="submit" value="'.esc_attr( 'Save' ).'" />';
                echo '</form>';
            break;
            case 'posts':
                settings_fields( 'pressburst_option__pressburst_posts' );
                do_settings_sections( 'pressburst_do_settings_sections__pressburst_posts' );
            break;
            case 'import':
                settings_fields( 'pressburst_option__import' );
                do_settings_sections( 'pressburst_do_settings_sections__import' );
            break;
            default:
                settings_fields( 'pressburst_option__getting_started' );
                do_settings_sections( 'pressburst_do_settings_sections__getting_started' );
            break;
        endswitch;

    ?>

    </div>
    </div>

    <?php

}



/* 
   ==============================
   Construct Getting Started Page
   ==============================
*/

function pressburst_construct_content__getting_started() {
    register_setting( 'pressburst_option__getting_started', 'pressburst_option__getting_started', 'pressburst_option__getting_started_validate' );
    add_settings_section( 'pressburst_section__getting_started', 'Getting Started', 'pressburst_content__getting_started', 'pressburst_do_settings_sections__getting_started' );
}

add_action( 'admin_init', 'pressburst_construct_content__getting_started' );

function pressburst_content__getting_started() {
    echo wpautop(esc_html__('Pressburst documentation.','pressburst-news-feed'));
    echo '<a class="button button-primary" href="https://media.pressburst.app/app/userguides/pressburst-news-wordpress-guide.pdf" target="_blank">'.esc_html__('Setup Guide','pressburst-news-feed').'</a>&nbsp;';
    echo '<a class="button button-primary" href="https://media.pressburst.app/app/userguides/pressburst-news-wordpress-developer-guide.pdf" target="_blank">'.esc_html__('Developer Guide','pressburst-news-feed').'</a>';
}



/* 
   =======================
   Construct Settings Page
   =======================
*/

function pressburst_construct_content__api_settings() {
    register_setting( 'pressburst_option__api_settings', 'pressburst_option__api_settings', 'pressburst_option__api_settings_validate' );
    add_settings_section( 'pressburst_section__api_settings', 'API Settings', 'pressburst_content__api_settings', 'pressburst_do_settings_sections__api_settings' );
    add_settings_field( 'pressburst_option__api_settings_key', 'API Key', 'pressburst_option__api_settings_key', 'pressburst_do_settings_sections__api_settings', 'pressburst_section__api_settings' );
    add_settings_field( 'pressburst_option__api_settings_channel_code', 'API Channel Code', 'pressburst_option__api_settings_channel_code', 'pressburst_do_settings_sections__api_settings', 'pressburst_section__api_settings' );
}

add_action( 'admin_init', 'pressburst_construct_content__api_settings' );

function pressburst_content__api_settings() {
    echo wpautop(esc_html__('Add your Pressburst API Key & API Channel Code below.','pressburst-news-feed'));
}

function pressburst_option__api_settings_key() {
    $options = get_option( 'pressburst_option__api_settings' );
    echo '<input required maxlength="64" minlength="64" id="pressburst_option__api_settings_key" name="pressburst_option__api_settings[key]" type="text" value="' . esc_attr( $options['key'] ) . '" />';
}

function pressburst_option__api_settings_channel_code() {
    $options = get_option( 'pressburst_option__api_settings' );
    echo '<input required maxlength="64" id="pressburst_option__api_settings_channel_code" name="pressburst_option__api_settings[channel_code]" type="text" value="' . esc_attr( $options['channel_code'] ) . '" />';
}


/* 
   ============================
   Construct Configuration Page
   ============================
*/

function pressburst_construct_content__configuration() {
    register_setting( 'pressburst_option__configuration', 'pressburst_option__configuration', 'pressburst_option__configuration_validate' );
    add_settings_section( 'pressburst_section__configuration', 'Configuration', 'pressburst_content__configuration', 'pressburst_do_settings_sections__configuration' );
    add_settings_field( 'pressburst_option__configuration_user_id', 'Set Post Author', 'pressburst_option__configuration_user_id', 'pressburst_do_settings_sections__configuration', 'pressburst_section__configuration' );
    add_settings_field( 'pressburst_option__configuration_tag', 'Show "Powered by Pressburst"', 'pressburst_option__configuration_tag', 'pressburst_do_settings_sections__configuration', 'pressburst_section__configuration' );
    add_settings_field( 'pressburst_option__configuration_fancybox', 'Enable Fancybox Image Gallery', 'pressburst_option__configuration_fancybox', 'pressburst_do_settings_sections__configuration', 'pressburst_section__configuration' );
}

add_action( 'admin_init', 'pressburst_construct_content__configuration' );

function pressburst_content__configuration() {
    echo wpautop(esc_html__('Plugin configuration.','pressburst-news-feed'));
}


function pressburst_option__configuration_user_id() {

    $options = get_option( 'pressburst_option__configuration' );

    $roles = array('author','editor','administrator');

    foreach($roles as $role) {

        $args = array(
            'role'    => $role,
            'orderby' => 'user_nicename',
            'order'   => 'ASC'
        );

        $users = get_users( $args );

        if($users) { ?>

        <select id="pressburst_option__configuration_user_id" name="pressburst_option__configuration[user_id]">

        <?php

            foreach ( $users as $user ) {

            ?>

                <option value="<?php echo esc_html( $user->ID ); ?>" <?php selected( $options['user_id'], esc_html( $user->ID ) ); ?>><?php echo esc_html( $user->display_name ); ?></option>

            <?php

            }

        }

    }

    ?>
    
        </select>

    <?php

}


function pressburst_option__configuration_tag() {

    $options = get_option( 'pressburst_option__configuration' );

    ?>

    <input type="checkbox" id="pressburst_option__configuration_tag" name="pressburst_option__configuration[tag]" value="1" <?php checked( esc_attr( $options['tag']), 1 ); ?>/>

    <?php

}


function pressburst_option__configuration_fancybox() {

    $options = get_option( 'pressburst_option__configuration' );

    ?>

    <input type="checkbox" id="pressburst_option__configuration_fancybox" name="pressburst_option__configuration[fancybox]" value="1" <?php checked( esc_attr( $options['fancybox']), 1 ); ?>/>

    <?php

}




/* 
   =====================
   Construct Import Page
   =====================
*/

function pressburst_construct_content__import() {
    register_setting( 'pressburst_option__import', 'pressburst_option__import', 'pressburst_option__import_validate' );
    add_settings_section( 'pressburst_section__import', 'Import Posts', 'pressburst_content__import', 'pressburst_do_settings_sections__import' );
}

add_action( 'admin_init', 'pressburst_construct_content__import' );

function pressburst_content__import() {
    $options = get_option( 'pressburst_option__api_settings' );
    $server_url = sanitize_url($_SERVER["REQUEST_URI"]);
    if(esc_attr( $options['key'] ) && esc_attr( $options['channel_code'] )) {
        echo wpautop(esc_html__('Import Pressburst posts by clicking the button below.','pressburst-news-feed'));
        echo '<a class="button button-primary" href="'.esc_url($server_url).'&import_posts=true">'.esc_html__('Import Next 10 Posts','pressburst-news-feed').'</a>';
        if(sanitize_text_field( isset($_GET['import_posts']) ) ) {
            pressburst_sync_posts();
        }
    } else {
        echo esc_html__('Please add your API Key and Channel Code','pressburst-news-feed');
    }
}




/* 
   ===============================
   Construct Pressburst Posts Page
   ===============================
*/

function pressburst_construct_content__pressburst_posts() {
    register_setting( 'pressburst_option__pressburst_posts', 'pressburst_option__pressburst_posts', 'pressburst_option__pressburst_posts_validate' );
    add_settings_section( 'pressburst_section__pressburst_posts', 'Pressburst Feed', 'pressburst_content__pressburst_posts', 'pressburst_do_settings_sections__pressburst_posts' );
}

add_action( 'admin_init', 'pressburst_construct_content__pressburst_posts' );

function pressburst_content__pressburst_posts() {
    $options = get_option( 'pressburst_option__api_settings' );
    $server_url = sanitize_url($_SERVER["REQUEST_URI"]);
    
    if(esc_attr( $options['key'] ) && esc_attr( $options['channel_code'] )) {
        echo wpautop(esc_html__('Your Pressburst post feed.','pressburst-news-feed'));

        $ppp = 18;

        if(sanitize_text_field(isset($_GET['posts_per_page']))) {
            $ppp = sanitize_text_field($_GET['posts_per_page']);
            $ppp = intval($ppp);
        }

        echo '<form action="'.esc_url($server_url).'" method="GET">';
        echo '<input type="hidden" id="page" name="page" value="pressburst-news-feed">';
        echo '<input type="hidden" id="tab" name="tab" value="posts">';
        echo '<label for="posts_per_page">'.esc_html__('Show Posts','pressburst-news-feed').'</label>&nbsp;';
        echo '<input id="posts_per_page" name="posts_per_page" type="number" value="'.esc_attr($ppp).'" max="100" min="1">&nbsp;';
        echo '<button class="button button-primary">'.esc_html__('Update Feed').'</button>';
        echo '</form>';

        pressburst_get_post_feed();
    } else {
        echo esc_html__('Please add your API Key and Channel Code','pressburst-news-feed');
    }
}

