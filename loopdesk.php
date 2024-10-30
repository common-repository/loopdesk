<?php
/**
 * Plugin Name: Loopdesk
 * Plugin URI: https://www.loopdesk.io
 * Description: Plugin für die Implementation von Loopdesk auf einer Wordpress-Webseite
 * Version: 0.1
 * Author: Code Crush GmbH
 * Author URI: https://www.codecrush.ch
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 **/


/********************************/
// ----- Admin notices
/********************************/
// invalid key error
function loopdesk_invalid_key_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo esc_html( 'Loopdesk message: The entered key is invalid and could not be set.' ); ?></p>
    </div>
    <?php
}

// invalid key error
function loopdesk_error_notice() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo esc_html( 'Loopdesk message: An unknown error occurred while setting the new key. Try again later.' ); ?></p>
    </div>
    <?php
}

// key updated
function loopdesk_update_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html( 'Loopdesk message: The key has been updated.' ); ?></p>
    </div>
    <?php
}

// key added
function loopdesk_created_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html( 'Loopdesk message: The new key was added. You can now use Loopdesk.' ); ?></p>
    </div>
    <?php
}


/********************************/
// ----- Set Key
/********************************/
$loopdesk_key =  isset( $_POST['loopdesk_key'] ) && substr( $_POST['loopdesk_key'], 0, 3 ) === "pu_" ? sanitize_text_field( wp_unslash( $_POST['loopdesk_key'] ) ) : '';
if ( ! empty( $loopdesk_key ) ) {
    if ( ! get_option( 'loopdesk_key' ) ) {
        if ( add_option( 'loopdesk_key', $loopdesk_key, '', 'yes' ) ) {
            add_action( 'admin_notices', 'loopdesk_created_notice' );
        } else {
            add_action( 'admin_notices', 'loopdesk_error_notice' );
        }
    } else {
        if ( update_option( 'loopdesk_key', $loopdesk_key, 'yes' ) ) {
            add_action( 'admin_notices', 'loopdesk_update_notice' );
        } else {
            add_action( 'admin_notices', 'loopdesk_error_notice' );
        }
    }
} elseif ( isset( $_POST['loopdesk_key'] ) && empty( $loopdesk_key )) {
    add_action( 'admin_notices', 'loopdesk_invalid_key_notice' );
}


/********************************/
// ----- PLUGIN FUNCTIONALITY
/********************************/

//add loopdesk script to head
function loopdesk_add_script() {
    $url = 'https://cdn.loopdesk.io/index.js';
    wp_enqueue_script('loopdesk', $url, true, '0.1', true);
};


function loopdesk_add_data_attribute($tag, $handle) {
    if ( 'loopdesk' !== $handle )
        return $tag;

    return str_replace( ' src', ' data-body="true" loopdesk="' . get_option('loopdesk_key') . '" src', $tag );
}


if(get_option('loopdesk_key')) {
    add_action( 'wp_enqueue_scripts', 'loopdesk_add_script' );
    add_filter('script_loader_tag', 'loopdesk_add_data_attribute', 10, 2);
}


//create settings page in backend
function loopdesk_option_page()
{
    ?>
    <div>
        <?php screen_icon(); ?>
        <h1>Loopdesk</h1>
        <form action="" method="post">
            <?php settings_fields( 'loopdesk_key' ); ?>
            <h3>Enter Key</h3>
            <p>Please enter the key from your <a href="https://app.loopdesk.io/dashboard" target="_blank">project page</a>.</p>
            <input type="text" id="loopdesk_key" name="loopdesk_key" value="<?php echo esc_html(get_option('loopdesk_key')); ?>" />
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


function loopdesk_add_options() {
    add_options_page('Loopdesk', 'Loopdesk', 'manage_options', 'loopdesk', 'loopdesk_option_page');
}
add_action('admin_menu', 'loopdesk_add_options');


/********************************/
// ----- ACTIVATE
/********************************/
function loopdesk_activate() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'loopdesk_activate' );


/********************************/
// ----- DEACTIVATE
/********************************/
function loopdesk_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'loopdesk_deactivate' );


/********************************/
// ----- UNINSTALL
/********************************/
function loopdesk_uninstall () {
    delete_option( 'loopdesk_key' );
}
register_uninstall_hook(__FILE__, 'loopdesk_uninstall');
